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
 * App\Models\Subscription
 *
 * @property int $id
 * @property int|null $package_id
 * @property int|null $creator_id
 * @property int|null $user_id
 * @property string $status
 * @property string|null $from
 * @property string|null $to
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read \App\Models\Package|null $package
 * @property-read \App\Models\User|null $user
 * @method static Builder|Subscription newModelQuery()
 * @method static Builder|Subscription newQuery()
 * @method static Builder|Subscription query()
 * @method static Builder|Subscription whereCreatedAt($value)
 * @method static Builder|Subscription whereCreatorId($value)
 * @method static Builder|Subscription whereFrom($value)
 * @method static Builder|Subscription whereId($value)
 * @method static Builder|Subscription wherePackageId($value)
 * @method static Builder|Subscription whereStatus($value)
 * @method static Builder|Subscription whereTo($value)
 * @method static Builder|Subscription whereUpdatedAt($value)
 * @method static Builder|Subscription whereUserId($value)
 * @mixin Eloquent
 */
class Subscription extends Model
{
    use HasFactory, Localizable;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
