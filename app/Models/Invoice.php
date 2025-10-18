<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Invoice
 *
 * @property int $id
 * @property float $amount
 * @property array|null $data
 * @property string|null $external_link
 * @property string|null $reference_id
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $type
 * @property string $status
 * @property string|null $status_changed_at
 * @property string $payment_type
 * @property int|null $reservation_id
 * @property int|null $order_id
 * @property int|null $order_line_id
 * @property int|null $invoice_by_id
 * @property int|null $invoice_for_id
 * @property int|null $business_id
 * @property int|null $branch_id
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Business|null $business
 * @property-read \App\Models\User|null $byUser
 * @property-read \App\Models\User|null $forUser
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\Reservation|null $reservation
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereInvoiceById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereInvoiceForId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereOrderLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereReservationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereStatusChangedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Invoice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'data' => 'json',
        'amount'=>'float',
        'status_changed_at' => 'datetime',
        'paid_at' => 'datetime',
        'due_at' => 'datetime'
    ];

    protected $hidden = ['data'];

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
