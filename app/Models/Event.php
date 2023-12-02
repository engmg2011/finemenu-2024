<?php


namespace App\Models;


use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Settable;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use Localizable, Settable, Mediable;

    protected $guarded = ['id'];
    protected $casts = ['start' => 'datetime', 'end' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
