<?php

namespace App\Models;

use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Settable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Category
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $parent_id
 * @property int $user_id
 * @property int|null $sort
 * @property int|null $menu_id
 * @property-read \App\Models\Business|null $business
 * @property-read Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read Collection<int, \App\Models\Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Menu|null $menu
 * @property-read Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @property-read \App\Models\User $user
 * @method static Builder|Category newModelQuery()
 * @method static Builder|Category newQuery()
 * @method static Builder|Category query()
 * @method static Builder|Category whereCreatedAt($value)
 * @method static Builder|Category whereId($value)
 * @method static Builder|Category whereMenuId($value)
 * @method static Builder|Category whereParentId($value)
 * @method static Builder|Category whereSort($value)
 * @method static Builder|Category whereUpdatedAt($value)
 * @method static Builder|Category whereUserId($value)
 * @mixin Eloquent
 */
class Category extends Model
{
    use HasFactory, Localizable, Mediable, Discountable, Settable;

    protected $guarded = ['id'];

    public function items()
    {
        return $this->hasMany(Item::class)->orderBy('sort');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->with(['locales', 'media', 'settings',
                'items.locales',
                'items.addons.locales',
                'items.addons.locales',
                'items.discounts.locales',
                'items.media',
                'items.prices.locales',
                'items.itemable',
                'items.holidays.locales']);
    }

    public function childrenNested()
    {
        return $this->children()->with('childrenNested');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
