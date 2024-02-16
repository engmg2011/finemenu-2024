<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\InitRegister
 *
 * @property int $id
 * @property string|null $phone
 * @property string|null $email
 * @property string $code
 * @property int $tries_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|InitRegister newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InitRegister newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InitRegister query()
 * @method static \Illuminate\Database\Eloquent\Builder|InitRegister whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InitRegister whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InitRegister whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InitRegister whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InitRegister wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InitRegister whereTriesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InitRegister whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class InitRegister extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
}
