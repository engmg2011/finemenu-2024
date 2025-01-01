<?php

namespace App\Repository\Eloquent;

use App\Constants\PermissionsConstants;
use App\Models\Item;
use App\Models\OrderLine;
use App\Models\Reservation;
use App\Models\User;
use App\Repository\InvoiceRepositoryInterface;
use App\Repository\ReservationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;


class ReservationRepository extends BaseRepository implements ReservationRepositoryInterface
{

    public const Relations = ['reservable.locales', 'order', 'reservedBy.contacts',
        'reservedFor.contacts','invoices' , 'branch.settings', 'business.settings'];

    public function __construct(Reservation $model, private InvoiceRepositoryInterface $invoiceRepository)
    {
        parent::__construct($model);
    }

    public function process(array $data): array
    {
        $data['reserved_by_id'] = auth('sanctum')->user()->id;
        $data['reserved_for_id'] = request('reserved_for_id') ?? auth('sanctum')->user()->id;

        return array_only($data, [
            "from", "to", "reservable_id", "reservable_type", "status",
            "data", "order_id", "order_line_id", "reserved_by_id", "reserved_for_id",
            "business_id", "branch_id", "created_at", "updated_at"
        ]);
    }

    public function setModelRelations($model, $data)
    {
        if(isset($data['invoices']))
            $this->invoiceRepository->set($model, $data['invoices']);
    }

    public function get($id)
    {
        return $this->model->with(ReservationRepository::Relations)->find($id);
    }

    public function list($conditions = null)
    {
        $branchId = request()->route('branchId');
        $businessId = request()->route('businessId');
        return Reservation::with(ReservationRepository::Relations)
            ->orderByDesc('id')
            ->where(['branch_id' => $branchId, 'business_id' => $businessId])
            ->where(fn($q) => $conditions ? $q->where(...$conditions) : $q)
            ->orderByDesc('id')
            ->paginate(request('per-page', 15));
    }

    public function create(array $data): Model
    {
        $data['branch_id'] = request()->route('branchId');
        $data['business_id'] = request()->route('businessId');
        $model = $this->model->create($this->process($data));
        $this->setModelRelations($model, $data);
        return $this->get($model->id);
    }

    /**
     * @throws \Exception
     */
    public function update($id, array $data): Model
    {
        // Check the user has the authority to make this order paid (admin | owner | user )
        $userId = auth('sanctum')->user()->id;
        $user = User::find($userId);
        $reservation = $this->model->findOrFail($id);

        if (!$user->hasAnyPermission([PermissionsConstants::Branch . '.' . $reservation->branch_id,
            PermissionsConstants::Business . '.' . $reservation->business_id]))
            return throw new \Exception('You Don\'t have permission', 403);

        // TODO:: check if data['paid']
        $model = tap($this->model->find($id))
            ->update($this->process($data));

        $this->setModelRelations($model, $data);
        return $this->get($model->id);
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
                'data' => $orderLine->data
            ];
        if (isset($reservationData['id']) && $reservationData['id'])
            $this->update($reservationData['id'], $reservationData);
        else
            $this->create($reservationData);
    }


}
