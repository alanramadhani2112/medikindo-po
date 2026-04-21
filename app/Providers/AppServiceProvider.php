<?php

namespace App\Providers;

use App\Models\Approval;
use App\Models\CustomerInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Observers\CustomerInvoiceObserver;
use App\Observers\PurchaseOrderItemObserver;
use App\Observers\SupplierInvoiceObserver;
use App\Policies\ApprovalPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\UserPolicy;
use App\Policies\GoodsReceiptPolicy;
use App\Policies\CreditNotePolicy;
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
        //
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
        Gate::policy(\App\Models\CreditNote::class,     \App\Policies\CreditNotePolicy::class);

        // Register observers
        PurchaseOrderItem::observe(PurchaseOrderItemObserver::class);
        
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

                $view->with('pendingApprovalCount', $pendingApprovalCount);
                $view->with('partialGRCount', $partialGRCount);
                $view->with('grReadyToInvoiceCount', $grReadyToInvoiceCount);
            }
        });
    }
}
