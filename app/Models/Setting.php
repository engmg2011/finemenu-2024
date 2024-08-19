<?php


namespace App\Models;


use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Setting
 *
 * @property int $id
 * @property array|null $data
 * @property string $settable_type
 * @property int $settable_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @method static Builder|Setting newModelQuery()
 * @method static Builder|Setting newQuery()
 * @method static Builder|Setting query()
 * @method static Builder|Setting whereCreatedAt($value)
 * @method static Builder|Setting whereData($value)
 * @method static Builder|Setting whereId($value)
 * @method static Builder|Setting whereSettableId($value)
 * @method static Builder|Setting whereSettableType($value)
 * @method static Builder|Setting whereUpdatedAt($value)
 * @method static Builder|Setting whereUserId($value)
 * @property string $key
 * @method static Builder|Setting whereKey($value)
 * @mixin Eloquent
 */
class Setting extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['data' => 'json'];
    protected $hidden = ['created_at', 'updated_at', 'user_id' , 'settable_type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
