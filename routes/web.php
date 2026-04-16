<?php

use App\Http\Controllers\Web\APVerificationController;
use App\Http\Controllers\Web\ApprovalWebController;
use App\Http\Controllers\Web\ARAgingController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\CustomerInvoiceWebController;
use App\Http\Controllers\Web\OrganizationWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DeliveryWebController;
use App\Http\Controllers\Web\PriceListWebController;
use App\Http\Controllers\Web\ProductWebController;
use App\Http\Controllers\Web\PurchaseOrderWebController;
use App\Http\Controllers\Web\SupplierWebController;
use App\Http\Controllers\Web\UserWebController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────
// Auth (guest only)
// ─────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login'])
        ->name('login.post')
        ->middleware('throttle:5,15'); // 5 attempts per 15 minutes
});

// CSRF Token Refresh (for preventing 419 errors on long-open login pages)
Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
})->name('refresh-csrf');

Route::post('/logout', [AuthWebController::class, 'logout'])
    ->name('logout')
    ->middleware(['auth', 'throttle:10,1']); // 10 logouts per minute

// ─────────────────────────────────────────────────────────────
// Authenticated Routes — prefixed with "web." to avoid API conflicts
// All permission middleware uses new strict permission names.
// ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', fn() => redirect()->route('web.dashboard'));

    // ── Dashboard ──────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('web.dashboard')
        ->middleware('can:view_dashboard');
    Route::get('/dashboard/audit', [DashboardController::class, 'audit'])->name('web.dashboard.audit')->middleware('can:view_audit');
    Route::get('/dashboard/finance', [DashboardController::class, 'finance'])->name('web.dashboard.finance')->middleware('can:view_invoices');

    // ── Analytics ──────────────────────────────────────────────
    Route::get('/analytics/products', [\App\Http\Controllers\Web\ProductAnalyticsController::class, 'index'])->name('web.analytics.products')->middleware('can:view_reports');

    // ── Purchase Orders ────────────────────────────────────────
    Route::prefix('purchase-orders')->name('web.po.')->group(function () {

        Route::get('/', [PurchaseOrderWebController::class, 'index'])
            ->name('index')
            ->middleware('can:view_purchase_orders');

        Route::middleware('can:create_purchase_orders')->group(function () {
            Route::get('/create', [PurchaseOrderWebController::class, 'create'])->name('create');
            Route::post('/',      [PurchaseOrderWebController::class, 'store'])->name('store');
        });

        Route::middleware('can:update_purchase_orders')->group(function () {
            Route::get('/{purchaseOrder}/edit', [PurchaseOrderWebController::class, 'edit'])->name('edit');
            Route::put('/{purchaseOrder}',      [PurchaseOrderWebController::class, 'update'])->name('update');
            Route::post('/{purchaseOrder}/submit', [PurchaseOrderWebController::class, 'submit'])->name('submit');
        });

        Route::middleware('can:delete_purchase_orders')->group(function () {
            Route::delete('/{purchaseOrder}', [PurchaseOrderWebController::class, 'destroy'])->name('destroy');
        });

        Route::get('/{purchaseOrder}',     [PurchaseOrderWebController::class, 'show'])->name('show')->middleware('can:view_purchase_orders');
        Route::get('/{purchaseOrder}/pdf', [PurchaseOrderWebController::class, 'exportPdf'])->name('pdf')->middleware('can:view_purchase_orders');
    });

    // ── Approvals ──────────────────────────────────────────────
    Route::prefix('approvals')->name('web.approvals.')->middleware('can:view_approvals')->group(function () {
        Route::get('/',                         [ApprovalWebController::class, 'index'])->name('index');
        Route::post('/{purchaseOrder}/process', [ApprovalWebController::class, 'process'])
            ->name('process')
            ->middleware('can:approve_purchase_orders');
    });

    // ── Goods Receipts ─────────────────────────────────────────
    Route::prefix('goods-receipts')->name('web.goods-receipts.')->middleware('can:view_goods_receipt')->group(function () {
        Route::get('/',               [\App\Http\Controllers\Web\GoodsReceiptWebController::class, 'index'])->name('index');
        Route::get('/{goodsReceipt}', [\App\Http\Controllers\Web\GoodsReceiptWebController::class, 'show'])->name('show');
        Route::get('/{goodsReceipt}/pdf', [\App\Http\Controllers\Web\GoodsReceiptWebController::class, 'exportPdf'])->name('pdf');
        
        // Separate middleware for create/store operations
        Route::middleware('can:confirm_receipt')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Web\GoodsReceiptWebController::class, 'create'])->name('create');
            Route::post('/',      [\App\Http\Controllers\Web\GoodsReceiptWebController::class, 'store'])->name('store');
        });
    });

    // ── Invoices (AP & AR) ─────────────────────────────────────
    Route::prefix('invoices')->name('web.invoices.')->middleware('can:view_invoices')->group(function () {
        // Supplier Invoice (AP) - Index
        Route::get('/supplier',                            [\App\Http\Controllers\Web\InvoiceWebController::class, 'indexSupplier'])->name('supplier.index');
        
        // Supplier Invoice (AP) - Create from Goods Receipt
        Route::get('/supplier/create',                     [\App\Http\Controllers\Web\InvoiceWebController::class, 'createSupplier'])
            ->name('supplier.create')
            ->middleware('can:create_invoices');
        Route::post('/supplier',                           [\App\Http\Controllers\Web\InvoiceWebController::class, 'storeSupplier'])
            ->name('supplier.store')
            ->middleware('can:create_invoices');
        
        Route::get('/supplier/{invoice}',                  [\App\Http\Controllers\Web\InvoiceWebController::class, 'showSupplier'])->name('supplier.show');
        Route::get('/supplier/{invoice}/pdf',              [\App\Http\Controllers\Web\InvoiceWebController::class, 'exportSupplierPdf'])->name('supplier.pdf');
        
        // Customer Invoice (AR) - Index
        Route::get('/customer',                            [\App\Http\Controllers\Web\InvoiceWebController::class, 'indexCustomer'])->name('customer.index');
        
        // Customer Invoice (AR) - Create from Goods Receipt
        Route::get('/customer/create',                     [\App\Http\Controllers\Web\InvoiceWebController::class, 'createCustomer'])
            ->name('customer.create')
            ->middleware('can:create_invoices');
        Route::post('/customer',                           [\App\Http\Controllers\Web\InvoiceWebController::class, 'storeCustomer'])
            ->name('customer.store')
            ->middleware('can:create_invoices');
        
        Route::get('/customer/{invoice}',                  [\App\Http\Controllers\Web\InvoiceWebController::class, 'showCustomer'])->name('customer.show');
        Route::get('/customer/{invoice}/pdf',              [\App\Http\Controllers\Web\InvoiceWebController::class, 'exportCustomerPdf'])->name('customer.pdf');

        Route::post('/customer/{invoice}/confirm-payment', [\App\Http\Controllers\Web\InvoiceWebController::class, 'confirmPayment'])
            ->name('customer.confirm_payment')
            ->middleware('can:process_payments');

        Route::post('/customer/{invoice}/verify-payment', [\App\Http\Controllers\Web\InvoiceWebController::class, 'verifyPayment'])
            ->name('customer.verify_payment')
            ->middleware('can:create_invoices');

        Route::post('/customer/{invoice}/approve-discrepancy', [\App\Http\Controllers\Web\InvoiceWebController::class, 'approveDiscrepancy'])
            ->name('customer.approve_discrepancy')
            ->middleware('can:create_invoices');

        Route::post('/customer/{invoice}/reject-discrepancy', [\App\Http\Controllers\Web\InvoiceWebController::class, 'rejectDiscrepancy'])
            ->name('customer.reject_discrepancy')
            ->middleware('can:create_invoices');
    });

    // ── AR Invoice System — New Routes (Sprint 3) ─────────────
    Route::middleware('can:view_invoices')->group(function () {
        // Customer Invoice (AR) — new dedicated controller
        Route::get('/invoices/customer', [CustomerInvoiceWebController::class, 'index'])
            ->name('web.invoices.customer.index');
        Route::get('/invoices/customer/{invoice}', [CustomerInvoiceWebController::class, 'show'])
            ->name('web.invoices.customer.show');
        Route::post('/invoices/customer/{invoice}/issue', [CustomerInvoiceWebController::class, 'issue'])
            ->name('web.invoices.customer.issue');
        Route::post('/invoices/customer/{invoice}/void', [CustomerInvoiceWebController::class, 'void'])
            ->name('web.invoices.customer.void');
        Route::get('/invoices/customer/{invoice}/pdf', [CustomerInvoiceWebController::class, 'print'])
            ->name('web.invoices.customer.pdf');

        // AP Verification — trigger Mirror generation
        Route::post('/invoices/supplier/{invoice}/verify', [APVerificationController::class, 'verify'])
            ->name('web.invoices.supplier.verify');
    });

    // ── Credit Notes ───────────────────────────────────────────
    Route::prefix('credit-notes')->name('web.credit-notes.')->middleware('can:view_invoices')->group(function () {
        Route::get('/', [\App\Http\Controllers\Web\CreditNoteWebController::class, 'index'])->name('index');
        Route::get('/{creditNote}', [\App\Http\Controllers\Web\CreditNoteWebController::class, 'show'])->name('show');
        
        Route::middleware('can:create_invoices')->group(function () {
            // Create credit note for customer invoice
            Route::get('/customer-invoice/{invoice}/create', [\App\Http\Controllers\Web\CreditNoteWebController::class, 'createForCustomerInvoice'])
                ->name('create-customer');
            Route::post('/customer-invoice/{invoice}', [\App\Http\Controllers\Web\CreditNoteWebController::class, 'storeForCustomerInvoice'])
                ->name('store-customer');
            
            // Credit note actions
            Route::post('/{creditNote}/issue', [\App\Http\Controllers\Web\CreditNoteWebController::class, 'issue'])->name('issue');
            Route::post('/{creditNote}/apply', [\App\Http\Controllers\Web\CreditNoteWebController::class, 'apply'])->name('apply');
            Route::post('/{creditNote}/cancel', [\App\Http\Controllers\Web\CreditNoteWebController::class, 'cancel'])->name('cancel');
        });
    });

    // Price List Management
    Route::resource('/price-lists', PriceListWebController::class)
        ->names('web.price-lists')
        ->middleware('can:manage_products');

    // AR Aging Dashboard
    Route::get('/ar-aging', [ARAgingController::class, 'index'])
        ->name('web.ar-aging.index')
        ->middleware('can:view_invoices');

    // ── Payments ───────────────────────────────────────────────
    Route::prefix('payments')->name('web.payments.')->middleware('can:view_payments')->group(function () {
        Route::get('/',          [\App\Http\Controllers\Web\PaymentWebController::class, 'index'])->name('index');
        Route::get('/incoming',  [\App\Http\Controllers\Web\PaymentWebController::class, 'createIncoming'])->name('create.incoming');
        Route::post('/incoming', [\App\Http\Controllers\Web\PaymentWebController::class, 'storeIncoming'])->name('store.incoming')->middleware('can:process_payments');
        Route::get('/outgoing',  [\App\Http\Controllers\Web\PaymentWebController::class, 'createOutgoing'])->name('create.outgoing');
        Route::post('/outgoing', [\App\Http\Controllers\Web\PaymentWebController::class, 'storeOutgoing'])->name('store.outgoing')->middleware('can:process_payments');
    });

    // ── Payment Proofs ─────────────────────────────────────────
    Route::prefix('payment-proofs')->name('web.payment-proofs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Web\PaymentProofWebController::class, 'index'])
            ->name('index')
            ->middleware('can:view_payment_status');
        
        Route::middleware('can:submit_payment_proof')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Web\PaymentProofWebController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Web\PaymentProofWebController::class, 'store'])->name('store');
        });

        Route::get('/{paymentProof}', [\App\Http\Controllers\Web\PaymentProofWebController::class, 'show'])
            ->name('show');

        // Finance User actions
        Route::middleware('can:verify_payment_proof')->group(function () {
            Route::get('/{paymentProof}/verify', [\App\Http\Controllers\Web\PaymentProofWebController::class, 'verify'])->name('verify');
            Route::post('/{paymentProof}/verify', [\App\Http\Controllers\Web\PaymentProofWebController::class, 'processVerification'])->name('process-verification');
        });

        Route::middleware('can:approve_payment')->group(function () {
            Route::get('/{paymentProof}/approve', [\App\Http\Controllers\Web\PaymentProofWebController::class, 'approve'])->name('approve');
            Route::post('/{paymentProof}/approve', [\App\Http\Controllers\Web\PaymentProofWebController::class, 'processApproval'])->name('process-approval');
            Route::get('/{paymentProof}/reject', [\App\Http\Controllers\Web\PaymentProofWebController::class, 'reject'])->name('reject');
            Route::post('/{paymentProof}/reject', [\App\Http\Controllers\Web\PaymentProofWebController::class, 'processRejection'])->name('process-rejection');
        });

        // Document management
        Route::middleware('can:upload_payment_document')->group(function () {
            Route::post('/{paymentProof}/documents', [\App\Http\Controllers\Web\PaymentProofWebController::class, 'uploadDocument'])->name('upload-document');
        });
        
        Route::get('/{paymentProof}/documents/{document}', [\App\Http\Controllers\Web\PaymentProofWebController::class, 'downloadDocument'])
            ->name('download-document');
    });

    // ── Financial Controls (Credit Control) ────────────────────
    Route::prefix('financial-controls')->name('web.financial-controls.')->middleware('can:view_credit_control')->group(function () {
        Route::get('/',                      [\App\Http\Controllers\Web\FinancialControlWebController::class, 'index'])->name('index');
        Route::post('/',                     [\App\Http\Controllers\Web\FinancialControlWebController::class, 'store'])->name('store');
        Route::patch('/{financial_control}', [\App\Http\Controllers\Web\FinancialControlWebController::class, 'update'])->name('update');
    });

    // ── Master Data: Products ──────────────────────────────────
    Route::prefix('products')->name('web.products.')->middleware('can:manage_products')->group(function () {
        Route::get('/',                [ProductWebController::class, 'index'])->name('index');
        Route::get('/create',          [ProductWebController::class, 'create'])->name('create');
        Route::post('/',               [ProductWebController::class, 'store'])->name('store');
        Route::get('/{product}/edit',  [ProductWebController::class, 'edit'])->name('edit');
        Route::put('/{product}',       [ProductWebController::class, 'update'])->name('update');
        Route::delete('/{product}',    [ProductWebController::class, 'destroy'])->name('destroy');
    });

    // ── Master Data: Suppliers ─────────────────────────────────
    Route::prefix('suppliers')->name('web.suppliers.')->middleware('can:manage_suppliers')->group(function () {
        Route::get('/',                        [SupplierWebController::class, 'index'])->name('index');
        Route::get('/create',                  [SupplierWebController::class, 'create'])->name('create');
        Route::post('/',                       [SupplierWebController::class, 'store'])->name('store');
        Route::get('/{supplier}/edit',         [SupplierWebController::class, 'edit'])->name('edit');
        Route::put('/{supplier}',              [SupplierWebController::class, 'update'])->name('update');
        Route::delete('/{supplier}',           [SupplierWebController::class, 'destroy'])->name('destroy');
        Route::patch('/{supplier}/toggle-status', [SupplierWebController::class, 'toggleStatus'])->name('toggle_status');
    });

    // ── Master Data: Organizations ─────────────────────────────
    Route::prefix('organizations')->name('web.organizations.')->middleware('can:manage_organizations')->group(function () {
        Route::get('/',                          [OrganizationWebController::class, 'index'])->name('index');
        Route::get('/create',                    [OrganizationWebController::class, 'create'])->name('create');
        Route::post('/',                         [OrganizationWebController::class, 'store'])->name('store');
        Route::get('/{organization}/edit',       [OrganizationWebController::class, 'edit'])->name('edit');
        Route::put('/{organization}',            [OrganizationWebController::class, 'update'])->name('update');
        Route::delete('/{organization}',         [OrganizationWebController::class, 'destroy'])->name('destroy');
        Route::patch('/{organization}/toggle-status', [OrganizationWebController::class, 'toggleStatus'])->name('toggle_status');
    });

    // ── Master Data: Users ─────────────────────────────────────
    Route::prefix('users')->name('web.users.')->middleware('can:manage_users')->group(function () {
        Route::get('/',            [UserWebController::class, 'index'])->name('index');
        Route::get('/create',      [UserWebController::class, 'create'])->name('create');
        Route::post('/',           [UserWebController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserWebController::class, 'edit'])->name('edit');
        Route::put('/{user}',      [UserWebController::class, 'update'])->name('update');
        Route::delete('/{user}',   [UserWebController::class, 'destroy'])->name('destroy');
    });

    // ── Notifications ──────────────────────────────────────────
    Route::prefix('notifications')->name('web.notifications.')->group(function () {
        Route::get('/',               [\App\Http\Controllers\Web\NotificationWebController::class, 'index'])->name('index');
        Route::get('/recent',         [\App\Http\Controllers\Web\NotificationWebController::class, 'getRecent'])->name('recent');
        Route::get('/unread-count',   [\App\Http\Controllers\Web\NotificationWebController::class, 'unreadCount'])->name('unread_count');
        Route::post('/mark-all-read', [\App\Http\Controllers\Web\NotificationWebController::class, 'markAllAsRead'])->name('mark_all_read');
        Route::get('/{id}/read',      [\App\Http\Controllers\Web\NotificationWebController::class, 'markAsRead'])->name('markAsRead');
    });

    // ── Inventory ──────────────────────────────────────────────
    Route::prefix('inventory')->name('web.inventory.')->middleware('can:view_inventory')->group(function () {
        Route::get('/',                              [\App\Http\Controllers\Web\InventoryWebController::class, 'index'])->name('index');
        Route::get('/movements',                     [\App\Http\Controllers\Web\InventoryWebController::class, 'movements'])->name('movements');
        Route::get('/low-stock',                     [\App\Http\Controllers\Web\InventoryWebController::class, 'lowStock'])->name('low_stock');
        Route::get('/expiring',                      [\App\Http\Controllers\Web\InventoryWebController::class, 'expiring'])->name('expiring');
        Route::get('/product/{product}',             [\App\Http\Controllers\Web\InventoryWebController::class, 'show'])->name('show');
        Route::get('/adjust/{inventoryItem}',        [\App\Http\Controllers\Web\InventoryWebController::class, 'adjustForm'])->name('adjust.form');
        Route::post('/adjust/{inventoryItem}',       [\App\Http\Controllers\Web\InventoryWebController::class, 'adjust'])->name('adjust');
    });

    // ── Examples/Tests ────────────────────────────────────────
    Route::get('/examples/toolbar-demo', fn() => view('examples.toolbar-demo'))->name('web.examples.toolbar');
    Route::get('/test-layout', [\App\Http\Controllers\Web\TestController::class, 'layout'])->name('web.test.layout');
    Route::get('/diagnostic', fn() => view('diagnostic'))->name('web.diagnostic');

});
