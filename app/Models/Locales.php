<?php

namespace App\Models;

use App\Constants\DatabaseTables;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locales extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = DatabaseTables::localization;
    protected $hidden = ['updated_at' , 'created_at', 'localizable_type' , 'localizable_id' ];

    public function localizable()
    {
        return $this->morphTo();
    }
}
