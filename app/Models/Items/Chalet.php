<?php

namespace App\Models\Items;

use App\Models\Item;
use App\Traits\Itemable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Items\Chalet
 *
 * @property int $id
 * @property int|null $insurance
 * @property float|null $latitude
 * @property float|null $longitude
 * @property array|null $address
 * @property string|null $frontage
 * @property int $bedrooms
 * @property int $item_id
 * @property int|null $owner_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Item|null $item
 * @method static Builder|Chalet newModelQuery()
 * @method static Builder|Chalet newQuery()
 * @method static Builder|Chalet query()
 * @method static Builder|Chalet whereAddress($value)
 * @method static Builder|Chalet whereBedrooms($value)
 * @method static Builder|Chalet whereCreatedAt($value)
 * @method static Builder|Chalet whereFrontage($value)
 * @method static Builder|Chalet whereId($value)
 * @method static Builder|Chalet whereInsurance($value)
 * @method static Builder|Chalet whereItemId($value)
 * @method static Builder|Chalet whereLatitude($value)
 * @method static Builder|Chalet whereLongitude($value)
 * @method static Builder|Chalet whereOwnerId($value)
 * @method static Builder|Chalet whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Chalet extends Model
{
    use HasFactory, Itemable;
    protected $guarded=['id'];
    protected $casts = ['address' => 'json'];
    protected $hidden = ['created_at','updated_at'];

}
