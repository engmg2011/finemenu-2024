<?php

namespace App\Constants;

class PaymentConstants
{
    const CREDIT = "credit";
    const DEBIT = "debit";

    const STATUS_PENDING = "pending";
    const STATUS_PAID = "paid";
    const STATUS_CANCELED = "canceled";
    const STATUS_REFUNDED = "refunded";

    const TYPE_CASH = "cash";
    const TYPE_ONLINE = "online";
    const TYPE_CHECK = "check";
    const TYPE_TRANSFER = "transfer";
    const TYPE_KNET = "knet";
    const TYPE_LINK = "link";
    const TYPE_WAMD = "wamd";

}
