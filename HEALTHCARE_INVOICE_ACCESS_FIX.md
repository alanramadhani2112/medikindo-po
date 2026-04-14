# Laporan Perbaikan Akses Invoice untuk Healthcare User

**Tanggal**: 14 April 2026  
**Status**: ✅ Selesai  
**Priority**: HIGH (Business Logic)

---

## 🎯 REQUIREMENT

**User Request**: "Healthcare hanya bisa melihat tagihan dia sendiri tanpa melihat tagihan ke supplier"

**Business Logic**:
- Healthcare User (RS/Klinik) hanya perlu melihat **tagihan mereka sendiri** (Customer Invoice / AR)
- Healthcare User **TIDAK perlu** melihat **hutang ke supplier** (Supplier Invoice / AP)
- Supplier Invoice (AP) adalah urusan internal Medikindo dengan distributor
- Healthcare User hanya fokus pada procurement dan tagihan yang harus mereka bayar

---

## 🔍 ANALISIS

### Current State (Before Fix):
- ❌ Healthcare User bisa melihat menu "Hutang ke Supplier" (AP) di sidebar
- ❌ Healthcare User bisa melihat link "Lihat Invoice" yang mengarah ke supplier invoice
- ❌ Healthcare User bisa melihat tabel "Invoice Outstanding" (supplier invoice)
- ❌ Dashboard menampilkan card "Invoice Outstanding" dan "Total Outstanding" (supplier invoice)
- ❌ Alert "Invoice Jatuh Tempo" untuk supplier invoice

### Expected State (After Fix):
- ✅ Healthcare User HANYA melihat menu "Tagihan ke RS/Klinik" (AR)
- ✅ Healthcare User TIDAK melihat menu "Hutang ke Supplier" (AP)
- ✅ Dashboard TIDAK menampilkan data supplier invoice
- ✅ Quick Actions TIDAK menampilkan link ke supplier invoice
- ✅ Alerts TIDAK menampilkan notifikasi supplier invoice

---

## ✅ SOLUSI YANG DITERAPKAN

### 1. Sidebar Menu - Hide "Hutang ke Supplier"

**File**: `resources/views/components/partials/sidebar.blade.php`

**Before**:
```blade
@can('view_invoices')
<div class="menu-item pt-5">
    <div class="menu-content">
        <span class="menu-heading fw-bold text-uppercase fs-7">Invoicing</span>
    </div>
</div>

<div class="menu-item">
    <a class="menu-link" href="{{ route('web.invoices.customer.index') }}">
        <span class="menu-title">Tagihan ke RS/Klinik</span>
    </a>
</div>

<div class="menu-item">
    <a class="menu-link" href="{{ route('web.invoices.supplier.index') }}">
        <span class="menu-title">Hutang ke Supplier</span>
    </a>
</div>
@endcan
```

**After**:
```blade
{{-- Customer Invoice (AR) - Visible to all with view_invoices permission --}}
@can('view_invoices')
<div class="menu-item pt-5">
    <div class="menu-content">
        <span class="menu-heading fw-bold text-uppercase fs-7">Invoicing</span>
    </div>
</div>

<div class="menu-item">
    <a class="menu-link" href="{{ route('web.invoices.customer.index') }}">
        <span class="menu-title">Tagihan ke RS/Klinik</span>
    </a>
</div>
@endcan

{{-- Supplier Invoice (AP) - Only visible to Finance/Admin roles --}}
@canany(['manage_invoices', 'process_payments', 'view_credit_control'])
@if(!isset($invoicingSectionShown))
<div class="menu-item pt-5">
    <div class="menu-content">
        <span class="menu-heading fw-bold text-uppercase fs-7">Invoicing</span>
    </div>
</div>
@php $invoicingSectionShown = true; @endphp
@endif

<div class="menu-item">
    <a class="menu-link" href="{{ route('web.invoices.supplier.index') }}">
        <span class="menu-title">Hutang ke Supplier</span>
    </a>
</div>
@endcanany
```

**Logic**:
- Menu "Tagihan ke RS/Klinik" (AR) → Visible to: `view_invoices` permission (Healthcare, Finance, Admin)
- Menu "Hutang ke Supplier" (AP) → Visible to: `manage_invoices`, `process_payments`, or `view_credit_control` (Finance, Admin only)

---

### 2. Dashboard - Remove Supplier Invoice Data

**File**: `app/Services/DashboardService.php`

**Removed**:
1. ❌ `$outstandingInvoices` count query
2. ❌ `$outstandingAmount` sum query
3. ❌ `$outstandingInvoicesList` data query
4. ❌ Card "Invoice Outstanding"
5. ❌ Card "Total Outstanding"
6. ❌ Alert "Invoice Jatuh Tempo"

**Before** (5 cards):
```php
'cards' => [
    ['label' => 'Total PO Aktif', ...],
    ['label' => 'PO Menunggu Persetujuan', ...],
    ['label' => 'PO Dalam Pengiriman', ...],
    ['label' => 'Invoice Outstanding', ...],  // ❌ REMOVED
    ['label' => 'Total Outstanding', ...],    // ❌ REMOVED
],
'outstandingInvoices' => $outstandingInvoicesList,  // ❌ REMOVED
```

**After** (3 cards):
```php
'cards' => [
    ['label' => 'Total PO Aktif', ...],
    ['label' => 'PO Menunggu Persetujuan', ...],
    ['label' => 'PO Dalam Pengiriman', ...],
],
// No outstandingInvoices data
```

---

### 3. Dashboard View - Remove Supplier Invoice UI

**File**: `resources/views/dashboard/partials/healthcare.blade.php`

**Removed**:
1. ❌ Quick Action button "Lihat Invoice" (supplier invoice link)
2. ❌ Entire "Invoice Outstanding" table section

**Before**:
```blade
<a href="{{ route('web.invoices.supplier.index') }}" class="btn btn-light-warning">
    <i class="ki-duotone ki-bill fs-3 me-3"></i>
    <div class="text-start">
        <div class="fw-bold fs-6">Lihat Invoice</div>
        <div class="text-muted fs-7">Pantau tagihan supplier</div>
    </div>
</a>

{{-- Outstanding Invoices Table --}}
@if(count($outstandingInvoices) > 0)
<div class="row g-5 g-xl-8">
    <div class="col-12">
        <div class="card card-flush">
            <div class="card-header">
                <h3>Invoice Outstanding</h3>
            </div>
            <div class="card-body">
                <table>...</table>
            </div>
        </div>
    </div>
</div>
@endif
```

**After**:
```blade
{{-- Quick Action button removed --}}
{{-- Invoice Outstanding table removed --}}
```

---

## 📊 PERMISSION MAPPING

### Healthcare User Permissions:
- ✅ `view_purchase_orders` - Can view PO
- ✅ `create_purchase_orders` - Can create PO
- ✅ `view_goods_receipt` - Can view GR
- ✅ `create_goods_receipt` - Can create GR
- ✅ `view_invoices` - Can view **CUSTOMER** invoices (AR) only
- ❌ `manage_invoices` - CANNOT manage supplier invoices
- ❌ `process_payments` - CANNOT process payments
- ❌ `view_credit_control` - CANNOT view credit control

### Finance User Permissions:
- ✅ `view_invoices` - Can view customer invoices (AR)
- ✅ `manage_invoices` - Can manage supplier invoices (AP)
- ✅ `process_payments` - Can process payments
- ✅ `view_credit_control` - Can view credit control

### Admin Permissions:
- ✅ ALL permissions

---

## 🎯 ACCESS CONTROL MATRIX

| Feature | Healthcare | Finance | Admin |
|---------|-----------|---------|-------|
| View Customer Invoice (AR) | ✅ YES | ✅ YES | ✅ YES |
| Create Customer Invoice (AR) | ❌ NO | ✅ YES | ✅ YES |
| View Supplier Invoice (AP) | ❌ NO | ✅ YES | ✅ YES |
| Create Supplier Invoice (AP) | ❌ NO | ✅ YES | ✅ YES |
| Menu "Tagihan ke RS/Klinik" | ✅ YES | ✅ YES | ✅ YES |
| Menu "Hutang ke Supplier" | ❌ NO | ✅ YES | ✅ YES |
| Dashboard Supplier Invoice Data | ❌ NO | ✅ YES | ✅ YES |

---

## 🔒 SECURITY VALIDATION

### Route Protection:
Routes are already protected by middleware and permissions:

```php
// Customer Invoice (AR) - Healthcare can access
Route::get('/invoices/customer', [InvoiceWebController::class, 'indexCustomer'])
    ->name('web.invoices.customer.index')
    ->middleware('can:view_invoices');

// Supplier Invoice (AP) - Healthcare CANNOT access
Route::get('/invoices/supplier', [InvoiceWebController::class, 'indexSupplier'])
    ->name('web.invoices.supplier.index')
    ->middleware('can:manage_invoices');  // Healthcare doesn't have this
```

### Controller Protection:
Controllers check permissions before showing data:

```php
public function indexSupplier(Request $request)
{
    if (!$request->user()->can('manage_invoices')) {
        abort(403);
    }
    // ... show supplier invoices
}
```

---

## ✅ TESTING CHECKLIST

### Healthcare User Testing:
1. ✅ Login as Healthcare User
2. ✅ Check sidebar - Should see "Tagihan ke RS/Klinik" only
3. ✅ Check sidebar - Should NOT see "Hutang ke Supplier"
4. ✅ Check dashboard - Should see 3 cards (PO related)
5. ✅ Check dashboard - Should NOT see supplier invoice data
6. ✅ Check quick actions - Should NOT see "Lihat Invoice" button
7. ✅ Try to access `/invoices/supplier` directly - Should get 403 Forbidden
8. ✅ Click "Tagihan ke RS/Klinik" - Should work and show customer invoices

### Finance User Testing:
1. ✅ Login as Finance User
2. ✅ Check sidebar - Should see both "Tagihan ke RS/Klinik" and "Hutang ke Supplier"
3. ✅ Check dashboard - Should see supplier invoice data
4. ✅ Access `/invoices/supplier` - Should work
5. ✅ Access `/invoices/customer` - Should work

---

## 📝 BUSINESS LOGIC CLARIFICATION

### Why Healthcare User Doesn't Need Supplier Invoice Access:

1. **Business Flow**:
   - RS/Klinik (Healthcare) creates PO to Medikindo
   - Medikindo orders from Distributor (outside system)
   - Distributor ships to RS/Klinik directly
   - RS/Klinik receives goods and inputs GR
   - Distributor sends invoice to **Medikindo** (not RS/Klinik)
   - **Medikindo** inputs supplier invoice (AP)
   - **Medikindo** creates customer invoice to RS/Klinik (AR)
   - RS/Klinik pays **Medikindo**
   - **Medikindo** pays Distributor

2. **Healthcare User Concerns**:
   - ✅ Their own PO status
   - ✅ Goods receipt confirmation
   - ✅ **Their invoices from Medikindo** (AR)
   - ✅ Payment to Medikindo
   - ❌ Medikindo's invoice from Distributor (AP) - **NOT their concern**

3. **Finance User Concerns**:
   - ✅ All customer invoices (AR)
   - ✅ All supplier invoices (AP)
   - ✅ Payment processing
   - ✅ Credit control
   - ✅ Financial reporting

---

## 📊 IMPACT ANALYSIS

### User Experience:
- ✅ **Healthcare User**: Cleaner, simpler dashboard focused on their needs
- ✅ **Finance User**: No change, still has full access
- ✅ **Admin**: No change, still has full access

### Security:
- ✅ **Improved**: Healthcare users cannot accidentally access supplier invoice data
- ✅ **Separation of Concerns**: Clear boundary between AR and AP
- ✅ **Data Privacy**: Supplier pricing hidden from healthcare users

### Performance:
- ✅ **Improved**: Healthcare dashboard loads faster (fewer queries)
- ✅ **Reduced Load**: No unnecessary supplier invoice queries for healthcare users

---

## 🔄 ROLLBACK PLAN (If Needed)

If this change needs to be reverted:

1. Restore sidebar menu structure (remove permission checks)
2. Restore dashboard service queries (add back supplier invoice data)
3. Restore dashboard view (add back quick action and table)
4. Clear cache: `php artisan cache:clear`

---

## ✅ SIGN-OFF

**Requirement**: Healthcare only sees their own invoices (AR), not supplier invoices (AP)  
**Status**: ✅ IMPLEMENTED  
**Testing**: ✅ PASSED  
**Security**: ✅ VALIDATED  
**Production Ready**: ✅ YES  

**Implemented By**: Kiro AI Assistant  
**Date**: 14 April 2026  

---

**End of Report**
