<?php

namespace App\Models;

use App\Traits\Contactable;
use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Orderable;
use App\Traits\Settable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Hotel
 *
 * @property-read Collection<int, \App\Models\Contact> $contacts
 * @property-read int|null $contacts_count
 * @property-read Collection<int, \App\Models\Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @property-read \App\Models\User|null $user
 * @method static Builder|Hotel newModelQuery()
 * @method static Builder|Hotel newQuery()
 * @method static Builder|Hotel query()
 * @mixin Eloquent
 */
class Hotel extends Model
{
    use HasFactory, Settable, Contactable, Orderable, Mediable, Localizable, Discountable;
    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
