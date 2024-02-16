<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\IpTries
 *
 * @property int $id
 * @property string $ip
 * @property int $tries
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|IpTries newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IpTries newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IpTries query()
 * @method static \Illuminate\Database\Eloquent\Builder|IpTries whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpTries whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpTries whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpTries whereTries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpTries whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IpTries extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
}
