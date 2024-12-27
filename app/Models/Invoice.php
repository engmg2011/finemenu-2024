<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = ['data' => 'json'];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function forUser()
    {
        return $this->belongsTo(User::class, 'invoice_for_id');
    }

    public function byUser()
    {
        return $this->belongsTo(User::class , 'invoice_by_id');
    }

    public function business(){
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }


}
