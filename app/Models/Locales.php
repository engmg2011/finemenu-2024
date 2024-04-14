<?php

namespace App\Models;

use App\Constants\DatabaseTables;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Locales
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $name
 * @property string|null $description
 * @property string $locale
 * @property string $localizable_type
 * @property int $localizable_id
 * @property-read Model|Eloquent $localizable
 * @method static Builder|Locales newModelQuery()
 * @method static Builder|Locales newQuery()
 * @method static Builder|Locales query()
 * @method static Builder|Locales whereCreatedAt($value)
 * @method static Builder|Locales whereDescription($value)
 * @method static Builder|Locales whereId($value)
 * @method static Builder|Locales whereLocale($value)
 * @method static Builder|Locales whereLocalizableId($value)
 * @method static Builder|Locales whereLocalizableType($value)
 * @method static Builder|Locales whereName($value)
 * @method static Builder|Locales whereUpdatedAt($value)
 * @mixin Eloquent
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
