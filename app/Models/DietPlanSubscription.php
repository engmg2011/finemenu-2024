<?php

namespace App\Models;

use App\Traits\Localizable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\DietPlanSubscription
 *
 * @property int $id
 * @property int|null $diet_plan_id
 * @property int|null $creator_id
 * @property int|null $user_id
 * @property int $business_id
 * @property string $status
 * @property string $payment_status
 * @property array $selected_meals
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\DietPlan|null $dietPlan
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read \App\Models\User|null $user
 * @method static Builder|DietPlanSubscription newModelQuery()
 * @method static Builder|DietPlanSubscription newQuery()
 * @method static Builder|DietPlanSubscription query()
 * @method static Builder|DietPlanSubscription whereBusinessId($value)
 * @method static Builder|DietPlanSubscription whereCreatedAt($value)
 * @method static Builder|DietPlanSubscription whereCreatorId($value)
 * @method static Builder|DietPlanSubscription whereDietPlanId($value)
 * @method static Builder|DietPlanSubscription whereId($value)
 * @method static Builder|DietPlanSubscription wherePaymentStatus($value)
 * @method static Builder|DietPlanSubscription whereSelectedMeals($value)
 * @method static Builder|DietPlanSubscription whereStatus($value)
 * @method static Builder|DietPlanSubscription whereUpdatedAt($value)
 * @method static Builder|DietPlanSubscription whereUserId($value)
 * @mixin Eloquent
 */
class DietPlanSubscription extends Model
{
    use HasFactory, Localizable;

    protected $guarded = ['id'];
    protected $casts = ['selected_meals'=>'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dietPlan(): BelongsTo
    {
        return $this->belongsTo(DietPlan::class);
    }
}
