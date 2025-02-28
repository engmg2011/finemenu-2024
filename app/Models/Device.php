<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

/**
 * App\Models\Device
 *
 * @property int $id
 * @property string $device_name
 * @property string|null $token_id
 * @property string|null $onesignal_token
 * @property mixed|null $info
 * @property string $last_active
 * @property string|null $os
 * @property mixed|null $versions
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\User $user
 * @method static Builder|Device newModelQuery()
 * @method static Builder|Device newQuery()
 * @method static Builder|Device query()
 * @method static Builder|Device whereCreatedAt($value)
 * @method static Builder|Device whereDeviceName($value)
 * @method static Builder|Device whereId($value)
 * @method static Builder|Device whereInfo($value)
 * @method static Builder|Device whereLastActive($value)
 * @method static Builder|Device whereOnesignalToken($value)
 * @method static Builder|Device whereOs($value)
 * @method static Builder|Device whereTokenId($value)
 * @method static Builder|Device whereUpdatedAt($value)
 * @method static Builder|Device whereUserId($value)
 * @method static Builder|Device whereVersions($value)
 * @mixin Eloquent
 */
class Device extends Model
{
    use HasFactory, Notifiable;
    protected $guarded = ['id'];
    protected $casts=['info' => 'json'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Route notifications for the OneSignal channel.
     *
     * @param Notification $notification
     * @return string
     */
    public function routeNotificationForOneSignal($notification)
    {
        // Return the onesignal_token field
        return $this->onesignal_token;
    }
}
