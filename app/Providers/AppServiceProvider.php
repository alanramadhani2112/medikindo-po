<?php

namespace App\Providers;

use App\Models\Approval;
use App\Models\CustomerInvoice;
use App\Models\Organization;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Observers\CustomerInvoiceObserver;
use App\Observers\OrganizationObserver;
use App\Observers\PurchaseOrderItemObserver;
use App\Observers\SupplierInvoiceObserver;
use App\Policies\ApprovalPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\UserPolicy;
use App\Policies\GoodsReceiptPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local', 'testing')) {
            $this->app->register(\Laravel\Dusk\DuskServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default pagination view
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.bootstrap-5');
        \Illuminate\Pagination\Paginator::defaultSimpleView('vendor.pagination.bootstrap-5');

        // ── Super Admin God Mode ─────────────────────────────────────────────
        // Bypasses ALL Gate/Policy authorization checks system-wide.
        // Immutability bypass is handled separately in ImmutabilityGuardService.
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        // Explicit route model binding: {financial_control} → CreditLimit
        \Illuminate\Support\Facades\Route::model('financial_control', \App\Models\CreditLimit::class);

        // ── Policy Registration ──────────────────────────────────────────────
        Gate::policy(PurchaseOrder::class,              PurchaseOrderPolicy::class);
        Gate::policy(Approval::class,                   ApprovalPolicy::class);
        Gate::policy(User::class,                       UserPolicy::class);
        Gate::policy(\App\Models\PaymentProof::class,   \App\Policies\PaymentProofPolicy::class);
        Gate::policy(\App\Models\GoodsReceipt::class,   \App\Policies\GoodsReceiptPolicy::class);

        // Register observers
        PurchaseOrderItem::observe(PurchaseOrderItemObserver::class);
        Organization::observe(OrganizationObserver::class);
        
        // Register invoice observers for immutability enforcement
        // Use app() to ensure proper dependency injection
        SupplierInvoice::observe(app(SupplierInvoiceObserver::class));
        CustomerInvoice::observe(app(CustomerInvoiceObserver::class));

        View::composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $isSuperAdmin = $user->hasRole('Super Admin');

                // Pending approvals badge
                $pendingApprovalCount = 0;
                if ($user->can('view_approvals')) {
                    $pendingApprovalCount = PurchaseOrder::where('status', PurchaseOrder::STATUS_SUBMITTED)
                        ->when(! $isSuperAdmin, fn($q) => $q->where('organization_id', $user->organization_id))
                        ->count();
                }

                // Partial GR badge — PO yang masih partially_received
                $partialGRCount = 0;
                if ($user->can('view_goods_receipt')) {
                    $partialGRCount = PurchaseOrder::where('status', PurchaseOrder::STATUS_PARTIALLY_RECEIVED)
                        ->when(! $isSuperAdmin, fn($q) => $q->where('organization_id', $user->organization_id))
                        ->count();
                }

                // GR siap diinvoice (completed GR yang belum punya supplier invoice)
                $grReadyToInvoiceCount = 0;
                if ($user->can('create_invoices')) {
                    $grReadyToInvoiceCount = \App\Models\GoodsReceipt::where('status', \App\Models\GoodsReceipt::STATUS_COMPLETED)
                        ->whereDoesntHave('supplierInvoices')
                        ->when(! $isSuperAdmin, fn($q) => $q->whereHas('purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id)))
                        ->count();
                }

                // Supplier invoices yang jatuh tempo dan belum lunas (untuk Payment Out)
                $supplierInvoicesDueCount = 0;
                if ($user->can('process_payments')) {
                    $supplierInvoicesDueCount = SupplierInvoice::whereIn('status', [
                            \App\Enums\SupplierInvoiceStatus::VERIFIED,
                            \App\Enums\SupplierInvoiceStatus::OVERDUE
                        ])
                        ->where('due_date', '<=', now())
                        ->whereColumn('paid_amount', '<', 'total_amount')
                        ->when(! $isSuperAdmin, fn($q) => $q->whereHas('goodsReceipt.purchaseOrder', fn($po) => $po->where('organization_id', $user->organization_id)))
                        ->count();
                }

                // Customer invoices yang overdue
                $customerInvoicesOverdueCount = 0;
                if ($user->can('view_invoices')) {
                    $customerInvoicesOverdueCount = CustomerInvoice::whereIn('status', [
                            \App\Enums\CustomerInvoiceStatus::ISSUED,
                            \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID
                        ])
                        ->where('due_date', '<', now())
                        ->whereColumn('paid_amount', '<', 'total_amount')
                        ->when(! $isSuperAdmin, fn($q) => $q->where('organization_id', $user->organization_id))
                        ->count();
                }

                // Payment proofs yang perlu diverifikasi atau diapprove
                $paymentProofsPendingCount = 0;
                if ($user->can('view_payment_status')) {
                    $paymentProofsPendingCount = \App\Models\PaymentProof::whereIn('status', [
                            \App\Enums\PaymentProofStatus::SUBMITTED,
                            \App\Enums\PaymentProofStatus::VERIFIED,
                            \App\Enums\PaymentProofStatus::RESUBMITTED
                        ])
                        ->when(! $isSuperAdmin, fn($q) => $q->whereHas('customerInvoice', fn($inv) => $inv->where('organization_id', $user->organization_id)))
                        ->count();
                }

                // AR Aging - invoice overdue > 30 hari
                $arAgingCriticalCount = 0;
                if ($user->can('view_reports')) {
                    $arAgingCriticalCount = CustomerInvoice::whereIn('status', [
                            \App\Enums\CustomerInvoiceStatus::ISSUED,
                            \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID
                        ])
                        ->where('due_date', '<', now()->subDays(30))
                        ->whereColumn('paid_amount', '<', 'total_amount')
                        ->when(! $isSuperAdmin, fn($q) => $q->where('organization_id', $user->organization_id))
                        ->count();
                }

                // Credit Control - customer yang melebihi credit limit
                $creditLimitExceededCount = 0;
                if ($user->can('view_credit_control')) {
                    // Get organizations with active credit limits
                    $creditLimits = \App\Models\CreditLimit::where('is_active', true)
                        ->with('organization')
                        ->when(! $isSuperAdmin, fn($q) => $q->where('organization_id', $user->organization_id))
                        ->get();
                    
                    // Count how many organizations exceed their credit limit
                    foreach ($creditLimits as $limit) {
                        if ($limit->organization) {
                            // Calculate total outstanding AR for this organization
                            $totalAR = $limit->organization->customerInvoices()
                                ->whereIn('status', [
                                    \App\Enums\CustomerInvoiceStatus::ISSUED,
                                    \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID
                                ])
                                ->get()
                                ->sum('outstanding_amount');
                            
                            // Check if exceeds limit
                            if ($totalAR > $limit->max_limit) {
                                $creditLimitExceededCount++;
                            }
                        }
                    }
                }

                // Inventory - produk expired atau hampir expired (dalam 30 hari)
                // DISABLED: Inventory feature is coming soon
                $inventoryExpiringCount = 0;
                // if ($user->can('view_inventory')) {
                //     $inventoryExpiringCount = \App\Models\InventoryItem::where('quantity_on_hand', '>', 0)
                //         ->whereNotNull('expiry_date')
                //         ->where('expiry_date', '<=', now()->addDays(30))
                //         ->when(! $isSuperAdmin, fn($q) => $q->where('organization_id', $user->organization_id))
                //         ->count();
                // }

                $view->with('pendingApprovalCount', $pendingApprovalCount);
                $view->with('partialGRCount', $partialGRCount);
                $view->with('grReadyToInvoiceCount', $grReadyToInvoiceCount);
                $view->with('supplierInvoicesDueCount', $supplierInvoicesDueCount);
                $view->with('customerInvoicesOverdueCount', $customerInvoicesOverdueCount);
                $view->with('paymentProofsPendingCount', $paymentProofsPendingCount);
                $view->with('arAgingCriticalCount', $arAgingCriticalCount);
                $view->with('creditLimitExceededCount', $creditLimitExceededCount);
                // $view->with('inventoryExpiringCount', $inventoryExpiringCount); // DISABLED
            }
        });
    }
}
