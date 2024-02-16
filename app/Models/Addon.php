<?php

namespace App\Models;

use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Priceable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Addon
 *
 * @property int $id
 * @property int $addonable_id
 * @property string $addonable_type
 * @property float|null $price
 * @property int $multiple
 * @property int|null $max
 * @property int $user_id
 * @property int|null $parent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Addon> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Price> $prices
 * @property-read int|null $prices_count
 * @method static \Illuminate\Database\Eloquent\Builder|Addon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Addon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Addon query()
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereAddonableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereAddonableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereMultiple($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereUserId($value)
 * @mixin \Eloquent
 */
class Addon extends Model
{
    use HasFactory, Priceable, Localizable, Mediable;
    protected $guarded = ['id'];

    public function children()
    {
        return $this->hasMany(Addon::class, 'parent_id');
    }
}
