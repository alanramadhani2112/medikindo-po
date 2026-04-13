<?php

use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\GoodsReceiptController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Medikindo PO System
|--------------------------------------------------------------------------
*/

// -------------------------------------------------------------------------
// Public — Authentication
// -------------------------------------------------------------------------
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login',    [AuthController::class, 'login'])->name('auth.login');
});

// -------------------------------------------------------------------------
// Protected — Require Sanctum token
// -------------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me',     [AuthController::class, 'me'])->name('auth.me');
    });

    // -----------------------------------------------------------------------
    // Master Data
    // -----------------------------------------------------------------------

    // Organizations
    Route::apiResource('organizations', OrganizationController::class);

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);

    // Products
    Route::apiResource('products', ProductController::class);

    // -----------------------------------------------------------------------
    // Purchase Orders
    // -----------------------------------------------------------------------
    Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {

        Route::get('/',    [PurchaseOrderController::class, 'index'])->name('index');
        Route::post('/',   [PurchaseOrderController::class, 'store'])->name('store');
        Route::get('/{purchase_order}',    [PurchaseOrderController::class, 'show'])->name('show');
        Route::patch('/{purchase_order}',  [PurchaseOrderController::class, 'update'])->name('update');
        Route::delete('/{purchase_order}', [PurchaseOrderController::class, 'destroy'])->name('destroy');

        // Item management
        Route::put('/{purchase_order}/items', [PurchaseOrderController::class, 'syncItems'])
            ->name('items.sync');

        // Lifecycle transitions
        Route::post('/{purchase_order}/submit',           [PurchaseOrderController::class, 'submit'])
            ->name('submit');
        Route::post('/{purchase_order}/send-to-supplier', [PurchaseOrderController::class, 'sendToSupplier'])
            ->name('send-to-supplier');

        // Approvals (nested under PO)
        Route::get('/{purchase_order}/approvals',         [ApprovalController::class, 'index'])
            ->name('approvals.index');
        Route::post('/{purchase_order}/approvals/process', [ApprovalController::class, 'process'])
            ->name('approvals.process');
    });

    // -----------------------------------------------------------------------
    // Goods Receipts
    // -----------------------------------------------------------------------
    Route::apiResource('goods-receipts', GoodsReceiptController::class)->only(['index', 'show', 'store']);

    // -----------------------------------------------------------------------
    // Invoices (AP & AR)
    // -----------------------------------------------------------------------
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/supplier', [InvoiceController::class, 'indexSupplierInvoices'])->name('supplier.index');
        Route::get('/supplier/{supplierInvoice}', [InvoiceController::class, 'showSupplierInvoice'])->name('supplier.show');
        Route::post('/supplier', [InvoiceController::class, 'storeSupplierInvoice'])->name('supplier.store');

        Route::get('/customer', [InvoiceController::class, 'indexCustomerInvoices'])->name('customer.index');
        Route::get('/customer/{customerInvoice}', [InvoiceController::class, 'showCustomerInvoice'])->name('customer.show');
        Route::post('/customer', [InvoiceController::class, 'storeCustomerInvoice'])->name('customer.store');
    });

    // -----------------------------------------------------------------------
    // Payments
    // -----------------------------------------------------------------------
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
        Route::post('/incoming', [PaymentController::class, 'storeIncoming'])->name('incoming.store');
        Route::post('/outgoing', [PaymentController::class, 'storeOutgoing'])->name('outgoing.store');
    });

    // -----------------------------------------------------------------------
    // Audit Logs
    // -----------------------------------------------------------------------
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // -----------------------------------------------------------------------
    // User Management
    // -----------------------------------------------------------------------
    Route::apiResource('users', UserController::class)->except(['store']);

    // -----------------------------------------------------------------------
    // Reports / Dashboard
    // -----------------------------------------------------------------------
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/dashboard',   [ReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/po-summary',  [ReportController::class, 'poSummary'])->name('po-summary');
    });
});
