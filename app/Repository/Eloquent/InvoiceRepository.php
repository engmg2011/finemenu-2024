<?php

namespace App\Repository\Eloquent;

use App\Constants\PermissionsConstants;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Models\User;
use App\Repository\InvoiceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;


class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{

    public const Relations = ['reservation', 'order.prices', 'order.discounts', 'forUser.contacts',
        'byUser.contacts', 'branch', 'business'];

    public function __construct(Invoice $model,)
    {
        parent::__construct($model);
    }

    public function process(array $data): array
    {
        $data['invoice_by_id'] = auth('sanctum')->user()->id;
        $data['invoice_for_id'] = request('reserved_for_id') ?? auth('sanctum')->user()->id;

        return array_only($data, [
            'amount', 'data', 'external_link', 'reference_id',
            'note', 'type', 'status', 'status_changed_at', 'payment_type',
            'reservation_id', 'order_id', 'order_line_id', 'invoice_by_id', 'invoice_for_id',
            'business_id', 'branch_id'
        ]);
    }

    public function get($id)
    {
        return $this->model->with(InvoiceRepository::Relations)->find($id);
    }

    public function list($conditions = null)
    {
        $branchId = request()->route('branchId');
        $businessId = request()->route('branchId');
        return Invoice::with(InvoiceRepository::Relations)
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

        $data['data'] = [];

        $model = $this->model->create($this->process($data));
        return $this->get($model->id);
    }

    /**
     * @throws \Exception
     */
    public function update($id, array $data): Model
    {
        $userId = auth('sanctum')->user()->id;
        $user = User::find($userId);
        $invoice = $this->model->findOrFail($id);

        if (!$user->hasAnyPermission([PermissionsConstants::Branch . '.' . $invoice->branch_id,
            PermissionsConstants::Business . '.' . $invoice->business_id]))
            return throw new \Exception('You Don\'t have permission', 403);

        if (isset($data['status']) && $data['status'] != $invoice->status)
            $data['status_changed_at'] = now();

        $model = tap($this->model->find($id))
            ->update($this->process($data));

        return $this->get($model->id);
    }


    public function set(Reservation $reservation, array &$invoices)
    {
        // TODO :: Validate if total invoices amount == orderline price
        // TODO :: if auth user not admin -> remove status
        foreach ($invoices as &$invoice) {
            $invoiceData = $invoice + [
                    'reservation_id' => $reservation->id,
                    'order_id' => $reservation->order_id,
                    'order_line_id' => $reservation->order_line_id,
                    'invoice_by_id' => auth('sanctum')->user()->id,
                    'invoice_for_id' => $invoice['invoice_for_id'] ?? auth('sanctum')->user()->id,
                ];
            if (isset($invoice['id']) && $invoice['id'])
                $this->update($invoice['id'], $invoiceData);
            else
                $this->create($invoiceData);
        }
    }
}
