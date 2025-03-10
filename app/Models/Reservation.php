<?php

namespace App\Models;

use App\Constants\PaymentConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Reservation
 *
 * @property int $id
 * @property string $from
 * @property string $to
 * @property int|null $reservable_id
 * @property string|null $reservable_type
 * @property array|null $data
 * @property string $status
 * @property int|null $order_id
 * @property int|null $order_line_id
 * @property int|null $reserved_by_id
 * @property int|null $reserved_for_id
 * @property int|null $business_id
 * @property int|null $branch_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Business|null $business
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\OrderLine|null $orderline
 * @property-read Model|\Eloquent $reservable
 * @property-read \App\Models\User|null $reservedBy
 * @property-read \App\Models\User|null $reservedFor
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereOrderLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereReservableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereReservableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereReservedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereReservedForId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Reservation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['reservable_id', 'reservable_type', 'reserved_by_id', 'reserved_for_id'];

    protected $casts = ['data' => 'json', 'notes' => 'json'];

    protected $appends = ['payment_status'];

    public function reservable()
    {
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

    public function reservedBy()
    {
        return $this->belongsTo(User::class, 'reserved_by_id');
    }

    public function reservedFor()
    {
        return $this->belongsTo(User::class, 'reserved_for_id');
    }

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function getPaymentStatusAttribute()
    {
        $reservationData = $this->data;
        if (!$reservationData) return null;

        $total = $reservationData['total_price'];
        if (!$total) return null;

        $paidAmount = 0;
        $invoices = $reservationData['invoices'] ?? [];
        foreach ($invoices as &$invoice) {
            if (isset($invoice['type']) && isset($invoice['status'])) {
                if ($invoice['type'] === PaymentConstants::INVOICE_CREDIT
                    && $invoice['status'] === PaymentConstants::INVOICE_PAID) {
                    $paidAmount = $paidAmount + $invoice['amount'];
                }
            }
        }

        return match (true) {
            $paidAmount >= $total => PaymentConstants::RESERVATION_PAID,
            $paidAmount > 0 => PaymentConstants::RESERVATION_PARTIALLY_PAID,
            default => PaymentConstants::RESERVATION_NOT_PAID,
        };
    }

}
