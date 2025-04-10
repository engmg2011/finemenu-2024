<?php

namespace App\Models;

use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Priceable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property string|null $note
 * @property string|null $scheduled_at
 * @property int $user_id
 * @property string $orderable_type
 * @property int $orderable_id
 * @property string|null $status
 * @property bool $paid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $device_id
 * @property array|null $delivery_address
 * @property float $total_price
 * @property float $subtotal_price
 * @property-read \App\Models\Device|null $device
 * @property-read Collection<int, \App\Models\Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\OrderLine> $orderLines
 * @property-read int|null $order_lines_count
 * @property-read Model|\Eloquent $orderable
 * @property-read Collection<int, \App\Models\Price> $prices
 * @property-read int|null $prices_count
 * @property-read \App\Models\User $user
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereDeliveryAddress($value)
 * @method static Builder|Order whereDeviceId($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereNote($value)
 * @method static Builder|Order whereOrderableId($value)
 * @method static Builder|Order whereOrderableType($value)
 * @method static Builder|Order wherePaid($value)
 * @method static Builder|Order whereScheduledAt($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereSubtotalPrice($value)
 * @method static Builder|Order whereTotalPrice($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUserId($value)
 * @mixin Eloquent
 */
class Order extends Model
{
    use HasFactory, Localizable, Priceable, Discountable;

    protected $guarded = ['id'];
    protected $casts = ['paid' => 'boolean', 'delivery_address' => 'json'];

    /**
     * @return HasMany
     */
    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderable()
    {
        return $this->morphTo();
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);

    }
}
