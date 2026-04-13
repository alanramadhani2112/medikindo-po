<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerInvoiceRequest;
use App\Http\Requests\StoreSupplierInvoiceRequest;
use App\Models\CustomerInvoice;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\SupplierInvoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    // ----------------------------------------------------
    // SUPPLIER INVOICES (Accounts Payable)
    // ----------------------------------------------------

    public function indexSupplierInvoices(Request $request): JsonResponse
    {
        if (! $request->user()->can('view_invoice')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $user = $request->user();

        $invoices = SupplierInvoice::with(['supplier', 'purchaseOrder', 'goodsReceipt'])
            ->when(! $user->hasRole('Super Admin'), fn($q) => $q->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id)))
            ->latest()
            ->paginate(15);
            
        return response()->json($invoices);
    }

    public function showSupplierInvoice(Request $request, SupplierInvoice $supplierInvoice): JsonResponse
    {
        if (! $request->user()->can('view_invoice')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (
            ! $request->user()->hasRole('Super Admin')
            && optional($supplierInvoice->purchaseOrder)->organization_id !== $request->user()->organization_id
        ) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json($supplierInvoice->load(['supplier', 'purchaseOrder', 'goodsReceipt', 'paymentAllocations']));
    }

    public function storeSupplierInvoice(StoreSupplierInvoiceRequest $request): JsonResponse
    {
        if (! $request->user()->can('manage_invoice')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validated();

        $po = PurchaseOrder::findOrFail($data['purchase_order_id']);
        $gr = GoodsReceipt::findOrFail($data['goods_receipt_id']);

        try {
            $result = $this->invoiceService->issueInvoice($po, $gr, $request->user(), $data['due_date']);
            $invoice = $result['supplier_invoice'];

            if (! empty($data['invoice_number'])) {
                $invoice->update(['invoice_number' => $data['invoice_number']]);
            }

            return response()->json([
                'message' => 'Supplier Invoice generated successfully',
                'data'    => $invoice->fresh()
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // ----------------------------------------------------
    // CUSTOMER INVOICES (Accounts Receivable)
    // ----------------------------------------------------

    public function indexCustomerInvoices(Request $request): JsonResponse
    {
        if (! $request->user()->can('view_invoice')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $user = $request->user();

        $invoices = CustomerInvoice::with(['organization', 'purchaseOrder', 'goodsReceipt'])
            ->when(! $user->hasRole('Super Admin'), fn($q) => $q->where('organization_id', $user->organization_id))
            ->latest()
            ->paginate(15);
            
        return response()->json($invoices);
    }

    public function showCustomerInvoice(Request $request, CustomerInvoice $customerInvoice): JsonResponse
    {
        if (! $request->user()->can('view_invoice')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (
            ! $request->user()->hasRole('Super Admin')
            && $customerInvoice->organization_id !== $request->user()->organization_id
        ) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json($customerInvoice->load(['organization', 'purchaseOrder', 'goodsReceipt', 'paymentAllocations']));
    }

    public function storeCustomerInvoice(StoreCustomerInvoiceRequest $request): JsonResponse
    {
        if (! $request->user()->can('manage_invoice')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validated();

        $po = PurchaseOrder::findOrFail($data['purchase_order_id']);
        $gr = GoodsReceipt::findOrFail($data['goods_receipt_id']);

        try {
            $result = $this->invoiceService->issueInvoice($po, $gr, $request->user(), $data['due_date']);
            $invoice = $result['customer_invoice'];

            return response()->json([
                'message' => 'Customer Invoice generated successfully',
                'data'    => $invoice
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
