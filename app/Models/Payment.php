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
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }
}
