<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // hospital or clinic
        'code',
        'address',
        'city',
        'province',
        'phone',
        'email',
        'license_number',
        'is_authorized_narcotic',
        'is_active',
        'default_tax_rate',
        'default_discount_percentage',
        // AR Invoice fiscal data
        'npwp',
        'nik',
        'customer_code',
        'bank_accounts',
    ];

    protected function casts(): array
    {
        return [
            'is_active'                   => 'boolean',
            'is_authorized_narcotic'      => 'boolean',
            'default_tax_rate'            => 'decimal:2',
            'default_discount_percentage' => 'decimal:2',
            'bank_accounts'               => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function creditLimit(): HasOne
    {
        return $this->hasOne(CreditLimit::class);
    }

    public function customerInvoices(): HasMany
    {
        return $this->hasMany(CustomerInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
