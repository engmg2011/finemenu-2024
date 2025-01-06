<?php

namespace App\Models;

use App\Traits\Addonable;
use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Priceable;
use App\Traits\Reservable;
use App\Traits\Settable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Item
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $category_id
 * @property int $user_id
 * @property int|null $sort
 * @property-read Collection<int, Addon> $addons
 * @property-read int|null $addons_count
 * @property-read Category|null $category
 * @property-read Collection<int, Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Collection<int, Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, Price> $prices
 * @property-read int|null $prices_count
 * @property-read User $user
 * @method static Builder|Item newModelQuery()
 * @method static Builder|Item newQuery()
 * @method static Builder|Item query()
 * @method static Builder|Item whereCategoryId($value)
 * @method static Builder|Item whereCreatedAt($value)
 * @method static Builder|Item whereId($value)
 * @method static Builder|Item whereSort($value)
 * @method static Builder|Item whereUpdatedAt($value)
 * @method static Builder|Item whereUserId($value)
 * @property-read Collection<int, DietPlan> $plans
 * @property-read int|null $plans_count
 * @mixin Eloquent
 */
class Item extends Model
{
    use HasFactory, Localizable, Mediable,
        Priceable, Discountable, Addonable, Reservable, Settable;

    protected $guarded = ['id'];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function plans(){
        return $this->belongsToMany(DietPlan::class);
    }

}
