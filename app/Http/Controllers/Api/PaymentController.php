<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncomingPaymentRequest;
use App\Http\Requests\StoreOutgoingPaymentRequest;
use App\Models\CustomerInvoice;
use App\Models\Payment;
use App\Models\SupplierInvoice;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->can('view_invoice')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $user = $request->user();

        $payments = Payment::with(['organization', 'supplier', 'allocations'])
            ->when(! $user->hasRole('Super Admin'), function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('organization_id', $user->organization_id)
                        ->orWhereHas('allocations.supplierInvoice.purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id))
                        ->orWhereHas('allocations.customerInvoice', fn($ci) => $ci->where('organization_id', $user->organization_id));
                });
            })
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->latest()
            ->paginate(15);

        return response()->json($payments);
    }

    public function show(Request $request, Payment $payment): JsonResponse
    {
        if (! $request->user()->can('view_invoice')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (
            ! $request->user()->hasRole('Super Admin')
            && $payment->organization_id !== $request->user()->organization_id
            && ! $payment->allocations()->whereHas('supplierInvoice.purchaseOrder', fn($po) => $po->where('organization_id', $request->user()->organization_id))->exists()
            && ! $payment->allocations()->whereHas('customerInvoice', fn($ci) => $ci->where('organization_id', $request->user()->organization_id))->exists()
        ) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json($payment->load(['organization', 'supplier', 'allocations']));
    }

    public function storeIncoming(StoreIncomingPaymentRequest $request): JsonResponse
    {
        if (! $request->user()->can('confirm_payment')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validated();

        $invoice = CustomerInvoice::findOrFail($data['customer_invoice_id']);

        if (
            ! $request->user()->hasRole('Super Admin')
            && $invoice->organization_id !== $request->user()->organization_id
        ) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        try {
            $payment = $this->paymentService->processIncomingPayment($data, $invoice);
            return response()->json([
                'message' => 'Incoming payment recorded successfully',
                'data'    => $payment
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function storeOutgoing(StoreOutgoingPaymentRequest $request): JsonResponse
    {
        if (! $request->user()->can('verify_payment')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validated();

        $invoice = SupplierInvoice::findOrFail($data['supplier_invoice_id']);

        if (
            ! $request->user()->hasRole('Super Admin')
            && $invoice->organization_id !== $request->user()->organization_id
        ) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        try {
            $payment = $this->paymentService->processOutgoingPayment($data, $invoice);
            return response()->json([
                'message' => 'Outgoing payment recorded successfully',
                'data'    => $payment
            ], 201);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
