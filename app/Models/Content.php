<?php

namespace App\Models;

use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Content
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $parent_id
 * @property string $contentable_type
 * @property int $contentable_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Content> $children
 * @property-read int|null $children_count
 * @property-read Model|\Eloquent $contentable
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @method static \Illuminate\Database\Eloquent\Builder|Content newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Content newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Content query()
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereContentableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereContentableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Content whereUserId($value)
 * @mixin \Eloquent
 */
class Content extends Model
{
    use HasFactory, Localizable;
    protected $guarded=['id'];


    public function contentable()
    {
        return $this->morphTo();
    }

    public function children() {
        return $this->hasMany(Content::class, 'parent_id')->orderBy('id', 'desc');
    }
}
