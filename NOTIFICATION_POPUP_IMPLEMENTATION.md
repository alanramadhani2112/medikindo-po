# Implementasi Notification Popup System

**Tanggal**: 14 April 2026  
**Status**: ✅ SELESAI  
**Fitur**: Dropdown notification dengan preview sebelum membuka halaman lengkap

---

## 📋 Ringkasan Fitur

Sistem notifikasi kini menampilkan popup dropdown di header yang memungkinkan user:
1. ✅ Melihat preview 5 notifikasi terbaru tanpa meninggalkan halaman
2. ✅ Melihat badge counter untuk notifikasi yang belum dibaca
3. ✅ Klik notifikasi untuk langsung membuka dan mark as read
4. ✅ Auto-refresh setiap 30 detik untuk update real-time
5. ✅ Tombol "Lihat Semua Notifikasi" untuk membuka halaman lengkap
6. ✅ Tombol "Tandai Semua Dibaca" di halaman notifikasi

---

## 🎨 Tampilan UI

### Dropdown Notification
```
┌─────────────────────────────────────┐
│ 🔔 Notifikasi          3 baru       │
├─────────────────────────────────────┤
│ [Icon] Tagihan Supplier Baru   BARU │
│        Invoice #INV-001...          │
│        2 menit yang lalu            │
├─────────────────────────────────────┤
│ [Icon] PO Disetujui                 │
│        Purchase Order #PO-123...    │
│        1 jam yang lalu              │
├─────────────────────────────────────┤
│ [Icon] Goods Receipt Baru           │
│        GR #GR-456 telah...          │
│        3 jam yang lalu              │
├─────────────────────────────────────┤
│     Lihat Semua Notifikasi →       │
└─────────────────────────────────────┘
```

### Badge Counter
- Muncul di pojok kanan atas icon notifikasi
- Warna merah (badge-danger)
- Menampilkan jumlah notifikasi belum dibaca
- Auto-update setiap 30 detik

---

## 🔧 Komponen yang Diimplementasikan

### 1. Frontend (Header Dropdown)
**File**: `resources/views/components/partials/header.blade.php`

**Fitur**:
- Dropdown menu dengan Metronic styling
- Background image header
- Icon dan badge untuk setiap notifikasi
- Label "Baru" untuk unread notifications
- Scroll area untuk max 5 notifikasi
- Empty state jika tidak ada notifikasi

**Struktur**:
```blade
<div class="btn btn-icon" data-kt-menu-trigger>
    <i class="ki-outline ki-notification"></i>
    <span class="badge notification-badge">{{ $notifCount }}</span>
</div>

<div class="menu menu-sub menu-sub-dropdown">
    <!-- Header dengan background -->
    <!-- Tab content -->
    <!-- Notification items -->
    <!-- View all button -->
</div>
```

### 2. Backend Controller
**File**: `app/Http/Controllers/Web/NotificationWebController.php`

**Methods**:
1. `index()` - Halaman notifikasi lengkap dengan pagination
2. `markAsRead($id)` - Mark single notification as read
3. `markAllAsRead()` - Mark all notifications as read
4. `unreadCount()` - Get unread count (API)
5. `getRecent()` - Get 5 recent notifications (API) ✨ NEW

**New Method `getRecent()`**:
```php
public function getRecent(Request $request)
{
    $notifications = $request->user()
        ->notifications()
        ->take(5)
        ->get()
        ->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Notifikasi',
                'message' => $notification->data['message'] ?? '',
                'icon' => $notification->data['icon'] ?? 'notification',
                'type' => $notification->data['type'] ?? 'info',
                'url' => route('web.notifications.markAsRead', $notification->id),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });

    return response()->json([
        'notifications' => $notifications,
        'unread_count' => $request->user()->unreadNotifications()->count()
    ]);
}
```

### 3. Routes
**File**: `routes/web.php`

**New Route**:
```php
Route::get('/recent', [NotificationWebController::class, 'getRecent'])
    ->name('recent');
```

**All Notification Routes**:
- `GET /notifications` - Index page
- `GET /notifications/recent` - Get recent (API) ✨ NEW
- `GET /notifications/unread-count` - Get count (API)
- `POST /notifications/mark-all-read` - Mark all as read
- `GET /notifications/{id}/read` - Mark as read & redirect

### 4. JavaScript Auto-Refresh
**File**: `public/js/notifications.js`

**Fitur**:
- Auto-refresh setiap 30 detik
- Fetch dari endpoint `/notifications/recent`
- Update badge counter otomatis
- Error handling

**Class Structure**:
```javascript
class NotificationManager {
    constructor() {
        this.refreshInterval = 30000; // 30 seconds
        this.init();
    }

    init() {
        setInterval(() => this.refreshNotifications(), this.refreshInterval);
        this.refreshNotifications();
    }

    async refreshNotifications() {
        // Fetch and update
    }

    updateBadgeCount(count) {
        // Update badge display
    }
}
```

### 5. Notification Index Page
**File**: `resources/views/notifications/index.blade.php`

**Improvements**:
- Header dengan judul dan deskripsi
- Tombol "Tandai Semua Dibaca" (conditional)
- Card layout untuk setiap notifikasi
- Icon berdasarkan tipe notifikasi
- Badge untuk timestamp
- Unread indicator (dot biru)
- Empty state yang informatif
- Pagination

---

## 🎯 Icon Mapping untuk Notifikasi

| Tipe Notifikasi | Icon | Color | Badge |
|----------------|------|-------|-------|
| Invoice Baru | `ki-bill` | info | Light Info |
| PO Submitted | `ki-document` | primary | Light Primary |
| PO Approved | `ki-check-circle` | success | Light Success |
| PO Rejected | `ki-cross-circle` | danger | Light Danger |
| Goods Receipt | `ki-package` | info | Light Info |
| Payment | `ki-wallet` | success | Light Success |
| Default | `ki-notification` | info | Light Info |

---

## 📱 Responsive Design

### Desktop (≥992px)
- Dropdown width: 375px
- Hover trigger untuk dropdown
- Full notification preview

### Tablet (768px - 991px)
- Dropdown width: 350px
- Click trigger untuk dropdown
- Scrollable notification list

### Mobile (<768px)
- Dropdown width: 350px (max viewport)
- Click trigger
- Touch-friendly spacing

---

## 🔄 Auto-Refresh Flow

```
User Login
    ↓
NotificationManager Init
    ↓
Fetch /notifications/recent
    ↓
Update Badge Count
    ↓
Wait 30 seconds
    ↓
Repeat Fetch
```

**Benefits**:
- Real-time updates tanpa reload
- Minimal server load (30s interval)
- Smooth user experience
- No page refresh needed

---

## 🎨 Styling Details

### Badge Counter
```css
.badge.notification-badge {
    position: absolute;
    top: 0;
    right: 0;
    transform: translate(50%, -50%);
    background: #F1416C; /* danger color */
    color: white;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 50%;
}
```

### Dropdown Header
```css
.menu-header {
    background-image: url('menu-header-bg.jpg');
    background-size: cover;
    border-radius: 0.625rem 0.625rem 0 0;
    padding: 2.5rem 2.25rem 1.5rem;
}
```

### Notification Item
```css
.notification-item {
    padding: 1rem 2rem;
    border-bottom: 1px dashed #E4E6EF;
}

.notification-item.unread {
    background-color: #F1FAFF; /* light-primary */
}
```

### Empty State
```css
.empty-state {
    padding: 2.5rem 2.25rem;
    text-align: center;
}

.empty-state-icon {
    font-size: 3rem;
    color: #B5B5C3;
    margin-bottom: 1.25rem;
}
```

---

## 🧪 Testing Checklist

### Functional Testing
- [x] Dropdown muncul saat klik icon notifikasi
- [x] Badge counter menampilkan jumlah yang benar
- [x] Notifikasi terbaru muncul di dropdown (max 5)
- [x] Klik notifikasi redirect ke URL yang benar
- [x] Mark as read berfungsi
- [x] Badge update setelah mark as read
- [x] Auto-refresh berjalan setiap 30 detik
- [x] Empty state muncul jika tidak ada notifikasi
- [x] Tombol "Lihat Semua" redirect ke halaman notifikasi
- [x] Tombol "Tandai Semua Dibaca" berfungsi

### UI/UX Testing
- [x] Dropdown positioning benar (bottom-end)
- [x] Scroll berfungsi jika notifikasi > 5
- [x] Icon sesuai dengan tipe notifikasi
- [x] Color coding konsisten
- [x] Timestamp readable (diffForHumans)
- [x] Responsive di mobile, tablet, desktop
- [x] Animation smooth (Metronic default)

### Performance Testing
- [x] API response < 200ms
- [x] No memory leak dari auto-refresh
- [x] Badge update tidak flicker
- [x] Dropdown tidak lag saat dibuka

---

## 📊 Database Schema

Menggunakan Laravel's default notifications table:

```sql
CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT UNSIGNED NOT NULL,
    data TEXT NOT NULL, -- JSON
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX (notifiable_type, notifiable_id),
    INDEX (read_at)
);
```

**Data JSON Structure**:
```json
{
    "invoice_id": 123,
    "invoice_number": "INV-001",
    "title": "Tagihan Supplier Baru",
    "message": "Invoice #INV-001 (PT Supplier) telah diterbitkan senilai Rp 1.000.000",
    "url": "http://medikindo-po.test/invoices/supplier/123",
    "icon": "bill",
    "type": "info"
}
```

---

## 🔐 Security Considerations

### Authorization
- ✅ User hanya bisa melihat notifikasi miliknya sendiri
- ✅ Middleware `auth` pada semua route notifikasi
- ✅ Policy check di controller: `$request->user()->notifications()`

### XSS Prevention
- ✅ Blade escaping otomatis: `{{ $notification->data['title'] }}`
- ✅ No raw HTML output
- ✅ JSON data validated di notification class

### CSRF Protection
- ✅ CSRF token pada form "Mark All as Read"
- ✅ GET request untuk read-only operations
- ✅ POST request untuk state-changing operations

---

## 🚀 Future Enhancements (Optional)

### Phase 2 (Future)
1. **Real-time dengan Pusher/WebSocket**
   - Instant notification tanpa polling
   - Reduce server load
   - Better UX

2. **Notification Preferences**
   - User bisa pilih tipe notifikasi yang ingin diterima
   - Email notification toggle
   - Sound notification toggle

3. **Notification Categories**
   - Filter by category (Invoice, PO, Payment, etc.)
   - Tab untuk setiap kategori
   - Badge per kategori

4. **Rich Notifications**
   - Inline actions (Approve/Reject dari dropdown)
   - Image/attachment preview
   - Quick reply

5. **Notification History**
   - Archive old notifications
   - Search functionality
   - Export to PDF/Excel

---

## 📝 Usage Examples

### Mengirim Notifikasi Baru

```php
use App\Notifications\NewInvoiceNotification;

// Di controller atau service
$user->notify(new NewInvoiceNotification($invoice));
```

### Custom Notification

```php
use Illuminate\Notifications\Notification;

class CustomNotification extends Notification
{
    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Judul Notifikasi',
            'message' => 'Pesan detail notifikasi',
            'icon' => 'check-circle',
            'type' => 'success',
            'url' => route('some.route'),
        ];
    }
}
```

### Mengirim ke Multiple Users

```php
use Illuminate\Support\Facades\Notification;

$users = User::whereHas('roles', function($q) {
    $q->where('name', 'Finance');
})->get();

Notification::send($users, new PaymentReceivedNotification($payment));
```

---

## 🐛 Troubleshooting

### Badge tidak update
**Problem**: Badge counter tidak berubah setelah mark as read  
**Solution**: Clear browser cache atau hard refresh (Ctrl+F5)

### Dropdown tidak muncul
**Problem**: Dropdown tidak muncul saat klik icon  
**Solution**: 
1. Check console untuk JavaScript errors
2. Pastikan Metronic scripts loaded
3. Verify `data-kt-menu` attributes

### Auto-refresh tidak jalan
**Problem**: Notifikasi tidak update otomatis  
**Solution**:
1. Check `notifications.js` loaded di layout
2. Verify route `/notifications/recent` accessible
3. Check browser console untuk fetch errors

### Notifikasi tidak muncul
**Problem**: Notifikasi baru tidak muncul di dropdown  
**Solution**:
1. Verify notification sent: `php artisan tinker` → `User::first()->notifications`
2. Check notification data structure
3. Clear cache: `php artisan optimize:clear`

---

## ✅ Completion Checklist

- [x] Header dropdown implemented
- [x] Badge counter working
- [x] Controller methods added
- [x] Routes configured
- [x] JavaScript auto-refresh implemented
- [x] Notification index page improved
- [x] Icon mapping configured
- [x] Responsive design verified
- [x] Security measures implemented
- [x] Documentation completed
- [x] Cache cleared
- [x] Testing completed

---

## 📚 References

- **Metronic 8 Documentation**: Dropdown Menu Component
- **Laravel Notifications**: https://laravel.com/docs/notifications
- **Keenicons**: https://keenicons.com/
- **Bootstrap 5**: Dropdown & Badge Components

---

*Dokumentasi ini dibuat untuk memudahkan maintenance dan pengembangan fitur notifikasi di masa depan.*
