<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToOrganization;

class Payment extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization;

    protected $guarded = ['id'];

    protected $casts = [
        'payment_date'         => 'date',
        'amount'               => 'decimal:2',
        'surcharge_amount'     => 'decimal:2',
        'surcharge_percentage' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(\App\Models\BankAccount::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * Total amount including surcharge
     */
    public function getTotalWithSurchargeAttribute(): float
    {
        return (float) $this->amount + (float) $this->surcharge_amount;
    }

    /**
     * Human-readable payment method label
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'Bank Transfer'   => 'Transfer Bank',
            'Cash'            => 'Tunai',
            'Virtual Account' => 'Virtual Account',
            'Giro'            => 'Giro',
            'Cek'             => 'Cek',
            'QRIS'            => 'QRIS',
            default           => $this->payment_method ?? '-',
        };
    }
}
