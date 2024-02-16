<?php

namespace App\Models;

use App\Constants\DatabaseTables;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Locales
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $name
 * @property string|null $description
 * @property string $locale
 * @property string $localizable_type
 * @property int $localizable_id
 * @property-read Model|\Eloquent $localizable
 * @method static \Illuminate\Database\Eloquent\Builder|Locales newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Locales newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Locales query()
 * @method static \Illuminate\Database\Eloquent\Builder|Locales whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Locales whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Locales whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Locales whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Locales whereLocalizableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Locales whereLocalizableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Locales whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Locales whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Locales extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = DatabaseTables::localization;
    protected $hidden = ['updated_at' , 'created_at', 'localizable_type' , 'localizable_id' ];

    public function localizable()
    {
        return $this->morphTo();
    }
}
