# Payment Out Proof & Supplier Notification System - Technical Design

**Feature**: Upload Bukti Bayar ke Supplier + Auto Notification  
**Version**: 1.0  
**Last Updated**: 21 April 2026

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Frontend (Blade + Alpine.js)             │
│  - Upload Form  - Payment List  - Payment Detail            │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│                    Laravel Backend                           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Controllers  │  │  Services    │  │   Models     │      │
│  │              │  │              │  │              │      │
│  │ PaymentOut   │─▶│ PaymentOut   │─▶│ PaymentOut   │      │
│  │ Controller   │  │ Service      │  │ Proof        │      │
│  │              │  │              │  │              │      │
│  │              │  │ Notification │  │ Supplier     │      │
│  │              │  │ Service      │  │ Invoice      │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                           │                                  │
│                    ┌──────▼──────┐                          │
│                    │ Queue Jobs  │                          │
│                    │             │                          │
│                    │ SendEmail   │                          │
│                    │ SendWhatsApp│                          │
│                    └──────┬──────┘                          │
└───────────────────────────┼──────────────────────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
┌───────▼────────┐  ┌───────▼────────┐  ┌──────▼──────┐
│  Email Service │  │ WhatsApp API   │  │ File Storage│
│  (SMTP/API)    │  │ (Fonnte/Wablas)│  │ (S3/Local)  │
└────────────────┘  └────────────────┘  └─────────────┘
```

---

## 🗄️ Database Schema

### Migration Files

#### 1. Create `payment_out_proofs` table

```php
Schema::create('payment_out_proofs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('supplier_invoice_id')->constrained()->onDelete('cascade');
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->date('payment_date');
    $table->decimal('amount', 15, 2);
    $table->enum('payment_method', ['bank_transfer', 'giro', 'cash', 'other'])->default('bank_transfer');
    $table->string('reference_number');
    $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts');
    $table->text('notes')->nullable();
    $table->enum('status', ['pending', 'sent', 'confirmed'])->default('pending');
    $table->timestamp('confirmed_at')->nullable();
    $table->foreignId('confirmed_by')->nullable()->constrained('users');
    $table->foreignId('uploaded_by')->constrained('users');
    $table->timestamps();
    
    $table->index(['supplier_invoice_id', 'status']);
    $table->index('payment_date');
});
```

#### 2. Create `payment_out_documents` table

```php
Schema::create('payment_out_documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('payment_out_proof_id')->constrained()->onDelete('cascade');
    $table->enum('document_type', ['payment_proof', 'receipt', 'other'])->default('payment_proof');
    $table->string('file_name');
    $table->string('file_path');
    $table->integer('file_size'); // in bytes
    $table->string('mime_type');
    $table->foreignId('uploaded_by')->constrained('users');
    $table->timestamps();
});
```

#### 3. Create `payment_notifications` table

```php
Schema::create('payment_notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('payment_out_proof_id')->constrained()->onDelete('cascade');
    $table->enum('notification_type', ['email', 'whatsapp']);
    $table->string('recipient'); // email or phone
    $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])->default('pending');
    $table->timestamp('sent_at')->nullable();
    $table->timestamp('delivered_at')->nullable();
    $table->text('error_message')->nullable();
    $table->integer('retry_count')->default(0);
    $table->timestamps();
    
    $table->index(['payment_out_proof_id', 'notification_type']);
});
```

#### 4. Modify `suppliers` table

```php
Schema::table('suppliers', function (Blueprint $table) {
    $table->string('email')->nullable()->after('phone');
    $table->string('whatsapp_number')->nullable()->after('email');
    $table->string('contact_person')->nullable()->after('whatsapp_number');
    $table->enum('notification_preference', ['email', 'whatsapp', 'both'])->default('both')->after('contact_person');
});
```

#### 5. Modify `supplier_invoices` table

```php
Schema::table('supplier_invoices', function (Blueprint $table) {
    $table->decimal('paid_amount', 15, 2)->default(0)->after('total_amount');
    $table->decimal('outstanding_amount', 15, 2)->after('paid_amount');
    $table->enum('payment_status', ['unpaid', 'partial_paid', 'paid'])->default('unpaid')->after('status');
    $table->date('last_payment_date')->nullable()->after('payment_status');
});
```

---

## 📦 Models

### PaymentOutProof Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentOutProof extends Model
{
    protected $fillable = [
        'supplier_invoice_id',
        'organization_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'bank_account_id',
        'notes',
        'status',
        'confirmed_at',
        'confirmed_by',
        'uploaded_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    // Relationships
    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PaymentOutDocument::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(PaymentNotification::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeConfirmed($query)
    {
        return $this->where('status', 'confirmed');
    }

    // Methods
    public function markAsSent(): void
    {
        $this->update(['status' => 'sent']);
    }

    public function markAsConfirmed(User $user): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $user->id,
        ]);
    }
}
```

### PaymentOutDocument Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PaymentOutDocument extends Model
{
    protected $fillable = [
        'payment_out_proof_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by',
    ];

    // Relationships
    public function paymentOutProof(): BelongsTo
    {
        return $this->belongsTo(PaymentOutProof::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Methods
    public function getDownloadUrl(): string
    {
        return route('web.payment-out.document.download', $this->id);
    }

    public function getFileUrl(): string
    {
        return Storage::url($this->file_path);
    }

    public function delete(): ?bool
    {
        // Delete file from storage
        Storage::delete($this->file_path);
        
        return parent::delete();
    }
}
```

### PaymentNotification Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentNotification extends Model
{
    protected $fillable = [
        'payment_out_proof_id',
        'notification_type',
        'recipient',
        'status',
        'sent_at',
        'delivered_at',
        'error_message',
        'retry_count',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Relationships
    public function paymentOutProof(): BelongsTo
    {
        return $this->belongsTo(PaymentOutProof::class);
    }

    // Methods
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    public function canRetry(): bool
    {
        return $this->retry_count < 3 && $this->status === 'failed';
    }
}
```

---

## 🎯 Services

### PaymentOutService

```php
<?php

namespace App\Services;

use App\Models\PaymentOutProof;
use App\Models\PaymentOutDocument;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Jobs\SendPaymentNotificationEmail;
use App\Jobs\SendPaymentNotificationWhatsApp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentOutService
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * Create payment out proof with documents and send notifications
     */
    public function createPaymentProof(
        SupplierInvoice $invoice,
        array $paymentData,
        array $files,
        User $user
    ): PaymentOutProof {
        return DB::transaction(function () use ($invoice, $paymentData, $files, $user) {
            // Validate payment amount
            $this->validatePaymentAmount($invoice, $paymentData['amount']);

            // Create payment proof
            $payment = PaymentOutProof::create([
                'supplier_invoice_id' => $invoice->id,
                'organization_id' => $invoice->organization_id,
                'payment_date' => $paymentData['payment_date'],
                'amount' => $paymentData['amount'],
                'payment_method' => $paymentData['payment_method'],
                'reference_number' => $paymentData['reference_number'],
                'bank_account_id' => $paymentData['bank_account_id'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'status' => 'pending',
                'uploaded_by' => $user->id,
            ]);

            // Upload documents
            foreach ($files as $file) {
                $this->uploadDocument($payment, $file, $user);
            }

            // Update supplier invoice
            $this->updateSupplierInvoice($invoice, $paymentData['amount']);

            // Send notifications (async via queue)
            $this->sendNotifications($payment);

            // Audit log
            $this->auditService->log(
                action: 'payment_out.created',
                entityType: PaymentOutProof::class,
                entityId: $payment->id,
                metadata: [
                    'invoice_number' => $invoice->invoice_number,
                    'supplier_id' => $invoice->supplier_id,
                    'amount' => $paymentData['amount'],
                    'payment_method' => $paymentData['payment_method'],
                ],
                userId: $user->id
            );

            return $payment->load(['documents', 'notifications']);
        });
    }

    /**
     * Validate payment amount
     */
    private function validatePaymentAmount(SupplierInvoice $invoice, string $amount): void
    {
        $outstanding = bcsub($invoice->total_amount, $invoice->paid_amount, 2);

        if (bccomp($amount, '0', 2) <= 0) {
            throw new \DomainException('Jumlah pembayaran harus lebih dari 0');
        }

        if (bccomp($amount, $outstanding, 2) > 0) {
            throw new \DomainException(
                "Jumlah pembayaran (Rp " . number_format($amount, 0, ',', '.') . 
                ") melebihi sisa tagihan (Rp " . number_format($outstanding, 0, ',', '.') . ")"
            );
        }
    }

    /**
     * Upload payment document
     */
    private function uploadDocument(
        PaymentOutProof $payment,
        UploadedFile $file,
        User $user
    ): PaymentOutDocument {
        // Generate file path
        $year = now()->format('Y');
        $month = now()->format('m');
        $supplierId = $payment->supplierInvoice->supplier_id;
        
        $path = "payment_proofs/{$year}/{$month}/{$supplierId}";
        $fileName = 'payment_' . $payment->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        // Store file
        $filePath = $file->storeAs($path, $fileName, 'private');

        // Create document record
        return PaymentOutDocument::create([
            'payment_out_proof_id' => $payment->id,
            'document_type' => 'payment_proof',
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => $user->id,
        ]);
    }

    /**
     * Update supplier invoice payment status
     */
    private function updateSupplierInvoice(SupplierInvoice $invoice, string $paymentAmount): void
    {
        $newPaidAmount = bcadd($invoice->paid_amount, $paymentAmount, 2);
        $outstanding = bcsub($invoice->total_amount, $newPaidAmount, 2);

        $paymentStatus = bccomp($outstanding, '0', 2) === 0 ? 'paid' : 'partial_paid';

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'outstanding_amount' => $outstanding,
            'payment_status' => $paymentStatus,
            'last_payment_date' => now()->toDateString(),
        ]);
    }

    /**
     * Send notifications (email + WhatsApp)
     */
    private function sendNotifications(PaymentOutProof $payment): void
    {
        $supplier = $payment->supplierInvoice->supplier;
        $preference = $supplier->notification_preference ?? 'both';

        // Send email
        if (in_array($preference, ['email', 'both']) && $supplier->email) {
            SendPaymentNotificationEmail::dispatch($payment);
        }

        // Send WhatsApp
        if (in_array($preference, ['whatsapp', 'both']) && $supplier->whatsapp_number) {
            SendPaymentNotificationWhatsApp::dispatch($payment);
        }
    }

    /**
     * Resend notifications
     */
    public function resendNotifications(PaymentOutProof $payment, string $type = 'both'): void
    {
        $supplier = $payment->supplierInvoice->supplier;

        if (in_array($type, ['email', 'both']) && $supplier->email) {
            SendPaymentNotificationEmail::dispatch($payment);
        }

        if (in_array($type, ['whatsapp', 'both']) && $supplier->whatsapp_number) {
            SendPaymentNotificationWhatsApp::dispatch($payment);
        }
    }
}
```

---

## 🚀 Queue Jobs

### SendPaymentNotificationEmail Job

```php
<?php

namespace App\Jobs;

use App\Models\PaymentOutProof;
use App\Models\PaymentNotification;
use App\Mail\PaymentNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPaymentNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    public function __construct(
        public PaymentOutProof $payment
    ) {}

    public function handle(): void
    {
        $supplier = $this->payment->supplierInvoice->supplier;

        if (!$supplier->email) {
            Log::warning('Supplier email not found', ['supplier_id' => $supplier->id]);
            return;
        }

        // Create notification record
        $notification = PaymentNotification::create([
            'payment_out_proof_id' => $this->payment->id,
            'notification_type' => 'email',
            'recipient' => $supplier->email,
            'status' => 'pending',
        ]);

        try {
            // Send email
            Mail::to($supplier->email)
                ->send(new PaymentNotificationMail($this->payment));

            // Mark as sent
            $notification->markAsSent();
            $this->payment->markAsSent();

            Log::info('Payment notification email sent', [
                'payment_id' => $this->payment->id,
                'email' => $supplier->email,
            ]);

        } catch (\Exception $e) {
            // Mark as failed
            $notification->markAsFailed($e->getMessage());

            Log::error('Failed to send payment notification email', [
                'payment_id' => $this->payment->id,
                'email' => $supplier->email,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }
}
```

### SendPaymentNotificationWhatsApp Job

```php
<?php

namespace App\Jobs;

use App\Models\PaymentOutProof;
use App\Models\PaymentNotification;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPaymentNotificationWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    public function __construct(
        public PaymentOutProof $payment
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $supplier = $this->payment->supplierInvoice->supplier;

        if (!$supplier->whatsapp_number) {
            Log::warning('Supplier WhatsApp not found', ['supplier_id' => $supplier->id]);
            return;
        }

        // Create notification record
        $notification = PaymentNotification::create([
            'payment_out_proof_id' => $this->payment->id,
            'notification_type' => 'whatsapp',
            'recipient' => $supplier->whatsapp_number,
            'status' => 'pending',
        ]);

        try {
            // Send WhatsApp
            $message = $this->buildMessage();
            $whatsapp->sendMessage($supplier->whatsapp_number, $message);

            // Mark as sent
            $notification->markAsSent();
            $this->payment->markAsSent();

            Log::info('Payment notification WhatsApp sent', [
                'payment_id' => $this->payment->id,
                'phone' => $supplier->whatsapp_number,
            ]);

        } catch (\Exception $e) {
            // Mark as failed
            $notification->markAsFailed($e->getMessage());

            Log::error('Failed to send payment notification WhatsApp', [
                'payment_id' => $this->payment->id,
                'phone' => $supplier->whatsapp_number,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function buildMessage(): string
    {
        $invoice = $this->payment->supplierInvoice;
        $supplier = $invoice->supplier;

        return sprintf(
            "Halo %s,\n\n" .
            "Pembayaran untuk Invoice #%s telah kami lakukan:\n\n" .
            "📅 Tanggal: %s\n" .
            "💰 Jumlah: Rp %s\n" .
            "🏦 Metode: %s\n" .
            "📝 Referensi: %s\n\n" .
            "Detail lengkap dan dokumen dapat diakses di:\n%s\n\n" .
            "Terima kasih atas kerjasamanya.\n\n" .
            "Medikindo Finance Team",
            $supplier->name,
            $invoice->invoice_number,
            $this->payment->payment_date->format('d M Y'),
            number_format($this->payment->amount, 0, ',', '.'),
            ucwords(str_replace('_', ' ', $this->payment->payment_method)),
            $this->payment->reference_number,
            route('web.payment-out.show', $this->payment->id)
        );
    }
}
```

---

Apakah Anda ingin saya lanjutkan dengan:
1. Controller implementation
2. View/UI design
3. Email template
4. WhatsApp service integration
5. Testing strategy

Atau langsung mulai implementasi?