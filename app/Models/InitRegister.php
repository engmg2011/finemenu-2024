<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\InitRegister
 *
 * @property int $id
 * @property string|null $phone
 * @property string|null $email
 * @property string $code
 * @property int $tries_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|InitRegister newModelQuery()
 * @method static Builder|InitRegister newQuery()
 * @method static Builder|InitRegister query()
 * @method static Builder|InitRegister whereCode($value)
 * @method static Builder|InitRegister whereCreatedAt($value)
 * @method static Builder|InitRegister whereEmail($value)
 * @method static Builder|InitRegister whereId($value)
 * @method static Builder|InitRegister wherePhone($value)
 * @method static Builder|InitRegister whereTriesCount($value)
 * @method static Builder|InitRegister whereUpdatedAt($value)
 * @mixin Eloquent
 */
class InitRegister extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
}
