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
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property int $user_id
 * @property string|null $passcode
 * @property string|null $slug
 * @property int|null $creator_id
 * @property-read Collection<int, Contact> $contacts
 * @property-read int|null $contacts_count
 * @property-read Collection<int, Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Collection<int, Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, Setting> $settings
 * @property-read int|null $settings_count
 * @property-read User $user
 * @method static Builder|Hotel newModelQuery()
 * @method static Builder|Hotel newQuery()
 * @method static Builder|Hotel query()
 * @method static Builder|Hotel whereCreatedAt($value)
 * @method static Builder|Hotel whereCreatorId($value)
 * @method static Builder|Hotel whereId($value)
 * @method static Builder|Hotel whereName($value)
 * @method static Builder|Hotel wherePasscode($value)
 * @method static Builder|Hotel whereSlug($value)
 * @method static Builder|Hotel whereUpdatedAt($value)
 * @method static Builder|Hotel whereUserId($value)
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
