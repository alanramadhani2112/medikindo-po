<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToOrganization;
use App\Traits\Filterable;

class CreditNote extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganization, Filterable;

    // -----------------------------------------------------------------------
    // Status Constants
    // -----------------------------------------------------------------------
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_APPLIED = 'applied';
    public const STATUS_CANCELLED = 'cancelled';

    // -----------------------------------------------------------------------
    // Type Constants
    // -----------------------------------------------------------------------
    public const TYPE_RETURN = 'return';           // Retur barang
    public const TYPE_DISCOUNT = 'discount';       // Diskon tambahan
    public const TYPE_CORRECTION = 'correction';   // Koreksi kesalahan
    public const TYPE_CANCELLATION = 'cancellation'; // Pembatalan invoice

    protected $fillable = [
        'cn_number',
        'organization_id',
        'customer_invoice_id',
        'supplier_invoice_id',
        'type',
        'status',
        'reason',
        'amount',
        'tax_amount',
        'total_amount',
        'issued_by',
        'issued_at',
        'applied_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'issued_at' => 'datetime',
            'applied_at' => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function customerInvoice(): BelongsTo
    {
        return $this->belongsTo(CustomerInvoice::class);
    }

    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(CreditNoteLineItem::class);
    }

    // -----------------------------------------------------------------------
    // Status Helpers
    // -----------------------------------------------------------------------

    public function isDraft(): bool { return $this->status === self::STATUS_DRAFT; }
    public function isIssued(): bool { return $this->status === self::STATUS_ISSUED; }
    public function isApplied(): bool { return $this->status === self::STATUS_APPLIED; }
    public function isCancelled(): bool { return $this->status === self::STATUS_CANCELLED; }

    // -----------------------------------------------------------------------
    // Business Logic
    // -----------------------------------------------------------------------

    /**
     * Generate CN number automatically
     */
    public static function generateCnNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        $lastCn = self::where('cn_number', 'like', "CN-{$year}{$month}-%")
            ->orderBy('cn_number', 'desc')
            ->first();

        if ($lastCn) {
            $lastNumber = (int) substr($lastCn->cn_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('CN-%s%s-%04d', $year, $month, $newNumber);
    }

    /**
     * Issue the credit note
     */
    public function issue(User $user): void
    {
        if (!$this->isDraft()) {
            throw new \DomainException('Credit note can only be issued from draft status');
        }

        $this->update([
            'status' => self::STATUS_ISSUED,
            'issued_by' => $user->id,
            'issued_at' => now(),
        ]);
    }

    /**
     * Apply credit note to reduce invoice balance
     */
    public function apply(): void
    {
        if (!$this->isIssued()) {
            throw new \DomainException('Credit note must be issued before applying');
        }

        // Apply to customer invoice
        if ($this->customerInvoice) {
            $this->customerInvoice->applyCreditNote($this);
        }

        // Apply to supplier invoice
        if ($this->supplierInvoice) {
            $this->supplierInvoice->applyCreditNote($this);
        }

        $this->update([
            'status' => self::STATUS_APPLIED,
            'applied_at' => now(),
        ]);
    }

    /**
     * Calculate totals from line items
     */
    public function recalculateTotals(): void
    {
        $this->amount = $this->lineItems()->sum('subtotal');
        $this->tax_amount = $this->lineItems()->sum('tax_amount');
        $this->total_amount = $this->amount + $this->tax_amount;
        $this->save();
    }
}