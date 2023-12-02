<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['data' => 'json'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
