<?php

namespace App\Constants;

class PaymentConstants
{
    const RESERVATION_PENDING = "pending";
    const RESERVATION_CANCELED = "canceled";
    const RESERVATION_COMPLETED = "completed";

    const INVOICE_CREDIT = "credit";
    const INVOICE_DEBIT = "debit";

    const INVOICE_PENDING = "pending";
    const INVOICE_PAID = "paid";
    const INVOICE_CANCELED = "canceled";
    const INVOICE_REFUNDED = "refunded";

    const TYPE_CASH = "cash";
    const TYPE_ONLINE = "online";
    const TYPE_CHECK = "check";
    const TYPE_TRANSFER = "transfer";
    const TYPE_KNET = "knet";
    const TYPE_LINK = "link";
    const TYPE_WAMD = "wamd";

}
