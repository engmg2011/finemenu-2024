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
 * App\Models\Discount
 *
 * @property int $id
 * @property int $discountable_id
 * @property string $discountable_type
 * @property float $amount
 * @property string $type
 * @property string|null $from
 * @property string|null $to
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Locales> $locales
 * @property-read int|null $locales_count
 * @method static Builder|Discount newModelQuery()
 * @method static Builder|Discount newQuery()
 * @method static Builder|Discount query()
 * @method static Builder|Discount whereAmount($value)
 * @method static Builder|Discount whereCreatedAt($value)
 * @method static Builder|Discount whereDiscountableId($value)
 * @method static Builder|Discount whereDiscountableType($value)
 * @method static Builder|Discount whereFrom($value)
 * @method static Builder|Discount whereId($value)
 * @method static Builder|Discount whereTo($value)
 * @method static Builder|Discount whereType($value)
 * @method static Builder|Discount whereUpdatedAt($value)
 * @method static Builder|Discount whereUserId($value)
 * @mixin Eloquent
 */
class Discount extends Model
{
    use HasFactory, Localizable;
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at' , 'discountable_type' , 'discountable_id'];
}
