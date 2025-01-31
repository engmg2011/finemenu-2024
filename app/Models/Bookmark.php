<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Bookmark
 *
 * @property int $id
 * @property int $item_id
 * @property int $user_id
 * @property int $branch_id
 * @property int $business_id
 * @property-read \App\Models\Item $item
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark query()
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark whereUserId($value)
 * @mixin \Eloquent
 */
class Bookmark extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $hidden = ['user_id' ,'branch_id' , 'business_id'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'bookmark_user')->withTimestamps();
    }
}
