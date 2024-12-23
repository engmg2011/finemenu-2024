<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
