<?php

namespace App\Models;

use App\Traits\Localizable;
use App\Traits\Orderable;
use App\Traits\Priceable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Package
 *
 * @property int $id
 * @property int $days
 * @property string $type
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, Price> $prices
 * @property-read int|null $prices_count
 * @method static Builder|Package newModelQuery()
 * @method static Builder|Package newQuery()
 * @method static Builder|Package query()
 * @method static Builder|Package whereCreatedAt($value)
 * @method static Builder|Package whereDays($value)
 * @method static Builder|Package whereId($value)
 * @method static Builder|Package whereSlug($value)
 * @method static Builder|Package whereType($value)
 * @method static Builder|Package whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Package extends Model
{
    use HasFactory, Localizable, Priceable, Orderable;

    protected $guarded = ['id'];
}
