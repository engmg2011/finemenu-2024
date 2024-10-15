<?php

namespace App\Models;

use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Priceable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Addon> $children
 * @property-read int|null $children_count
 * @property-read Collection<int, Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, Price> $prices
 * @property-read int|null $prices_count
 * @method static Builder|Addon newModelQuery()
 * @method static Builder|Addon newQuery()
 * @method static Builder|Addon query()
 * @method static Builder|Addon whereAddonableId($value)
 * @method static Builder|Addon whereAddonableType($value)
 * @method static Builder|Addon whereCreatedAt($value)
 * @method static Builder|Addon whereId($value)
 * @method static Builder|Addon whereMax($value)
 * @method static Builder|Addon whereMultiple($value)
 * @method static Builder|Addon whereParentId($value)
 * @method static Builder|Addon wherePrice($value)
 * @method static Builder|Addon whereUpdatedAt($value)
 * @method static Builder|Addon whereUserId($value)
 * @mixin Eloquent
 */
class Addon extends Model
{
    use HasFactory, Localizable, Mediable;
    protected $guarded = ['id'];

    public function children()
    {
        return $this->hasMany(Addon::class, 'parent_id');
    }
}
