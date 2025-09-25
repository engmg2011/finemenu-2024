<?php

namespace App\Models;

use App\Traits\Localizable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Area
 *
 * @property int $id
 * @property int $business_id
 * @property int $branch_id
 * @property int $sort
 * @property-read Collection<int, Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, Seat> $tables
 * @property-read int|null $tables_count
 * @method static Builder|Area newModelQuery()
 * @method static Builder|Area newQuery()
 * @method static Builder|Area query()
 * @method static Builder|Area whereBranchId($value)
 * @method static Builder|Area whereBusinessId($value)
 * @method static Builder|Area whereId($value)
 * @method static Builder|Area whereSort($value)
 * @mixin Eloquent
 */
class Area extends Model
{
    use HasFactory, Localizable;

    protected $guarded = ['id'];
    public $timestamps = false;

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }
}
