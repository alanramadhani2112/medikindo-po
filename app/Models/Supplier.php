<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'npwp',
        'license_number',
        'license_expiry_date',
        'is_authorized_narcotic',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'              => 'boolean',
            'is_authorized_narcotic' => 'boolean',
            'license_expiry_date'    => 'date',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
