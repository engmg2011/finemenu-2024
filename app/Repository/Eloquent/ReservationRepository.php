<?php

namespace App\Repository\Eloquent;

use App\Constants\AuditServices;
use App\Constants\BusinessTypes;
use App\Constants\ConfigurationConstants;
use App\Constants\PaymentConstants;
use App\Constants\PermissionActions;
use App\Constants\PermissionsConstants;
use App\Constants\PermissionServices;
use App\Constants\RolesConstants;
use App\Events\NewReservation;
use App\Events\UpdateReservation;
use App\Jobs\SendNewReservationNotification;
use App\Jobs\SendUpdateReservationNotification;
use App\Models\Business;
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
        'reservedFor.contacts', 'invoices', 'branch.settings', 'business.settings', 'follower', 'seat.locales'];

    public function __construct(Reservation $model, private InvoiceRepository $invoiceRepository)
    {
        parent::__construct($model);
    }

    public function process(array $data): array
    {
        if(empty($data['unit'])) {
            $data['unit'] = 1;
            \Log::debug("empty or null unit id");
        }
        return array_only($data, [
            "from", "to", "reservable_id", "reservable_type", "status",
            "data", "order_id", "order_line_id", "reserved_by_id", "reserved_for_id",
            "business_id", "branch_id", "created_at", "updated_at", 'notes', 'follower_id',
            "unit" , "seat_id"
        ]);
    }

    public function setModelRelations($model, $data)
    {
        $currentUser = auth('sanctum')->user();
        $branchId = \request()->route("branchId") ?? $data['branch_id'] ?? null;
        if (isset($data['invoices'])) {
            $isAdmin = $currentUser->hasAnyRole([RolesConstants::ADMIN, RolesConstants::BUSINESS_OWNER,
                RolesConstants::BRANCH_MANAGER, RolesConstants::CASHIER]);
            $isBranchAdmin = $branchId && $currentUser->hasAnyPermission(PermissionsConstants::Branch . '.' . $branchId);
            if ($isAdmin || $isBranchAdmin) {
                $this->invoiceRepository->setForReservation($model, $data['invoices']);
            }
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
            'to' => 'required|date',
        ]);

        $data = $request->all();
        // Convert to Carbon instances to compare
        if(str_contains($data['from'], "T")){
            if(strlen($data['from']) === 16){
                $from = Carbon::createFromFormat('Y-m-d\\TH:i', $data['from']);
                $to = Carbon::createFromFormat('Y-m-d\\TH:i', $data['to']);
            }else{
                $from = Carbon::createFromFormat('Y-m-d\\TH:i:s', $data['from']);
                $to = Carbon::createFromFormat('Y-m-d\\TH:i:s', $data['to']);
            }
        }else{
            $from = Carbon::parse($data['from']);
            $to = Carbon::parse($data['to']);
        }

        // Swap if from > to
        if ($from->gt($to)) {
            [$data['from'], $data['to']] = [$data['to'], $data['from']];
        }
        $branchId = request()->route('branchId');
        $businessId = request()->route('businessId');
        $itemId = $request->input('item_id');
        $status = $request->input('status');
        $reservedForId = $request->input('reserved_for_id');
        $reservedById = $request->input('reserved_by_id');
        $followerId = $request->input('follower_id');

        $business = Business::find($businessId);
        $startDate = businessToUtcConverter($data['from'], $business,'Y-m-d H:i:s');
        $endDate = businessToUtcConverter($data['to'], $business,'Y-m-d H:i:s');

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
        return Reservation::with('reservable.featuredImage')
            ->where(['branch_id' => $branchId, 'business_id' => $businessId])
            ->where(fn($q) => $conditions ? $q->where(...$conditions) : $q)
            ->orderByDesc('id')
            ->paginate(request('per-page', 15));
    }

    public function create(array $data): Model
    {
        $branchId = request()->route('branchId');
        $businessId = request()->route('businessId');

        if(Business::find($businessId)->type === BusinessTypes::CHALET){
            $this->checkAllowedReservationUnits($data, $businessId, $branchId);
        }

        $data['reserved_by_id'] = auth('sanctum')->user()->id;
        $data['reserved_for_id'] = request()->get('reserved_for_id') ?? auth('sanctum')->user()->id;

        $model = $this->model->create($this->process($data));
        $this->setModelRelations($model, $data);

        if (!$model->data)
            $this->setReservationCashedData($model->id);

        AuditService::log(AuditServices::Reservations, $model->id, "Created booking " . $model->id, $businessId, $branchId);

        event(new NewReservation($model->id));
        dispatch(new SendNewReservationNotification($model->id));

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

        if (!$user->hasAnyPermission([
            PermissionsConstants::Branch . '.' . $reservation->branch_id,
            PermissionsConstants::Business . '.' . $reservation->business_id,
            PermissionsConstants::Branch . '.' . $reservation->branch_id . '.' . PermissionServices::Reservations . '.' . PermissionActions::Update
        ]))
            abort(403, 'You Don\'t have permission');

        if (!isset($data['reservable_id']))
            $data['reservable_id'] = $reservation->reservable_id;

        $businessId = request()->route('businessId');
        if(Business::find($businessId)->type === BusinessTypes::CHALET) {
            if (isset($data['from']) && isset($data['to'])) {
                $this->checkAllowedReservationUnits($data, $reservation->business_id, $reservation->branch_id, $id);
            }
        }

        // TODO:: check if data['paid']
        $model = tap($reservation)
            ->update($this->process($data));

        $this->setModelRelations($model, $data);

        $this->setReservationCashedData($model->id);

        event(new UpdateReservation($model->id));
        dispatch(new SendUpdateReservationNotification($model->id));

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
            ->where(function ($query) use ($reservationData) {
                if(isset($reservationData['unit']))
                    $query->where('unit', $reservationData['unit']);
            })
            ->first();
    }

    // get current intersected reservations with the required period
    public function currentReservations($data, $businessId, $branchId, $updateId = null)
    {
        $business = Business::find($businessId);

        // Reservation Margin Before and after any reservation
        $reservationMargin = $business->getConfig(ConfigurationConstants::RESERVATIONS_MARGIN , 0);

        // Change to UTC
        $startDate =  (clone $data['from'])->subSeconds($reservationMargin);
        $endDate = (clone $data['to'])->addSeconds($reservationMargin);

        $reservable_id = $data['reservable_id'];
        return Reservation::
        select(['from', 'to' , 'unit'])
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

    public function isUnitAllowed($item, $reservations, $requiredUnit = 1)
    {
        if ($item->itemable->units < $requiredUnit)
            return false;
        foreach ($reservations as $reservation) {
            if ($reservation['unit'] === $requiredUnit)
                return false;
        }
        return true;
    }

    public function checkAllowedReservationUnits($data, $businessId, $branchId, $updateId = null)
    {
        $currentReservations = $this->currentReservations($data, $businessId, $branchId, $updateId)->toArray();

        $item = Item::with('itemable')->find($data['reservable_id']);
        if (!isset($data['unit']))
            $data['unit'] = 1;

        if( isset($data['unit']['value']) ){
            $data['unit'] = intval($data['unit']['value']);
        }

        $all = $this->isUnitAllowed($item, $currentReservations, $data['unit']);
        if (!$all)
            abort(400, "Unit isn't available, please choose different dates or try again later");

        $periodMap = array_map(function ($period) {
            return Period::make($period['from'], $period['to']);
        }, $currentReservations);
        // Add the requested period to the map
        $periodMap[] = Period::make(Carbon::parse($data['from']), Carbon::parse($data['to']));
        $collection = new PeriodCollection(...$periodMap);

        // Carbon period for the will created / updated reservation
        $period = CarbonPeriod::create($data['from'], $data['to']);
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

            if ($intersectionCount > $item->itemable->units)
                abort(400, "Not available, please choose different dates or try again later");
        }
    }

}
