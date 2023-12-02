<?php

namespace App\Repository\Eloquent;

use App\Models\Subscription;
use App\Repository\SubscriptionRepositoryInterface;

class SubscriptionRepository extends BaseRepository implements SubscriptionRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Subscription $model
     */
    public function __construct(Subscription $model) {
        parent::__construct($model);
    }

}
