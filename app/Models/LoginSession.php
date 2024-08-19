<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LoginSession
 *
 * @property int $id
 * @property string $login_session
 * @property string $valid_until
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereLoginSession($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereValidUntil($value)
 * @mixin \Eloquent
 */
class LoginSession extends Model
{
    use HasFactory;
    protected $guarded= ['id'];
}
