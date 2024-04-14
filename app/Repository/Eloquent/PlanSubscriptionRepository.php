<?php

namespace App\Repository\Eloquent;

use App\Models\DietPlanSubscription;
use App\Repository\PlanSubscriptionRepositoryInterface;

class PlanSubscriptionRepository extends BaseRepository implements PlanSubscriptionRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param DietPlanSubscription $model
     */
    public function __construct(DietPlanSubscription $model) {
        parent::__construct($model);
    }

}
