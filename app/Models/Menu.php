<?php

namespace App\Models;

use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Settable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Menu
 *
 * @property int $id
 * @property string $slug
 * @property string|null $type
 * @property int $business_id
 * @property int $user_id
 * @property int $sort
 * @property-read \App\Models\Business $business
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereUserId($value)
 * @mixin \Eloquent
 */
class Menu extends Model
{
    use HasFactory, Localizable, Mediable, Settable;

    protected $guarded = ['id'];
    public $timestamps = false;


    public function categories(): HasMany {
        return $this->hasMany(Category::class)->where('parent_id' , null)->orderBy('sort');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
