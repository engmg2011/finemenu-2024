<?php

namespace App\Models;

use App\Traits\Localizable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Content
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $parent_id
 * @property string $contentable_type
 * @property int $contentable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Content> $children
 * @property-read int|null $children_count
 * @property-read Model|\Eloquent $contentable
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @method static Builder|Content newModelQuery()
 * @method static Builder|Content newQuery()
 * @method static Builder|Content query()
 * @method static Builder|Content whereContentableId($value)
 * @method static Builder|Content whereContentableType($value)
 * @method static Builder|Content whereCreatedAt($value)
 * @method static Builder|Content whereId($value)
 * @method static Builder|Content whereParentId($value)
 * @method static Builder|Content whereUpdatedAt($value)
 * @method static Builder|Content whereUserId($value)
 * @mixin Eloquent
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
