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

        // Super Admin God Mode - bypass all authorization checks
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);
        Gate::policy(Approval::class, ApprovalPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(\App\Models\PaymentProof::class, \App\Policies\PaymentProofPolicy::class);

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

                // Only compute badge count if user can see the approvals menu
                $pendingApprovalCount = 0;
                if ($user->can('view_approvals')) {
                    $pendingApprovalCount = PurchaseOrder::where('status', PurchaseOrder::STATUS_SUBMITTED)
                        ->when(! $isSuperAdmin, fn($q) => $q->where('organization_id', $user->organization_id))
                        ->count();
                }

                $view->with('pendingApprovalCount', $pendingApprovalCount);
            }
        });
    }
}
