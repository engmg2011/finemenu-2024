<?php

namespace App\Models;

use App\Traits\Localizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory, Localizable;
    protected $guarded=['id'];


    public function contentable()
    {
        return $this->morphTo();
    }

    public function children() {
        return $this->hasMany(Content::class, 'parent_id')->orderBy('id', 'desc');
    }
}
