<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Invoice Modification Attempt Model
 * 
 * This model is IMMUTABLE - no updates or deletes allowed.
 * It logs all attempts to modify immutable invoice data for security audit.
 */
class InvoiceModificationAttempt extends Model
{
    use HasFactory;

    // Disable timestamps since we use attempted_at
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'attempted_at'      => 'datetime',
        'attempted_changes' => 'array',
    ];

    /**
     * Get the user who attempted the modification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the invoice (polymorphic)
     * 
     * @return SupplierInvoice|CustomerInvoice|null
     */
    public function getInvoiceAttribute()
    {
        if ($this->invoice_type === 'supplier') {
            return SupplierInvoice::find($this->invoice_id);
        }
        
        if ($this->invoice_type === 'customer') {
            return CustomerInvoice::find($this->invoice_id);
        }
        
        return null;
    }

    /**
     * Prevent updates - this model is immutable
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new \RuntimeException('Invoice modification attempts cannot be updated');
    }

    /**
     * Prevent deletes - this model is immutable
     */
    public function delete(): ?bool
    {
        throw new \RuntimeException('Invoice modification attempts cannot be deleted');
    }

    /**
     * Create a new modification attempt log
     * 
     * @param string $invoiceType 'supplier' or 'customer'
     * @param int $invoiceId
     * @param int $userId
     * @param array $attemptedChanges
     * @param string $rejectionReason
     * @param string $ipAddress
     * @param string|null $userAgent
     * @return self
     */
    public static function log(
        string $invoiceType,
        int $invoiceId,
        int $userId,
        array $attemptedChanges,
        string $rejectionReason,
        string $ipAddress,
        ?string $userAgent = null
    ): self {
        return self::create([
            'invoice_type'      => $invoiceType,
            'invoice_id'        => $invoiceId,
            'user_id'           => $userId,
            'attempted_at'      => now(),
            'attempted_changes' => $attemptedChanges,
            'rejection_reason'  => $rejectionReason,
            'ip_address'        => $ipAddress,
            'user_agent'        => $userAgent,
        ]);
    }
}
