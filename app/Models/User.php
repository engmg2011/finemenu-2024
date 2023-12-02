<?php

namespace App\Models;

use App\Traits\Contactable;
use App\Traits\Mediable;
use App\Traits\Settable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Settable, Contactable, Mediable, HasRoles, HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'remember_token'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token',];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['email_verified_at' => 'datetime',];

    public function items(): HasMany {
        return $this->hasMany(Item::class)->orderBy('sort');
    }

    public function categories(): HasMany {
        return $this->hasMany(Category::class)->where('parent_id' , null)->orderBy('sort');
    }

    public function restaurants(): HasMany {
        return $this->hasMany(Restaurant::class);
    }

    public function hotels(): HasMany {
        return $this->hasMany(Hotel::class);
    }

    public function services(): HasMany {
        return $this->hasMany(Service::class);
    }

    public function devices() {
        return $this->hasMany(Device::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);

    }

    public function routeNotificationForOneSignal()
    {
        $playerIds = [];
        foreach (User::find(1)->devices as $device){
            $playerIds[] = $device->player_id;
        }
        return $playerIds;
    }



}
