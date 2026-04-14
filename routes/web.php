<?php

use App\Http\Controllers\Web\ApprovalWebController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\OrganizationWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DeliveryWebController;
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
    Route::post('/login', [AuthWebController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout')->middleware('auth');

// ─────────────────────────────────────────────────────────────
// Authenticated Routes — prefixed with "web." to avoid API conflicts
// All permission middleware uses new strict permission names.
// ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', fn() => redirect()->route('web.dashboard'));

    // ── Dashboard ──────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('web.dashboard');
    Route::get('/dashboard/audit', [DashboardController::class, 'audit'])->name('web.dashboard.audit')->middleware('can:view_audit');
    Route::get('/dashboard/finance', [DashboardController::class, 'finance'])->name('web.dashboard.finance')->middleware('can:view_invoices');

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
        Route::get('/create',         [\App\Http\Controllers\Web\GoodsReceiptWebController::class, 'create'])->name('create');
        Route::post('/',              [\App\Http\Controllers\Web\GoodsReceiptWebController::class, 'store'])->name('store');
        Route::get('/{goodsReceipt}', [\App\Http\Controllers\Web\GoodsReceiptWebController::class, 'show'])->name('show');
        Route::get('/{goodsReceipt}/pdf', [\App\Http\Controllers\Web\GoodsReceiptWebController::class, 'exportPdf'])->name('pdf');
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

    // ── Payments ───────────────────────────────────────────────
    Route::prefix('payments')->name('web.payments.')->middleware('can:view_payments')->group(function () {
        Route::get('/',          [\App\Http\Controllers\Web\PaymentWebController::class, 'index'])->name('index');
        Route::get('/incoming',  [\App\Http\Controllers\Web\PaymentWebController::class, 'createIncoming'])->name('create.incoming');
        Route::post('/incoming', [\App\Http\Controllers\Web\PaymentWebController::class, 'storeIncoming'])->name('store.incoming')->middleware('can:process_payments');
        Route::get('/outgoing',  [\App\Http\Controllers\Web\PaymentWebController::class, 'createOutgoing'])->name('create.outgoing');
        Route::post('/outgoing', [\App\Http\Controllers\Web\PaymentWebController::class, 'storeOutgoing'])->name('store.outgoing')->middleware('can:process_payments');
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
        Route::get('/unread-count',   [\App\Http\Controllers\Web\NotificationWebController::class, 'unreadCount'])->name('unread_count');
        Route::post('/mark-all-read', [\App\Http\Controllers\Web\NotificationWebController::class, 'markAllAsRead'])->name('mark_all_read');
        Route::get('/{id}/read',      [\App\Http\Controllers\Web\NotificationWebController::class, 'markAsRead'])->name('read');
    });

    // ── Examples/Tests ────────────────────────────────────────
    Route::get('/examples/toolbar-demo', fn() => view('examples.toolbar-demo'))->name('web.examples.toolbar');
    Route::get('/test-layout', [\App\Http\Controllers\Web\TestController::class, 'layout'])->name('web.test.layout');
    Route::get('/diagnostic', fn() => view('diagnostic'))->name('web.diagnostic');

});
