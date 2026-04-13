<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessApprovalRequest;
use App\Models\PurchaseOrder;
use App\Services\ApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApprovalController extends Controller
{
    public function __construct(private readonly ApprovalService $approvalService) {}

    /**
     * List approvals for a given PO.
     */
    public function index(PurchaseOrder $purchaseOrder): JsonResponse
    {
        Gate::authorize('view', $purchaseOrder);

        return response()->json([
            'approvals' => $purchaseOrder->approvals()->with('approver:id,name')->get(),
        ]);
    }

    /**
     * Process an approval or rejection.
     */
    public function process(ProcessApprovalRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        Gate::authorize('approve', $purchaseOrder);

        $approval = $this->approvalService->process(
            po:       $purchaseOrder,
            approver: $request->user(),
            level:    $request->integer('level'),
            decision: $request->string('decision'),
            notes:    $request->string('notes'),
        );

        return response()->json([
            'message'  => "Purchase Order {$request->decision}.",
            'approval' => $approval->load('approver'),
        ]);
    }
}
