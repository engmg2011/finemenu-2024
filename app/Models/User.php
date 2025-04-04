<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Contactable;
use App\Traits\Mediable;
use App\Traits\Settable;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Eloquent;
use http\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Passport\Token;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $provider
 * @property string|null $provider_id
 * @property int|null $business_id
 * @property-read Collection<int, \App\Models\Business> $business
 * @property-read int|null $business_count
 * @property-read Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read Collection<int, \App\Models\Contact> $contacts
 * @property-read int|null $contacts_count
 * @property-read Collection<int, \App\Models\Device> $devices
 * @property-read int|null $devices_count
 * @property-read Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @property-read Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read string $profile_photo_url
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, \App\Models\Service> $services
 * @property-read int|null $services_count
 * @property-read Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @property-read Collection<int, \App\Models\Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User permission($permissions, $without = false)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null, $without = false)
 * @method static Builder|User whereBusinessId($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User whereProvider($value)
 * @method static Builder|User whereProviderId($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereTwoFactorConfirmedAt($value)
 * @method static Builder|User whereTwoFactorRecoveryCodes($value)
 * @method static Builder|User whereTwoFactorSecret($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User withoutPermission($permissions)
 * @method static Builder|User withoutRole($roles, $guard = null)
 * @mixin Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,
        HasProfilePhoto, TwoFactorAuthenticatable,
        Settable, Contactable, Mediable, HasRoles, HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id', 'remember_token'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'control' => 'array',
        'dashboard_access' => 'boolean'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function items(): HasMany {
        return $this->hasMany(Item::class)->orderBy('sort');
    }

    public function categories(): HasMany {
        return $this->hasMany(Category::class)->where('parent_id' , null)->orderBy('sort');
    }

    public function business(): HasMany {
        return $this->hasMany(Business::class);
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
/*
    public function routeNotificationForOneSignal()
    {
        $playerIds = [];
        foreach (User::find(1)->devices as $device){
            $playerIds[] = $device->onesignal_token;
        }
        return $playerIds;
    }*/


}
