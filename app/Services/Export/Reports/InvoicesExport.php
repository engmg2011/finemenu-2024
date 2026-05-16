<?php
namespace App\Services\Export\Reports;

class InvoicesExport
{
    public function headers(): array
    {
        return [
            'ID',
            'Amount',
            'Type',
            'Status',
            'Payment Type',
            'Reservation ID',
            'Order ID',
            'Reference ID',
            'Created At',
            'Paid At',
            'Guest Name',
            'Guest Email',
            'Guest Phone',
        ];
    }

    public function rows(array $invoices): array
    {
        return collect($invoices)->map(function ($invoice) {
            $guest = $invoice['for_user'] ?? null;
            return [
                $invoice['id'],
                $invoice['amount'],
                $invoice['type'],
                $invoice['status'],
                $invoice['payment_type'],
                $invoice['reservation_id'],
                $invoice['order_id'],
                $invoice['reference_id'],
                $invoice['created_at'],
                $invoice['paid_at'],
                $guest? $guest['name'] : null,
                $guest? $guest['email'] : null,
                $guest? $guest['phone'] : null,
            ];
        })->toArray();
    }
}
