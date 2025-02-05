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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @property bool $hide
 * @property bool $disable_ordering
 * @property int|null $itemable_id
 * @property string|null $itemable_type
 * @property-read Collection<int, \App\Models\Addon> $addons
 * @property-read int|null $addons_count
 * @property-read \App\Models\Category|null $category
 * @property-read Collection<int, \App\Models\Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Model|\Eloquent $itemable
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, \App\Models\DietPlan> $plans
 * @property-read int|null $plans_count
 * @property-read Collection<int, \App\Models\Price> $prices
 * @property-read int|null $prices_count
 * @property-read Collection<int, \App\Models\Reservation> $reservations
 * @property-read int|null $reservations_count
 * @property-read Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @property-read \App\Models\User $user
 * @method static Builder|Item newModelQuery()
 * @method static Builder|Item newQuery()
 * @method static Builder|Item query()
 * @method static Builder|Item whereCategoryId($value)
 * @method static Builder|Item whereCreatedAt($value)
 * @method static Builder|Item whereDisableOrdering($value)
 * @method static Builder|Item whereHide($value)
 * @method static Builder|Item whereId($value)
 * @method static Builder|Item whereItemableId($value)
 * @method static Builder|Item whereItemableType($value)
 * @method static Builder|Item whereSort($value)
 * @method static Builder|Item whereUpdatedAt($value)
 * @method static Builder|Item whereUserId($value)
 * @mixin Eloquent
 */
class Item extends Model
{
    use HasFactory, Localizable, Mediable,
        Priceable, Discountable, Addonable, Reservable, Settable;

    protected $guarded = ['id'];
    protected $casts = ['hide' => 'boolean', 'disable_ordering' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plans()
    {
        return $this->belongsToMany(DietPlan::class);
    }

    public function itemable()
    {
        return $this->morphTo();
    }

    public function holidays(): BelongsToMany
    {
        return $this->belongsToMany(Holiday::class)->withPivot('price');
    }
}
