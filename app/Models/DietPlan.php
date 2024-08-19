<?php

namespace App\Models;

use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Priceable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\DietPlan
 *
 * @property int $business_id
 * @property-read Collection<int, Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Collection<int, Item> $items
 * @property-read int|null $items_count
 * @property-read Collection<int, Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, Price> $prices
 * @property-read int|null $prices_count
 * @method static Builder|DietPlan newModelQuery()
 * @method static Builder|DietPlan newQuery()
 * @method static Builder|DietPlan query()
 * @property int $id
 * @property int $user_id
 * @property int|null $category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|DietPlan whereBusinessId($value)
 * @method static Builder|DietPlan whereCategoryId($value)
 * @method static Builder|DietPlan whereCreatedAt($value)
 * @method static Builder|DietPlan whereId($value)
 * @method static Builder|DietPlan whereUpdatedAt($value)
 * @method static Builder|DietPlan whereUserId($value)
 * @mixin Eloquent
 */
class DietPlan extends Model
{
    use HasFactory, Localizable, Priceable, Mediable, Discountable;
    protected $guarded = ['id'];
    protected $hidden = ['updated_at' , 'created_at'];

    public function items(){
        return $this->belongsToMany(Item::class, 'diet_plan_item');
    }
}
