<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentDocument extends Model
{
    protected $fillable = [
        'payment_proof_id',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    /**
     * Relationships
     */
    public function paymentProof(): BelongsTo
    {
        return $this->belongsTo(PaymentProof::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
