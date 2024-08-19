<?php

namespace App\Models;

use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Floor
 *
 * @property int $id
 * @property int $business_id
 * @property int $branch_id
 * @property int $sort
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Table> $tables
 * @property-read int|null $tables_count
 * @method static \Illuminate\Database\Eloquent\Builder|Floor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Floor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Floor query()
 * @method static \Illuminate\Database\Eloquent\Builder|Floor whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Floor whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Floor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Floor whereSort($value)
 * @mixin \Eloquent
 */
class Floor extends Model
{
    use HasFactory, Localizable;

    protected $guarded = ['id'];
    public $timestamps = false;

    public function tables()
    {
        return $this->hasMany(Table::class);
    }
}
