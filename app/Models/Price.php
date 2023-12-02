<?php


namespace App\Models;


use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use Localizable;
    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
