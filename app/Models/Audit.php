<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = ['data' => 'json', 'request' => 'json'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
