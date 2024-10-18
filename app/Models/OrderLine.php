<?php

namespace App\Models;

use App\Traits\Addonable;
use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Priceable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\OrderLine
 *
 * @property int $id
 * @property string|null $note
 * @property int|null $item_id
 * @property int|null $order_id
 * @property int|null $content_id
 * @property int|null $user_id
 * @property int|null $count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Addon> $addons
 * @property-read int|null $addons_count
 * @property-read Collection<int, Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Item|null $item
 * @property-read Collection<int, Price> $prices
 * @property-read int|null $prices_count
 * @property-read User|null $user
 * @method static Builder|OrderLine newModelQuery()
 * @method static Builder|OrderLine newQuery()
 * @method static Builder|OrderLine query()
 * @method static Builder|OrderLine whereContentId($value)
 * @method static Builder|OrderLine whereCount($value)
 * @method static Builder|OrderLine whereCreatedAt($value)
 * @method static Builder|OrderLine whereId($value)
 * @method static Builder|OrderLine whereItemId($value)
 * @method static Builder|OrderLine whereNote($value)
 * @method static Builder|OrderLine whereOrderId($value)
 * @method static Builder|OrderLine whereUpdatedAt($value)
 * @method static Builder|OrderLine whereUserId($value)
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @mixin Eloquent
 */
class OrderLine extends Model
{
    use HasFactory, Priceable, Addonable, Discountable, Localizable;
    protected $guarded = ['id'];
    protected $casts = ['data'=>'json'];
    protected $hidden = ['created_at', 'updated_at'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function item() {
       return $this->belongsTo(Item::class);
    }

}
