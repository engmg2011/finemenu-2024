<?php

namespace App\Repository\Eloquent;

use App\Constants\PaymentConstants;
use App\Constants\PermissionsConstants;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\User;
use App\Repository\InvoiceRepositoryInterface;
use App\Services\PaymentProviders\Hesabe;
use App\Services\PaymentProviders\PaymentService;
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
        $data['reference_id'] = strtoupper(uniqid());
        $data['invoice_by_id'] = auth('sanctum')->user()->id;
        $data['invoice_for_id'] = $invoice['invoice_for_id'] ?? auth('sanctum')->user()->id;
        $data['data'] = [];
        if (isset($data['payment_type']) && $data['payment_type'] === PaymentConstants::TYPE_ONLINE)
            $data['external_link'] = route('payment.hesabe-checkout', ['referenceId' => $data['reference_id']]);
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
            ->update(array_only($data, ['status', 'status_changed_at']));

        return $this->get($model->id);
    }


    public function setForReservation(Reservation $reservation, array &$invoices)
    {
        // TODO :: Validate if total invoices amount == orderline price
        // TODO :: if auth user not admin -> remove status
        foreach ($invoices as &$invoice) {
            $invoiceData = $invoice + [
                    'reservation_id' => $reservation->id,
                    'order_id' => $reservation->order_id,
                    'order_line_id' => $reservation->order_line_id,
                ];
            if (isset($invoice['id']) && $invoice['id'])
                $this->update($invoice['id'], $invoiceData);
            else
                $this->create($invoiceData);
        }
    }

    public function updateReservationInvoicesData($reservationId , $invoices)
    {
        $res = Reservation::find($reservationId);
        $cachedReservationData = $res->data;
        $cachedReservationData['invoices'] = $invoices;
        $res->update(['data' => $cachedReservationData]);
    }

    public function setForOrder(Order $order, array &$orderInvoice)
    {
        /**
         * assume
         *  - one acceptable reservation
         *  - reservation on orderline[0]
         */
        $invoices = [];
        $invoiceData = [
            'payment_type' => $orderInvoice['payment_type'],
            'note' => $orderInvoice['payment_type'],
            'order_id' => $order->id
        ];
        $reservation = $order->orderLines[0]->reservation;
        if ($reservation) {
            $invoiceData = $invoiceData + ['reservation_id' => $reservation->id];
        }
        $invoices[0] = $invoiceData + [
                'type' => PaymentConstants::INVOICE_CREDIT,
                'amount' => $order->total_price,
                'order_line_id' => $order->orderLines[0]->order_line_id,
            ];
        $i = 1;
        foreach ($order->orderlines as &$orderLine) {
            if ($orderLine->item->insurance) {
                $invoices[$i] = $invoiceData;
                $invoices[$i]['type'] = PaymentConstants::INVOICE_DEBIT;
                $invoices[$i]['amount'] = $orderLine->item->insurance;
                $invoices[$i]['orderline_id'] = $orderLine->id;
                $i++;
            }
        }
        foreach ($invoices as &$invoice) {
            if (isset($invoice['id']) && $invoice['id'])
                $this->update($invoice['id'], $invoice);
            else
                $this->create($invoice);
        }
        if($reservation)
            $this->updateReservationInvoicesData($reservation->id , $invoices);
    }

    public function pay($referenceNumber)
    {
        $invoice = $this->model->findOrFail(['reference_id' => $referenceNumber]);
        $invoice->update(["payment_type" => PaymentConstants::TYPE_ONLINE]);
        $paymentService = new PaymentService(new Hesabe());
        $paymentService->checkout($invoice->reference_id);
    }
}
