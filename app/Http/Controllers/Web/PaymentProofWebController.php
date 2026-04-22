<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PaymentProof;
use App\Models\CustomerInvoice;
use App\Services\PaymentProofService;
use App\Services\DocumentStorageService;
use App\Http\Requests\StorePaymentProofRequest;
use App\Http\Requests\VerifyPaymentProofRequest;
use App\Http\Requests\ApprovePaymentProofRequest;
use App\Http\Requests\RejectPaymentProofRequest;
use App\Http\Requests\UploadPaymentDocumentRequest;
use App\Enums\PaymentProofStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PaymentProofWebController extends Controller
{
    public function __construct(
        private readonly PaymentProofService $paymentProofService,
        private readonly DocumentStorageService $documentStorageService
    ) {}

    /**
     * Display a listing of payment proofs with role-based filtering.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', PaymentProof::class);

        $user = Auth::user();
        $query = PaymentProof::with(['customerInvoice.organization', 'submittedBy', 'verifiedBy', 'approvedBy']);

        // Role-based filtering
        if ($user->hasRole('Healthcare User')) {
            $query->byHealthcareUser($user->id);
        } elseif ($user->hasRole(['Finance', 'Super Admin', 'Admin Pusat', 'Approver'])) {
            // These roles can see all payment proofs
        } else {
            // Other roles see only their organization's payment proofs
            $query->whereHas('customerInvoice', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }

        // Status filtering — support both ?tab= and legacy ?status=
        $tab = $request->get('tab') ?? $request->get('status', 'submitted');
        if ($tab !== '') {
            $status = PaymentProofStatus::tryFrom($tab);
            if ($status) {
                $query->byStatus($status);
            }
        }

        // Search by invoice number or bank reference
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bank_reference', 'like', "%{$search}%")
                  ->orWhereHas('customerInvoice', function ($subQ) use ($search) {
                      $subQ->where('invoice_number', 'like', "%{$search}%");
                  });
            });
        }

        $paymentProofs = $query->latest()->paginate(15)->withQueryString();

        // Summary stats for Medikindo Finance users
        $statsQuery = PaymentProof::query();
        if ($user->hasRole('Healthcare User')) {
            $statsQuery->byHealthcareUser($user->id);
        } elseif ($user->hasRole(['Finance', 'Super Admin', 'Admin Pusat', 'Approver'])) {
            // See all
        } else {
            $statsQuery->whereHas('customerInvoice', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }

        $stats = [
            'submitted' => (clone $statsQuery)->where('status', PaymentProofStatus::SUBMITTED)->count(),
            'verified'  => (clone $statsQuery)->where('status', PaymentProofStatus::VERIFIED)->count(),
            'approved'  => (clone $statsQuery)->where('status', PaymentProofStatus::APPROVED)->count(),
            'rejected'  => (clone $statsQuery)->where('status', PaymentProofStatus::REJECTED)->count(),
        ];

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Payment Proofs']
        ];

        return view('payment-proofs.index', compact('paymentProofs', 'stats', 'tab', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new payment proof.
     */
    public function create(Request $request)
    {
        $invoiceId = $request->get('invoice_id');
        $invoice = null;

        if ($invoiceId) {
            $invoice = CustomerInvoice::findOrFail($invoiceId);
        }

        // Use the policy to check if user can create payment proof
        $this->authorize('create', PaymentProof::class);
        
        // If invoice is specified, check if user can submit for this specific invoice
        if ($invoice) {
            $user = Auth::user();
            $policy = new \App\Policies\PaymentProofPolicy();
            
            if (!$policy->submit($user, $invoice)) {
                abort(403, 'You are not authorized to submit payment proof for this invoice.');
            }
        }

        // Load eligible invoices (only unpaid/partial) for the dropdown
        $user = Auth::user();
        $invoices = CustomerInvoice::with('organization')
            ->whereIn('status', [\App\Enums\CustomerInvoiceStatus::ISSUED, \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID])
            ->when(!$user->hasRole(['Super Admin', 'Admin Pusat']), fn($q) => $q->where('organization_id', $user->organization_id))
            ->orderBy('invoice_number')
            ->get();

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Payment Proofs', 'url' => route('web.payment-proofs.index')],
            ['label' => 'Submit Bukti Pembayaran']
        ];

        return view('payment-proofs.create', compact('invoice', 'invoices', 'breadcrumbs'));
    }

    /**
     * Store a newly created payment proof.
     */
    public function store(StorePaymentProofRequest $request)
    {
        // Additional authorization check in controller
        $this->authorize('create', PaymentProof::class);
        
        try {
            // Get the uploaded file if present
            $file = $request->hasFile('file') ? $request->file('file') : null;
            
            $paymentProof = $this->paymentProofService->submitPaymentProof(
                $request->validated(),
                Auth::user(),
                $file
            );

            $message = 'Bukti pembayaran berhasil disubmit dan menunggu persetujuan.';
            if ($file) {
                $message .= ' Dokumen bukti transfer telah diunggah.';
            }

            return redirect()
                ->route('web.payment-proofs.show', $paymentProof)
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan bukti pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified payment proof.
     */
    public function show(PaymentProof $paymentProof)
    {
        $this->authorize('view', $paymentProof);

        $paymentProof->load([
            'customerInvoice.organization',
            'submittedBy',
            'verifiedBy',
            'approvedBy',
            'paymentDocuments'
        ]);

        // All payment proofs for the same invoice (for history timeline)
        $relatedProofs = PaymentProof::where('customer_invoice_id', $paymentProof->customer_invoice_id)
            ->with(['submittedBy', 'paymentDocuments'])
            ->orderBy('created_at', 'asc')
            ->get();

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Payment Proofs', 'url' => route('web.payment-proofs.index')],
            ['label' => 'Detail #' . $paymentProof->id]
        ];

        return view('payment-proofs.show', compact('paymentProof', 'relatedProofs', 'breadcrumbs'));
    }

    /**
     * Show the verification interface for Finance Users.
     * SIMPLIFIED: Skip verify step, go directly to approve (1-step flow)
     */
    public function verify(PaymentProof $paymentProof)
    {
        $this->authorize('verify', $paymentProof);

        if (!$paymentProof->canBeVerified() && !$paymentProof->isResubmitted()) {
            return redirect()->route('web.payment-proofs.show', $paymentProof)
                ->with('error', 'Bukti pembayaran ini tidak dapat diverifikasi pada status saat ini.');
        }

        $paymentProof->load([
            'customerInvoice.organization',
            'customerInvoice.lineItems.product',
            'submittedBy',
            'paymentDocuments'
        ]);

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Payment Proofs', 'url' => route('web.payment-proofs.index')],
            ['label' => 'Setujui Pembayaran #' . $paymentProof->id]
        ];

        // Use the approve view directly (1-step)
        return view('payment-proofs.approve', compact('paymentProof', 'breadcrumbs'));
    }

    /**
     * Process payment proof verification.
     * 
     * Untuk Super Admin: langsung verify + approve dalam 1 langkah.
     * Untuk Finance biasa: hanya verify (step 1), approve oleh Finance lain.
     */
    public function processVerification(VerifyPaymentProofRequest $request, PaymentProof $paymentProof)
    {
        $this->authorize('verify', $paymentProof);

        $user = Auth::user();

        // Cegah self-verification hanya untuk non-Super Admin
        if (!$user->isSuperAdmin() && $paymentProof->submitted_by === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat memverifikasi bukti pembayaran yang Anda submit sendiri.');
        }

        try {
            // Step 1: Verify
            $this->paymentProofService->verifyPaymentProof(
                $paymentProof,
                $user,
                ['verification_notes' => $request->input('approval_notes')]
            );

            // Step 2: Langsung approve juga (1-step flow)
            // Ini valid karena Finance sudah melihat dokumen dan memutuskan
            $this->paymentProofService->approvePaymentProof(
                $paymentProof->fresh(),
                $user,
                ['approval_notes' => $request->input('approval_notes')]
            );

            return redirect()
                ->route('web.payment-proofs.show', $paymentProof)
                ->with('success', 'Bukti pembayaran berhasil diverifikasi dan disetujui.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyetujui pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Show the approval interface for Finance Users.
     */
    public function approve(PaymentProof $paymentProof)
    {
        $this->authorize('approve', $paymentProof);

        if (!$paymentProof->canBeApproved()) {
            return redirect()->route('web.payment-proofs.show', $paymentProof)
                ->with('error', 'Bukti pembayaran ini tidak dapat diapprove pada status saat ini.');
        }

        $paymentProof->load([
            'customerInvoice.organization',
            'submittedBy',
            'verifiedBy',
            'paymentDocuments'
        ]);

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Payment Proofs', 'url' => route('web.payment-proofs.index')],
            ['label' => 'Verify #' . $paymentProof->id]
        ];

        return view('payment-proofs.approve', compact('paymentProof', 'breadcrumbs'));
    }

    /**
     * Process payment proof approval (step 2 — technical verification + create payment record).
     * Jika status masih SUBMITTED, otomatis verify dulu kemudian approve.
     */
    public function processApproval(ApprovePaymentProofRequest $request, PaymentProof $paymentProof)
    {
        $this->authorize('approve', $paymentProof);

        $user = Auth::user();

        // Cegah self-approval hanya untuk non-Super Admin
        if (!$user->isSuperAdmin()) {
            if ($paymentProof->submitted_by === Auth::id()) {
                return back()->with('error', 'Anda tidak dapat mengapprove bukti pembayaran yang Anda submit sendiri.');
            }
        }

        try {
            // Jika masih SUBMITTED, verify dulu otomatis sebelum approve
            if ($paymentProof->isSubmitted()) {
                $this->paymentProofService->verifyPaymentProof(
                    $paymentProof,
                    $user,
                    ['verification_notes' => $request->input('approval_notes')]
                );
                $paymentProof->refresh();
            }

            $this->paymentProofService->approvePaymentProof(
                $paymentProof,
                $user,
                $request->validated()
            );

            return redirect()
                ->route('web.payment-proofs.show', $paymentProof)
                ->with('success', 'Bukti pembayaran berhasil disetujui. Pembayaran telah dicatat.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyetujui bukti pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Show the rejection interface for Finance Users.
     */
    public function reject(PaymentProof $paymentProof)
    {
        $this->authorize('reject', $paymentProof);

        if (!$paymentProof->canBeApproved() && !$paymentProof->canBeVerified()) {
            return redirect()->route('web.payment-proofs.show', $paymentProof)
                ->with('error', 'Bukti pembayaran ini tidak dapat ditolak pada status saat ini.');
        }

        $paymentProof->load([
            'customerInvoice.organization',
            'submittedBy',
            'verifiedBy',
            'paymentDocuments'
        ]);

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Payment Proofs', 'url' => route('web.payment-proofs.index')],
            ['label' => 'Reject #' . $paymentProof->id]
        ];

        return view('payment-proofs.reject', compact('paymentProof', 'breadcrumbs'));
    }

    /**
     * Process payment proof rejection.
     */
    public function processRejection(RejectPaymentProofRequest $request, PaymentProof $paymentProof)
    {
        $this->authorize('reject', $paymentProof);

        try {
            $this->paymentProofService->rejectPaymentProof(
                $paymentProof,
                Auth::user(),
                $request->validated()['approval_notes']
            );

            return redirect()
                ->route('web.payment-proofs.show', $paymentProof)
                ->with('success', 'Bukti pembayaran berhasil ditolak.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menolak bukti pembayaran: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Recall
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Process recall (withdrawal) of a submitted payment proof.
     */
    public function recall(Request $request, PaymentProof $paymentProof)
    {
        $this->authorize('recall', $paymentProof);

        $request->validate([
            'recall_reason' => 'required|string|min:10|max:500',
        ], [
            'recall_reason.required' => 'Alasan penarikan wajib diisi.',
            'recall_reason.min'      => 'Alasan minimal 10 karakter.',
        ]);

        try {
            $this->paymentProofService->recallPaymentProof(
                $paymentProof,
                Auth::user(),
                $request->recall_reason
            );

            return redirect()
                ->route('web.payment-proofs.show', $paymentProof)
                ->with('success', 'Bukti pembayaran berhasil ditarik kembali. Anda dapat mengajukan bukti baru jika diperlukan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menarik bukti pembayaran: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Resubmit (Healthcare re-submits after rejection)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Show resubmit form pre-filled with original rejected proof data.
     */
    public function showResubmit(PaymentProof $paymentProof)
    {
        $this->authorize('resubmit', $paymentProof);

        $paymentProof->load([
            'customerInvoice.organization',
            'paymentDocuments',
            'submittedBy',
        ]);

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Payment Proofs', 'url' => route('web.payment-proofs.index')],
            ['label' => 'Ajukan Ulang #' . $paymentProof->id],
        ];

        return view('payment-proofs.resubmit', compact('paymentProof', 'breadcrumbs'));
    }

    /**
     * Process the resubmission.
     */
    public function processResubmit(Request $request, PaymentProof $paymentProof)
    {
        $this->authorize('resubmit', $paymentProof);

        $request->validate([
            'payment_date'         => 'required|date',
            'payment_method'       => 'required|string',
            'sender_bank_name'     => 'nullable|string|max:100',
            'sender_account_number'=> 'nullable|string|max:50',
            'giro_number'          => 'nullable|string|max:50',
            'giro_due_date'        => 'nullable|date',
            'bank_reference'       => 'nullable|string|max:100',
            'resubmission_notes'   => [
                'required',
                'string',
                'max:1000',
                function ($attribute, $value, $fail) {
                    $wordCount = str_word_count(trim($value));
                    if ($wordCount < 10) {
                        $fail('Keterangan perbaikan minimal 10 kata. Saat ini: ' . $wordCount . ' kata.');
                    }
                },
            ],
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'resubmission_notes.required' => 'Keterangan perbaikan wajib diisi.',
            'resubmission_notes.max'      => 'Keterangan maksimal 1000 karakter.',
            'payment_date.required'       => 'Tanggal pembayaran wajib diisi.',
            'payment_method.required'     => 'Metode pembayaran wajib dipilih.',
            'file.required'               => 'Upload bukti pembayaran baru wajib dilakukan.',
        ]);

        try {
            $file = $request->hasFile('file')
                ? $request->file('file')
                : null;

            $newProof = $this->paymentProofService->resubmitPaymentProof(
                $paymentProof,
                Auth::user(),
                $request->all(),
                $file
            );

            return redirect()
                ->route('web.payment-proofs.show', $newProof)
                ->with('success', 'Bukti pembayaran berhasil diajukan ulang. Tim Finance akan mereview kembali.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal mengajukan ulang: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Correction (Super Admin only)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Show correction form for an approved payment proof (Super Admin only).
     */
    public function correct(PaymentProof $paymentProof)
    {
        $this->authorize('correct', $paymentProof);

        $paymentProof->load(['customerInvoice.organization', 'submittedBy', 'paymentDocuments']);

        $breadcrumbs = [
            ['label' => 'Invoicing', 'url' => 'javascript:void(0)'],
            ['label' => 'Payment Proofs', 'url' => route('web.payment-proofs.index')],
            ['label' => 'Koreksi #' . $paymentProof->id],
        ];

        return view('payment-proofs.correct', compact('paymentProof', 'breadcrumbs'));
    }

    /**
     * Process correction of an approved payment proof (Super Admin only).
     */
    public function processCorrection(Request $request, PaymentProof $paymentProof)
    {
        $this->authorize('correct', $paymentProof);

        $data = $request->validate([
            'correction_reason'      => 'required|string|min:20|max:500',
            'corrected_amount'       => 'required|numeric|min:0.01',
            'corrected_payment_type' => 'required|in:full,partial',
            'corrected_payment_date' => 'required|date|before_or_equal:today',
            'bank_reference'         => 'nullable|string|max:100',
            'notes'                  => 'nullable|string|max:500',
        ], [
            'correction_reason.required' => 'Alasan koreksi wajib diisi (minimal 20 karakter).',
            'correction_reason.min'      => 'Alasan koreksi harus minimal 20 karakter untuk keperluan audit.',
            'corrected_amount.required'  => 'Nominal koreksi wajib diisi.',
        ]);

        try {
            $newProof = $this->paymentProofService->correctPaymentProof(
                $paymentProof,
                Auth::user(),
                $data
            );

            return redirect()
                ->route('web.payment-proofs.show', $newProof)
                ->with('success', 'Koreksi berhasil. Bukti pembayaran baru #' . $newProof->id . ' telah dibuat dan menunggu approval ulang.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal melakukan koreksi: ' . $e->getMessage());
        }
    }

    public function uploadDocument(UploadPaymentDocumentRequest $request, PaymentProof $paymentProof)
    {
        $this->authorize('uploadDocument', $paymentProof);

        try {
            $document = $this->paymentProofService->uploadDocument(
                $paymentProof,
                $request->file('file'),
                Auth::user()
            );

            return back()->with('success', 'Dokumen berhasil diunggah.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengunggah dokumen: ' . $e->getMessage());
        }
    }

    /**
     * View payment document inline (for preview).
     */
    public function viewDocument(PaymentProof $paymentProof, $documentId)
    {
        $document = $paymentProof->paymentDocuments()->findOrFail($documentId);
        
        // Check if user can view this payment proof (which includes document access)
        $this->authorize('view', $paymentProof);

        try {
            // Get the file path from storage
            $filePath = storage_path('app/private/' . $document->file_path);
            
            // Check if file exists
            if (!file_exists($filePath)) {
                return back()->with('error', 'File tidak ditemukan.');
            }
            
            // Return file with inline disposition (view in browser)
            return response()->file($filePath, [
                'Content-Type' => $document->mime_type ?? 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"'
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menampilkan dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Download payment document.
     */
    public function downloadDocument(PaymentProof $paymentProof, $documentId)
    {
        $document = $paymentProof->paymentDocuments()->findOrFail($documentId);
        
        // Check if user can view this payment proof (which includes document access)
        $this->authorize('view', $paymentProof);

        try {
            // Get the file path from storage
            $filePath = storage_path('app/private/' . $document->file_path);
            
            // Check if file exists
            if (!file_exists($filePath)) {
                return back()->with('error', 'File tidak ditemukan.');
            }
            
            // Force download
            return response()->download($filePath, $document->original_filename);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengunduh dokumen: ' . $e->getMessage());
        }
    }
}
