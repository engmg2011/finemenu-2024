<?php


namespace App\Models;


use App\Traits\Contactable;
use App\Traits\Contentable;
use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Orderable;
use App\Traits\Settable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    use Localizable, Settable, Contactable, Orderable, Mediable, Discountable, Contentable;
    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function categories(): HasMany {
        return $this->hasMany(Category::class)->where('parent_id' , null)->orderBy('sort');
    }
}
