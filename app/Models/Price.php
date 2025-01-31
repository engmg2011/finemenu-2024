<?php


namespace App\Models;


use App\Traits\Localizable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Price
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property float $price
 * @property string $priceable_type
 * @property int $priceable_id
 * @property int $user_id
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Model|\Eloquent $priceable
 * @property-read \App\Models\User $user
 * @method static Builder|Price newModelQuery()
 * @method static Builder|Price newQuery()
 * @method static Builder|Price query()
 * @method static Builder|Price whereCreatedAt($value)
 * @method static Builder|Price whereId($value)
 * @method static Builder|Price wherePrice($value)
 * @method static Builder|Price wherePriceableId($value)
 * @method static Builder|Price wherePriceableType($value)
 * @method static Builder|Price whereUpdatedAt($value)
 * @method static Builder|Price whereUserId($value)
 * @mixin Eloquent
 */
class Price extends Model
{
    use Localizable;
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'priceable_id' , 'priceable_type'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function priceable()
    {
        return $this->morphTo();
    }

}
