<?php

namespace App\Models\Items;

use App\Traits\Itemable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item|null $item
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet query()
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereBedrooms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereFrontage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Chalet extends Model
{
    use HasFactory, Itemable;
    protected $guarded=['id'];
    protected $casts = ['address' => 'json'];

}
