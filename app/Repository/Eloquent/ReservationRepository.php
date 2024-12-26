<?php

namespace App\Repository\Eloquent;

use App\Models\Reservation;
use App\Models\User;
use App\Repository\ReservationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;


class ReservationRepository extends BaseRepository implements ReservationRepositoryInterface
{

    public const Relations = ['reservable.locales', 'order', 'reservedBy.contacts' ,
        'reservedFor.contacts' ,'branch', 'business'];

    public function __construct(Reservation $model,)
    {
        parent::__construct($model);
    }

    public function process(array $data): array
    {
        $data['reserved_by_id'] = auth('sanctum')->user()->id;
        $data['reserved_for_id'] = request('reserved_for_id') ?? auth('sanctum')->user()->id;

        return array_only($data, [
            'from' , 'to' , 'reservable_id' , 'reservable_type',
            'data', 'order_id' , 'orderline_id' , 'reserved_by_id' , 'reserved_for_id',
            'business_id' , 'branch_id', 'created_at' , 'updated_at'
        ]);
    }

    public function get($id)
    {
        return $this->model->with(ReservationRepository::Relations)->find($id);
    }

    public function list($conditions = null)
    {
        $branchId = request()->route('branchId');
        $businessId = request()->route('branchId');
        return Reservation::with(ReservationRepository::Relations)
            ->orderByDesc('id')
            ->where(['branch_id'=> $branchId, 'business_id'=> $businessId])
            ->where(fn($q) => $conditions ? $q->where(...$conditions) : $q)
            ->orderByDesc('id')
            ->paginate(request('per-page', 15));
    }

    public function create(array $data): Model
    {
        $data['branch_id'] = request()->route('branchId');
        $data['business_id'] = request()->route('businessId');
        $model = $this->model->create($this->process($data));
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

        if (!$user->hasAnyPermission(['branch.'.$reservation->branch_id]))
            return throw new \Exception('You Don\'t have permission', 403);

        // TODO:: check if data['paid']
        $model = tap($this->model->find($id))
            ->update($this->process($data));

        return $this->get($model->id);
    }
}
