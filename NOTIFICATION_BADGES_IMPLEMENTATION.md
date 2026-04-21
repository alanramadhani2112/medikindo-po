# Notification Badges Implementation

## Overview
Sistem notification badges di sidebar menu yang menampilkan jumlah item yang memerlukan aksi dengan tooltip informasi saat di-hover.

## Badge Notifikasi End-to-End Flow

### 1. PROCUREMENT FLOW

#### Approvals (Badge Merah)
- **Count**: `$pendingApprovalCount`
- **Kondisi**: PO dengan status `SUBMITTED` yang menunggu approval
- **Tooltip**: "X PO menunggu approval"
- **Aksi**: Approve atau reject PO

#### Goods Receipt (Badge Kuning)
- **Count**: `$partialGRCount`
- **Kondisi**: PO dengan status `PARTIALLY_RECEIVED` yang menunggu pengiriman sisa
- **Tooltip**: "X PO menunggu pengiriman sisa"
- **Aksi**: Terima pengiriman sisa barang

### 2. HUTANG (AP - ACCOUNTS PAYABLE) FLOW

#### Supplier Invoices (Badge Merah)
- **Count**: `$grReadyToInvoiceCount`
- **Kondisi**: GR dengan status `COMPLETED` yang belum memiliki supplier invoice
- **Tooltip**: "X GR siap dibuatkan invoice"
- **Aksi**: Buat supplier invoice dari GR

#### Payment Out (Badge Merah)
- **Count**: `$supplierInvoicesDueCount`
- **Kondisi**: Supplier invoice dengan status `VERIFIED` atau `OVERDUE` yang sudah jatuh tempo dan belum lunas
- **Tooltip**: "X invoice supplier jatuh tempo perlu dibayar"
- **Aksi**: Bayar invoice supplier

### 3. PIUTANG (AR - ACCOUNTS RECEIVABLE) FLOW

#### Customer Invoices (Badge Merah)
- **Count**: `$customerInvoicesOverdueCount`
- **Kondisi**: Customer invoice dengan status `ISSUED` atau `PARTIAL_PAID` yang sudah jatuh tempo dan belum lunas
- **Tooltip**: "X invoice customer sudah jatuh tempo"
- **Aksi**: Follow up pembayaran ke customer

#### Payment Proofs (Badge Kuning)
- **Count**: `$paymentProofsPendingCount`
- **Kondisi**: Bukti pembayaran dengan status `SUBMITTED`, `VERIFIED`, atau `RESUBMITTED`
- **Tooltip**: "X bukti pembayaran perlu diverifikasi/approve"
- **Aksi**: Verifikasi dan approve bukti pembayaran

#### AR Aging (Badge Merah)
- **Count**: `$arAgingCriticalCount`
- **Kondisi**: Invoice dengan status `ISSUED` atau `PARTIAL_PAID` yang overdue lebih dari 30 hari
- **Tooltip**: "X invoice overdue >30 hari perlu tindakan"
- **Aksi**: Tindakan penagihan intensif

#### Credit Control (Badge Merah)
- **Count**: `$creditLimitExceededCount`
- **Kondisi**: Organisasi dengan active credit limit yang total outstanding AR-nya melebihi `max_limit`
- **Tooltip**: "X customer melebihi credit limit"
- **Aksi**: Review dan adjust credit limit atau blokir transaksi
- **Catatan**: Outstanding AR dihitung dari sum of `outstanding_amount` untuk invoice dengan status `ISSUED` atau `PARTIAL_PAID`

### 4. INVENTORY FLOW

#### Inventory (Coming Soon)
- **Status**: Feature belum diimplementasikan
- **Badge**: Tidak ada (menampilkan label "Soon")
- **Rencana**: Badge akan menampilkan produk yang expired atau hampir expired

## Warna Badge

- **Badge Merah (`badge-danger`)**: Urgent, memerlukan aksi segera
  - Approvals
  - Supplier Invoices (GR ready)
  - Payment Out (jatuh tempo)
  - Customer Invoices (overdue)
  - AR Aging (>30 hari)
  - Credit Control (exceeded)

- **Badge Kuning (`badge-warning`)**: Perhatian, perlu monitoring
  - Goods Receipt (partial)
  - Payment Proofs (pending verification)
  - Inventory (expiring soon)

## Technical Implementation

### 1. View Composer (AppServiceProvider.php)
Semua badge counts dihitung di `View::composer('*')` untuk tersedia di semua view:

```php
View::composer('*', function ($view) {
    if (auth()->check()) {
        // Calculate all badge counts
        // Share to all views
    }
});
```

### 2. Sidebar Component (sidebar.blade.php)
Setiap menu item yang memiliki badge menggunakan struktur:

```blade
<a class="menu-link" 
   href="..."
   @if(isset($badgeCount) && $badgeCount > 0)
       data-bs-toggle="tooltip" 
       data-bs-placement="right" 
       title="Tooltip message"
   @endif>
   <span class="menu-icon">...</span>
   <span class="menu-title">Menu Title</span>
   @if(isset($badgeCount) && $badgeCount > 0)
       <span class="badge badge-sm badge-circle badge-danger ms-auto">
           {{ $badgeCount }}
       </span>
   @endif
</a>
```

### 3. Tooltip Initialization
Bootstrap tooltips diinisialisasi via JavaScript:

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => 
        new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover',
            placement: 'right',
            container: 'body'
        })
    );
});
```

## Permission-Based Display

Semua badge counts hanya dihitung jika user memiliki permission yang sesuai:
- `view_approvals` → Approvals badge
- `view_goods_receipt` → Goods Receipt badge
- `create_invoices` → Supplier Invoices badge
- `process_payments` → Payment Out badge
- `view_invoices` → Customer Invoices badge
- `view_payment_status` → Payment Proofs badge
- `view_reports` → AR Aging badge
- `view_credit_control` → Credit Control badge
- `view_inventory` → Inventory badge

## Organization Filtering

Untuk non-Super Admin, semua counts difilter berdasarkan `organization_id` user yang sedang login untuk memastikan data isolation antar organisasi.

## Performance Considerations

- Badge counts dihitung sekali per page load via View Composer
## Performance Considerations

- Badge counts dihitung sekali per page load via View Composer
- Query dioptimasi dengan proper indexing pada kolom yang sering diquery:
  - `status`
  - `due_date`
  - `organization_id`
  - `paid_amount` dan `total_amount` (untuk outstanding calculation)
  - `quantity_on_hand` dan `expiry_date` (untuk inventory)
  - `is_active` (untuk credit limits)
- Outstanding amount dihitung menggunakan `whereColumn('paid_amount', '<', 'total_amount')` karena `outstanding_amount` adalah computed attribute, bukan kolom database
- Inventory menggunakan kolom `quantity_on_hand` (bukan `quantity`) untuk menghitung stock yang tersedia
- Credit Control menggunakan eager loading dan loop untuk menghitung organisasi yang melebihi limit (karena melibatkan computed attribute `outstanding_amount`)

## Future Enhancements

1. **Real-time Updates**: Implementasi WebSocket/Pusher untuk update badge real-time
2. **Badge Animation**: Tambahkan pulse animation untuk badge dengan priority tinggi
3. **Notification Center**: Centralized notification panel dengan detail semua pending actions
4. **Email Digest**: Daily/weekly email summary untuk pending actions
5. **Mobile Push Notifications**: Push notification untuk mobile app
