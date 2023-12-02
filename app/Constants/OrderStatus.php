<?php

namespace App\Constants;

class OrderStatus
{
    const Pending = 'pending',
        Accepted = 'accepted',
        Rejected = 'rejected',
        Ready = 'ready',
        Delivered = 'delivered',
        Cancelled = 'cancelled';
}
