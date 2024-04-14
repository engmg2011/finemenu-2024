<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\IpTries
 *
 * @property int $id
 * @property string $ip
 * @property int $tries
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|IpTries newModelQuery()
 * @method static Builder|IpTries newQuery()
 * @method static Builder|IpTries query()
 * @method static Builder|IpTries whereCreatedAt($value)
 * @method static Builder|IpTries whereId($value)
 * @method static Builder|IpTries whereIp($value)
 * @method static Builder|IpTries whereTries($value)
 * @method static Builder|IpTries whereUpdatedAt($value)
 * @mixin Eloquent
 */
class IpTries extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
}
