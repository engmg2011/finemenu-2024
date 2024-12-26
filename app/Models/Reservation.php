<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['reservable_id','reservable_type', 'reserved_by_id', 'reserved_for_id' ];

    protected $casts = ['data' => 'json'];

    public function reservable(){
        return $this->morphTo();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderline()
    {
        return $this->belongsTo(OrderLine::class);
    }

    public function reservedBy(){
        return $this->belongsTo(User::class, 'reserved_by_id');
    }

    public function reservedFor(){
        return $this->belongsTo(User::class, 'reserved_for_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

}
