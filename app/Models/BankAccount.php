<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'bank_name',
        'bank_code',
        'account_number',
        'account_holder_name',
        'is_active',
        'is_default',
        'account_type',
        'default_for_receive',
        'default_for_send',
        'default_priority',
        'current_balance',
        'balance_updated_at',
        'notes',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'is_default'          => 'boolean',
        'default_for_receive' => 'boolean',
        'default_for_send'    => 'boolean',
        'default_priority'    => 'integer',
        'current_balance'     => 'decimal:2',
        'balance_updated_at'  => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function customerInvoices(): HasMany
    {
        return $this->hasMany(CustomerInvoice::class);
    }

    /** Payments received INTO this bank (incoming from RS/Klinik) */
    public function incomingPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'bank_account_id')
            ->where('type', 'incoming');
    }

    /** Payments sent OUT from this bank (outgoing to Supplier) */
    public function outgoingPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'bank_account_id')
            ->where('type', 'outgoing');
    }

    /** All payments linked to this bank */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'bank_account_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForReceive($query)
    {
        return $query->where('is_active', true)
            ->whereIn('account_type', ['receive', 'both']);
    }

    public function scopeForSend($query)
    {
        return $query->where('is_active', true)
            ->whereIn('account_type', ['send', 'both']);
    }

    public function scopeDefaultReceive($query)
    {
        return $query->where('default_for_receive', true)
            ->where('is_active', true)
            ->orderBy('default_priority');
    }

    public function scopeDefaultSend($query)
    {
        return $query->where('default_for_send', true)
            ->where('is_active', true)
            ->orderBy('default_priority');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isDefault(): bool
    {
        return $this->is_default;
    }

    public function canReceive(): bool
    {
        return in_array($this->account_type, ['receive', 'both']);
    }

    public function canSend(): bool
    {
        return in_array($this->account_type, ['send', 'both']);
    }

    public function getAccountTypeLabel(): string
    {
        return match ($this->account_type) {
            'receive' => 'Terima Masuk',
            'send'    => 'Kirim Keluar',
            'both'    => 'Masuk & Keluar',
            default   => '-',
        };
    }

    public function getAccountTypeBadgeColor(): string
    {
        return match ($this->account_type) {
            'receive' => 'success',
            'send'    => 'danger',
            'both'    => 'primary',
            default   => 'secondary',
        };
    }

    public function canBeDeleted(): bool
    {
        return $this->customerInvoices()->count() === 0
            && $this->payments()->count() === 0;
    }

    /** Total uang masuk ke rekening ini */
    public function getTotalIncomingAttribute(): float
    {
        return (float) $this->incomingPayments()
            ->whereIn('status', ['completed', 'confirmed'])
            ->sum('amount');
    }

    /** Total uang keluar dari rekening ini */
    public function getTotalOutgoingAttribute(): float
    {
        return (float) $this->outgoingPayments()
            ->whereIn('status', ['completed', 'confirmed'])
            ->sum('amount');
    }

    /** Net cashflow rekening ini */
    public function getNetCashflowAttribute(): float
    {
        return $this->total_incoming - $this->total_outgoing;
    }
}
