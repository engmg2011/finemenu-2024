<?php

namespace App\Repository\Eloquent;

use App\Models\DietPlanSubscription;
use App\Repository\DietPlanSubscriptionRepositoryInterface;

class DietPlanSubscriptionRepository extends BaseRepository implements DietPlanSubscriptionRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param DietPlanSubscription $model
     */
    public function __construct(DietPlanSubscription $model) {
        parent::__construct($model);
    }

}
