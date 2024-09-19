<?php


namespace App\Models;


use App\Traits\Localizable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Media
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $src
 * @property string $type
 * @property string $mediable_type
 * @property int $mediable_id
 * @property int $user_id
 * @property string|null $slug
 * @property-read Collection<int, Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Model|Eloquent $mediable
 * @property-read User $user
 * @method static Builder|Media newModelQuery()
 * @method static Builder|Media newQuery()
 * @method static Builder|Media query()
 * @method static Builder|Media whereCreatedAt($value)
 * @method static Builder|Media whereId($value)
 * @method static Builder|Media whereMediableId($value)
 * @method static Builder|Media whereMediableType($value)
 * @method static Builder|Media whereSlug($value)
 * @method static Builder|Media whereSrc($value)
 * @method static Builder|Media whereType($value)
 * @method static Builder|Media whereUpdatedAt($value)
 * @method static Builder|Media whereUserId($value)
 * @mixin Eloquent
 */
class Media extends Model
{
    use Localizable;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mediable()
    {
        return $this->morphTo();
    }

    public function getSrcAttribute($src)
    {
        return ( strpos($src, "http:") === 0 ||  strpos($src, "https:") === 0 ) ?
            str_replace("http:", "https:", $src)
            : url($src);
    }


}
