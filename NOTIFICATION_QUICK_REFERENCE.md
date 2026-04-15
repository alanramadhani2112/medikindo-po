# Notification System - Quick Reference

**Last Updated**: 14 April 2026  
**Version**: 1.0

---

## 🚀 Quick Start

### Mengirim Notifikasi Baru

```php
use App\Notifications\NewInvoiceNotification;

// Single user
$user->notify(new NewInvoiceNotification($invoice));

// Multiple users
Notification::send($users, new NewInvoiceNotification($invoice));
```

### Membuat Custom Notification

```php
namespace App\Notifications;

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
            'message' => 'Pesan detail',
            'icon' => 'check-circle',
            'type' => 'success',
            'url' => route('some.route'),
        ];
    }
}
```

---

## 📋 Data Structure

### Notification Data Format

```json
{
    "title": "Tagihan Supplier Baru",
    "message": "Invoice #INV-001 telah diterbitkan senilai Rp 1.000.000",
    "icon": "bill",
    "type": "info",
    "url": "http://medikindo-po.test/invoices/supplier/123"
}
```

### Icon & Type Mapping

| Type | Icon | Color | Use Case |
|------|------|-------|----------|
| `info` | `bill`, `notification` | Blue | Invoice, General |
| `success` | `check-circle`, `wallet` | Green | Approval, Payment |
| `danger` | `cross-circle` | Red | Rejection, Error |
| `warning` | `time` | Orange | Pending, Alert |
| `primary` | `document` | Blue | PO, Document |

---

## 🔌 API Endpoints

### Get Recent Notifications
```
GET /notifications/recent
Response: {
    "notifications": [...],
    "unread_count": 3
}
```

### Get Unread Count
```
GET /notifications/unread-count
Response: {
    "count": 3
}
```

### Mark as Read
```
GET /notifications/{id}/read
Redirect to notification URL
```

### Mark All as Read
```
POST /notifications/mark-all-read
Redirect back with success message
```

---

## 🎨 Frontend Components

### Notification Badge
```html
<span class="badge notification-badge">3</span>
```

### Notification Item
```html
<div class="notification-item unread">
    <div class="notification-icon">
        <i class="ki-outline ki-bill"></i>
    </div>
    <div class="notification-content">
        <div class="notification-title">Title</div>
        <div class="notification-message">Message</div>
        <div class="notification-time">2 minutes ago</div>
    </div>
    <span class="notification-new-badge">Baru</span>
</div>
```

---

## ⚙️ Configuration

### Auto-Refresh Interval
```javascript
// In public/js/notifications.js
this.refreshInterval = 30000; // 30 seconds
```

### Max Notifications in Dropdown
```php
// In header.blade.php
auth()->user()->notifications()->take(5)->get()
```

---

## 🐛 Troubleshooting

### Badge tidak update
```bash
# Clear browser cache
Ctrl + F5

# Clear Laravel cache
php artisan view:clear
php artisan config:clear
```

### Dropdown tidak muncul
```javascript
// Check console for errors
// Verify Metronic scripts loaded
// Check data-kt-menu attributes
```

### Auto-refresh tidak jalan
```javascript
// Check notifications.js loaded
// Verify route accessible
// Check browser console
```

---

## 📊 Database Query

### Get User Notifications
```php
$notifications = auth()->user()->notifications()->get();
```

### Get Unread Only
```php
$unread = auth()->user()->unreadNotifications()->get();
```

### Mark as Read
```php
$notification->markAsRead();
```

### Mark All as Read
```php
auth()->user()->unreadNotifications->markAsRead();
```

---

## 🔐 Security

### Authorization Check
```php
// Only user's own notifications
$request->user()->notifications()
```

### XSS Prevention
```blade
{{-- Blade auto-escaping --}}
{{ $notification->data['title'] }}
```

### CSRF Protection
```blade
<form method="POST">
    @csrf
    ...
</form>
```

---

## 📱 Responsive Classes

```css
/* Desktop */
.w-lg-375px { width: 375px; }

/* Tablet */
.w-350px { width: 350px; }

/* Mobile */
.mh-325px { max-height: 325px; }
```

---

## 🎯 Common Use Cases

### 1. Invoice Created
```php
$user->notify(new NewInvoiceNotification($invoice));
```

### 2. PO Approved
```php
$notification = [
    'title' => 'PO Disetujui',
    'message' => "PO #{$po->po_number} telah disetujui",
    'icon' => 'check-circle',
    'type' => 'success',
    'url' => route('web.po.show', $po)
];
```

### 3. Payment Received
```php
$notification = [
    'title' => 'Pembayaran Diterima',
    'message' => "Pembayaran Rp {$amount} telah diterima",
    'icon' => 'wallet',
    'type' => 'success',
    'url' => route('web.payments.index')
];
```

---

## 📝 Testing Checklist

- [ ] Dropdown muncul saat klik
- [ ] Badge counter benar
- [ ] Notifikasi terbaru muncul
- [ ] Click redirect ke URL
- [ ] Mark as read berfungsi
- [ ] Auto-refresh berjalan
- [ ] Empty state muncul
- [ ] Responsive di semua device

---

## 🔗 Related Files

- `resources/views/components/partials/header.blade.php` - Dropdown UI
- `app/Http/Controllers/Web/NotificationWebController.php` - Controller
- `routes/web.php` - Routes
- `public/js/notifications.js` - Auto-refresh
- `public/css/notifications.css` - Styling
- `resources/views/notifications/index.blade.php` - Full page

---

## 📚 Documentation

- Full Documentation: `NOTIFICATION_POPUP_IMPLEMENTATION.md`
- Session Summary: `SESSION_SUMMARY_APRIL_14_2026.md`
- Laravel Docs: https://laravel.com/docs/notifications

---

*Quick reference untuk development dan troubleshooting notification system.*
