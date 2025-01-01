<?php


namespace App\Repository;

use App\Models\Order;
use App\Models\Reservation;

interface InvoiceRepositoryInterface
{

    public function get($id);

    public function process(array $data): array;

    public function create(array $data);

    public function update($id, array $data);

    public function list($conditions = null);

    public function setForReservation(Reservation $reservation, array &$invoices);

    public function setForOrder(Order $order, array &$orderInvoice);

}
