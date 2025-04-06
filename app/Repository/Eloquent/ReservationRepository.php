<?php

namespace App\Repository\Eloquent;

use App\Constants\AuditServices;
use App\Constants\PaymentConstants;
use App\Constants\PermissionsConstants;
use App\Constants\RolesConstants;
use App\Events\NewReservation;
use App\Events\UpdateReservation;
use App\Models\Item;
use App\Models\OrderLine;
use App\Models\Reservation;
use App\Models\User;
use App\Repository\ReservationRepositoryInterface;
use App\Services\AuditService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;


class ReservationRepository extends BaseRepository implements ReservationRepositoryInterface
{

    public const Relations = ['reservable.locales', 'order', 'reservedBy.contacts',
        'reservedFor.contacts', 'invoices', 'branch.settings', 'business.settings', 'follower'];

    public function __construct(Reservation $model, private InvoiceRepository $invoiceRepository)
    {
        parent::__construct($model);
    }

    public function process(array $data): array
    {
        return array_only($data, [
            "from", "to", "reservable_id", "reservable_type", "status",
            "data", "order_id", "order_line_id", "reserved_by_id", "reserved_for_id",
            "business_id", "branch_id", "created_at", "updated_at", 'notes', 'follower_id'
        ]);
    }

    public function setModelRelations($model, $data)
    {
        $currentUser = auth('sanctum')->user();
        if (isset($data['invoices']) && $currentUser->hasAnyRole([RolesConstants::ADMIN, RolesConstants::BUSINESS_OWNER])) {
            $this->invoiceRepository->setForReservation($model, $data['invoices']);
        }

    }

    public function get($id)
    {
        return $this->model->with(ReservationRepository::Relations)->find($id);
    }

    public function filter(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $branchId = request()->route('branchId');
        $businessId = request()->route('businessId');
        $startDate = $request->input('from');
        $endDate = $request->input('to');
        $itemId = $request->input('item_id');
        $status = $request->input('status');
        $reservedForId = $request->input('reserved_for_id');
        $reservedById = $request->input('reserved_by_id');
        $followerId = $request->input('follower_id');

        // TODO :: agree on default
        return Reservation::where(['branch_id' => $branchId, 'business_id' => $businessId])
            ->whereHas('reservable')
            ->where(function ($query) use ($itemId, $status, $reservedForId, $reservedById, $followerId) {
                if (isset($itemId)) $query->where('reservable_id', $itemId);
                if (isset($reservedForId)) $query->where('reserved_for_id', $reservedForId);
                if (isset($reservedById)) $query->where('reserved_by_id', $reservedById);
                if (isset($followerId)) $query->where('follower_id', $followerId);
                if (isset($status)) $query->where('status', $status);
                else $query->where('status', '!=', PaymentConstants::RESERVATION_CANCELED);
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('from', [$startDate, $endDate])
                    ->orWhereBetween('to', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('from', '<=', $startDate)
                            ->where('to', '>=', $endDate);
                    });
            })
            ->paginate(request('per-page', 1200));
    }

    public function listModel($businessId, $branchId, $conditions = null)
    {
        return Reservation::with(ReservationRepository::Relations)
            ->where(['branch_id' => $branchId, 'business_id' => $businessId])
            ->where(fn($q) => $conditions ? $q->where(...$conditions) : $q)
            ->orderByDesc('id')
            ->paginate(request('per-page', 15));
    }

    public function create(array $data): Model
    {
        $branchId = request()->route('branchId');
        $businessId = request()->route('businessId');

        $this->checkAllowedReservationAmount($data, $businessId, $branchId);

        $data['reserved_by_id'] = auth('sanctum')->user()->id;
        $data['reserved_for_id'] = request()->get('reserved_for_id') ?? auth('sanctum')->user()->id;

        $model = $this->model->create($this->process($data));
        $this->setModelRelations($model, $data);

        if (!$model->data)
            $this->setReservationCashedData($model->id);

        event(new NewReservation($model->id));
        AuditService::log(AuditServices::Reservations, $model->id, "Created booking " . $model->id, $businessId, $branchId);
        return $this->get($model->id);
    }

    /**
     * @throws \Exception
     */
    public function updateModel($id, array $data): Model
    {
        // Check the user has the authority to make this order paid (admin | owner | user )
        $userId = auth('sanctum')->user()->id;
        $user = User::find($userId);
        $reservation = $this->model->findOrFail($id);

        if (!$user->hasAnyPermission([PermissionsConstants::Branch . '.' . $reservation->branch_id,
            PermissionsConstants::Business . '.' . $reservation->business_id]))
            return throw new \Exception(403, 'You Don\'t have permission');

        if (!isset($data['reservable_id']))
            $data['reservable_id'] = $reservation->reservable_id;

        if (isset($data['from']) && isset($data['to'])) {

            $this->checkAllowedReservationAmount($data, $reservation->business_id, $reservation->branch_id, $id);

        }

        // TODO:: check if data['paid']
        $model = tap($reservation)
            ->update($this->process($data));

        $this->setModelRelations($model, $data);

        $this->setReservationCashedData($model->id);

        event(new UpdateReservation($model->id));
        AuditService::log(AuditServices::Reservations, $model->id, "Updated booking " . $model->id,
            $reservation->business_id, $reservation->branch_id);
        return $this->get($model->id);
    }

    public function orderLineToReservationData($olData)
    {
        $resData = [];
        $resData['reservable'] = $olData['item'];
        $resData['reserved_for'] = $olData['user'];
        $resData['reserved_by'] = $olData['user'];
        $resData += array_only($olData, ['addons', 'invoices', 'discounts', 'subtotal_price', 'total_price']);
        return $resData;
    }


    public function set(Item $item, OrderLine $orderLine, array $reservation)
    {
        $reservationData = $reservation + [
                'reservable_id' => $item->id,
                'reservable_type' => Item::class,
                'order_line_id' => $orderLine->id,
                'order_id' => $orderLine->order_id,
                'item_id' => $orderLine->item_id,
                'reservation_for_id' => $orderLine->user_id,
                'data' => $this->orderLineToReservationData($orderLine->data),
                'business_id' => request()->route('businessId'),
                'branch_id' => request()->route('branchId'),
            ];
        if (isset($reservationData['id']) && $reservationData['id'])
            $this->update($reservationData['id'], $reservationData);
        else
            $this->create($reservationData);
    }

    public function setReservationCashedData($reservationId)
    {
        /**
         * note: prices & discounts on orders table
         * as they are coming from mobiles not dashboard
         */
        $reservation = Reservation::with('invoices',
            'reservable.locales',
            'reservable.itemable',
            'reservedFor',
            'reservedBy'
        )->find($reservationId);

        $price = 0;
        foreach ($reservation->invoices as $invoice) {
            if ($invoice->type === PaymentConstants::INVOICE_CREDIT)
                $price += $invoice->amount;
            if ($invoice->type === PaymentConstants::INVOICE_DEBIT)
                $price -= $invoice->amount;
        }

        $cachedData = [];
        $cachedData += [
            "reservable" => $reservation->reservable,
            "reserved_for" => $reservation->reservedFor,
            "reserved_by" => $reservation->reservedBy,
            "follower" => $reservation->follower,
            "invoices" => $reservation->invoices,
            "subtotal_price" => $price,
            "total_price" => $price
        ];
        $reservation->update(['data' => $cachedData]);

    }

    public function setReservationInvoicesCashedData($reservationId)
    {
        /**
         * note: prices & discounts on orders table
         * as they are coming from mobiles not dashboard
         */
        $reservation = Reservation::with('invoices')->find($reservationId);

        $price = 0;
        foreach ($reservation->invoices as $invoice) {
            if ($invoice->type === PaymentConstants::INVOICE_CREDIT)
                $price += $invoice->amount;
            if ($invoice->type === PaymentConstants::INVOICE_DEBIT)
                $price -= $invoice->amount;
        }

        $cachedData = $reservation->data;
        unset($cachedData['invoices']);
        unset($cachedData['subtotal_price']);
        unset($cachedData['total_price']);
        $cachedData += [
            "invoices" => $reservation->invoices,
            "subtotal_price" => $price,
            "total_price" => $price
        ];
        $reservation->update(['data' => $cachedData]);
    }

    // checking if there is current reservation
    public function getSameReservation($reservationData, $reservable_id, $businessId, $branchId)
    {
        return Reservation::where('reserved_for_id', auth()->user()->id)
            ->where('reservable_id', $reservable_id)
            ->where('from', $reservationData['from'])
            ->where('to', $reservationData['to'])
            ->where('business_id', $businessId)
            ->where('branch_id', $branchId)
            ->where('status', PaymentConstants::RESERVATION_PENDING)
            ->first();
    }

    // get current intersected reservations with the required period
    public function currentReservations($data, $businessId, $branchId, $updateId = null)
    {
        $startDate = $data['from'];
        $endDate = $data['to'];
        $reservable_id = $data['reservable_id'];
        return Reservation::
        select(['from', 'to'])
            ->where(['branch_id' => $branchId, 'business_id' => $businessId])
            ->where('reservable_id', $reservable_id)
            ->where('status', '!=', PaymentConstants::RESERVATION_CANCELED)
            ->where(function ($query) use ($updateId) {
                // todo :: check for same id
                if ($updateId)
                    $query->where('id', '!=', $updateId);
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('from', [$startDate, $endDate])
                    ->orWhereBetween('to', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('from', '<=', $startDate)
                            ->where('to', '>=', $endDate);
                    });
            })->get();
    }

    public function checkAllowedReservationAmount($data, $businessId, $branchId, $updateId = null)
    {
        $currentReservations = $this->currentReservations($data, $businessId, $branchId, $updateId)->toArray();
        $item = Item::with('itemable')->find($data['reservable_id']);

        $periodMap = array_map(function ($period) {
            return Period::make($period['from'], $period['to']);
        }, $currentReservations);
        // Add the requested period to the map
        $periodMap[] = Period::make(Carbon::parse($data['from']), Carbon::parse($data['to']));
        $collection = new PeriodCollection(...$periodMap);

        // Carbon period for the will created / updated reservation
        $period = CarbonPeriod::create($data['from'], $data['to']);
        $daysIntersections = [];
        // every day in the period
        foreach ($period as $date) {
            $day = $date->format('Y-m-d');
            // Convert the day to a single-day Period
            $dayPeriod = Period::make(
                Carbon::parse($day)->startOfDay(),
                Carbon::parse($day)->endOfDay()
            );

            // Count intersections
            $intersectionCount = $collection
                ->filter(fn(Period $period) => $period->overlapsWith($dayPeriod))
                ->count();

            $daysIntersections[$day] = $intersectionCount;

            if ($intersectionCount > $item->itemable->amount)
                abort(400, "Not available now, please choose different dates or try again later");
        }
    }

}
