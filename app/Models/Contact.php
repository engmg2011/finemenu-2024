<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Contact
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $media
 * @property string $value
 * @property string $contactable_type
 * @property int $contactable_id
 * @property-read Model|Eloquent $contactable
 * @method static Builder|Contact newModelQuery()
 * @method static Builder|Contact newQuery()
 * @method static Builder|Contact query()
 * @method static Builder|Contact whereContactableId($value)
 * @method static Builder|Contact whereContactableType($value)
 * @method static Builder|Contact whereCreatedAt($value)
 * @method static Builder|Contact whereId($value)
 * @method static Builder|Contact whereMedia($value)
 * @method static Builder|Contact whereUpdatedAt($value)
 * @method static Builder|Contact whereValue($value)
 * @mixin Eloquent
 */
class Contact extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function contactable(){
        return $this->morphTo();
    }

}
