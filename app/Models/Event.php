<?php


namespace App\Models;


use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Settable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Event
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon $start
 * @property Carbon $end
 * @property string $eventable_type
 * @property int $eventable_id
 * @property int $user_id
 * @property-read Collection<int, Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, Setting> $settings
 * @property-read int|null $settings_count
 * @property-read User $user
 * @method static Builder|Event newModelQuery()
 * @method static Builder|Event newQuery()
 * @method static Builder|Event query()
 * @method static Builder|Event whereCreatedAt($value)
 * @method static Builder|Event whereEnd($value)
 * @method static Builder|Event whereEventableId($value)
 * @method static Builder|Event whereEventableType($value)
 * @method static Builder|Event whereId($value)
 * @method static Builder|Event whereStart($value)
 * @method static Builder|Event whereUpdatedAt($value)
 * @method static Builder|Event whereUserId($value)
 * @mixin Eloquent
 */
class Event extends Model
{
    use Localizable, Settable, Mediable;

    protected $guarded = ['id'];
    protected $casts = ['start' => 'datetime', 'end' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
