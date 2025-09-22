<?php

namespace App\Models;

use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Settable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Branch
 *
 * @property int $id
 * @property int|null $business_id
 * @property int|null $menu_id
 * @property int $sort
 * @property string|null $slug
 * @property-read \App\Models\Business|null $business
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Area> $areas
 * @property-read int|null $areas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Menu|null $menu
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Branch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Branch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Branch query()
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereSort($value)
 * @mixin \Eloquent
 */
class Branch extends Model
{
    use HasFactory, Localizable, Settable, Mediable;

    protected $guarded = ['id'];
    public $timestamps = false;

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function areas()
    {
        return $this->hasMany(Area::class, 'branch_id');
    }


}
