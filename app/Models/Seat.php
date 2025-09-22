<?php

namespace App\Models;

use App\Traits\Localizable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Seat
 *
 * @property int $id
 * @property int|null $area_id
 * @property int $sort
 * @property-read Collection<int, Locales> $locales
 * @property-read int|null $locales_count
 * @method static Builder|Seat newModelQuery()
 * @method static Builder|Seat newQuery()
 * @method static Builder|Seat query()
 * @method static Builder|Seat whereAreaId($value)
 * @method static Builder|Seat whereId($value)
 * @method static Builder|Seat whereSort($value)
 * @mixin Eloquent
 */
class Seat extends Model
{
    use HasFactory, Localizable;

    protected $guarded = ['id'];
    public $timestamps = false;
}
