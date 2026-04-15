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
     * Verify a Supplier Invoice (AP) and trigger AR mirror generation.
     *
     * POST /invoices/supplier/{invoice}/verify
     */
    public function verify(
        Request $request,
        SupplierInvoice $invoice,
        MirrorGenerationService $mirror
    ): RedirectResponse {
        $request->validate([
            'customer_id' => 'required|exists:organizations,id',
        ]);

        // Change AP status to 'verified'
        $invoice->status = 'verified';
        $invoice->verified_at = now();
        $invoice->save();

        try {
            $customerInvoice = $mirror->generateARFromAP($invoice, (int) $request->customer_id);

            return redirect()
                ->route('web.invoices.customer.show', $customerInvoice)
                ->with('success', "SupplierInvoice #{$invoice->invoice_number} berhasil diverifikasi. Draft AR #{$customerInvoice->invoice_number} telah dibuat.");
        } catch (DuplicateMirrorException $e) {
            return redirect()
                ->route('web.invoices.supplier.show', $invoice)
                ->with('error', 'Draft AR sudah ada untuk invoice supplier ini: ' . $e->getMessage());
        } catch (AntiPhantomBillingException $e) {
            // Rollback status change
            $invoice->status = SupplierInvoice::STATUS_ISSUED;
            $invoice->verified_at = null;
            $invoice->save();

            return redirect()
                ->route('web.invoices.supplier.show', $invoice)
                ->with('error', 'Verifikasi gagal: ' . $e->getMessage());
        } catch (\Throwable $e) {
            // Rollback status change
            $invoice->status = SupplierInvoice::STATUS_ISSUED;
            $invoice->verified_at = null;
            $invoice->save();

            return redirect()
                ->route('web.invoices.supplier.show', $invoice)
                ->with('error', 'Terjadi kesalahan saat verifikasi: ' . $e->getMessage());
        }
    }
}
