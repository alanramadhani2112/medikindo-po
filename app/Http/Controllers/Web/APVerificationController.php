<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\AntiPhantomBillingException;
use App\Exceptions\DuplicateMirrorException;
use App\Http\Controllers\Controller;
use App\Models\SupplierInvoice;
use App\Services\MirrorGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class APVerificationController extends Controller
{
    /**
     * Verify a Supplier Invoice (AP) and auto-generate AR Customer Invoice (draft).
     *
     * Flow:
     *   1. AP status → 'verified'
     *   2. MirrorGenerationService generates draft CustomerInvoice for the RS/Klinik
     *   3. Redirect to the new AR draft for review
     *
     * POST /invoices/supplier/{invoice}/verify
     */
    public function verify(
        Request $request,
        SupplierInvoice $invoice,
        MirrorGenerationService $mirror
    ): RedirectResponse {
        if (! $request->user()->can('create_invoices')) {
            abort(403, 'Akses Ditolak.');
        }

        // Gate: must be in draft status to verify
        if (! $invoice->isDraft()) {
            return redirect()
                ->route('web.invoices.supplier.show', $invoice)
                ->with('error', "Invoice hanya bisa diverifikasi dari status Draft. Status saat ini: {$invoice->status->getLabel()}.");
        }

        // Gate: must have a GR and PO with organization
        $po = $invoice->purchaseOrder;
        if (! $po || ! $po->organization_id) {
            return redirect()
                ->route('web.invoices.supplier.show', $invoice)
                ->with('error', 'Invoice tidak memiliki referensi Purchase Order atau Organisasi yang valid.');
        }

        // organization_id diambil dari PO — tidak perlu input manual
        $customerId = $po->organization_id;

        // Transition AP to verified
        $invoice->update([
            'status'      => \App\Enums\SupplierInvoiceStatus::VERIFIED,
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        try {
            $customerInvoice = $mirror->generateARFromAP($invoice, $customerId);

            return redirect()
                ->route('web.invoices.customer.show', $customerInvoice)
                ->with('success', "Invoice Pemasok #{$invoice->invoice_number} berhasil diverifikasi. Draft tagihan ke RS #{$customerInvoice->invoice_number} telah dibuat — silakan review dan terbitkan.");

        } catch (DuplicateMirrorException $e) {
            // AP already verified but AR already exists — just redirect to existing AR
            $existingAR = \App\Models\CustomerInvoice::where('supplier_invoice_id', $invoice->id)
                ->whereNotIn('status', [\App\Enums\CustomerInvoiceStatus::VOID->value])
                ->first();

            return redirect()
                ->route($existingAR ? 'web.invoices.customer.show' : 'web.invoices.supplier.show', $existingAR ?? $invoice)
                ->with('info', 'Invoice Pemasok sudah diverifikasi sebelumnya. Draft AR sudah ada.');

        } catch (AntiPhantomBillingException $e) {
            // Rollback AP status
            $invoice->update([
                'status'      => \App\Enums\SupplierInvoiceStatus::DRAFT,
                'verified_at' => null,
                'verified_by' => null,
            ]);

            return redirect()
                ->route('web.invoices.supplier.show', $invoice)
                ->with('error', 'Verifikasi gagal: ' . $e->getMessage());

        } catch (\Throwable $e) {
            // Rollback AP status
            $invoice->update([
                'status'      => \App\Enums\SupplierInvoiceStatus::DRAFT,
                'verified_at' => null,
                'verified_by' => null,
            ]);

            $errorMessage = $e->getMessage();
            \Log::error('APVerificationController: Verification failed', [
                'invoice_id' => $invoice->id,
                'error' => $errorMessage,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('web.invoices.supplier.show', $invoice)
                ->with('error', 'Terjadi kesalahan: ' . $errorMessage);
        }
    }
}
