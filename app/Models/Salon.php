<?php

namespace App\Models;

use App\Traits\Contactable;
use App\Traits\Contentable;
use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Orderable;
use App\Traits\Settable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Salon
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Branch> $branches
 * @property-read int|null $branches_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Contact> $contacts
 * @property-read int|null $contacts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Content> $contents
 * @property-read int|null $contents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Salon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Salon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Salon query()
 * @mixin \Eloquent
 */
class Salon extends Model
{
    use Localizable, Settable, Contactable, Orderable, Mediable, Discountable, Contentable;
    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function categories(): HasMany {
        return $this->hasMany(Category::class)->where('parent_id' , null)->orderBy('sort');
    }

    public function branches(): HasMany{
        return $this->hasMany(Branch::class);
    }
}
