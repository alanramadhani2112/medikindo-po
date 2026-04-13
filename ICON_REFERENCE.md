# 🎨 ICON REFERENCE - Quick Guide

## 📋 Sidebar Menu Icons

```
MAIN
├─ Dashboard          → ki-element-11 (grid)

PROCUREMENT
├─ Purchase Orders    → ki-purchase (shopping cart)
├─ Approvals          → ki-check-square (checkbox)
└─ Goods Receipt      → ki-package (package/box)

FINANCE
├─ Invoices           → ki-file-sheet (document)
├─ Payments           → ki-wallet (wallet)
└─ Credit Control     → ki-chart-simple (chart)

MASTER DATA
├─ Organizations      → ki-bank (building)
├─ Suppliers          → ki-delivery-3 (truck)
├─ Products           → ki-capsule (pill/medicine)
└─ Users              → ki-profile-user (user profile)
```

---

## 🔧 Action Button Icons

```
CRUD Actions
├─ Create/Add         → ki-plus
├─ View/Detail        → ki-eye
├─ Edit/Update        → ki-note-2
└─ Delete/Remove      → ki-trash

Other Actions
├─ Search/Filter      → ki-magnifier
├─ Download/Export    → ki-file-down
├─ Reset/Clear        → ki-cross
├─ Send/Submit        → ki-send
└─ Approve/Confirm    → ki-check
```

---

## 📊 Status Icons

```
Success               → ki-check-circle
Error/Failed          → ki-cross-circle
Warning/Info          → ki-information-5
Pending/Wait          → ki-time
```

---

## 💡 Usage Example

### Sidebar Menu
```blade
<span class="menu-icon">
    <i class="ki-outline ki-purchase fs-2"></i>
</span>
<span class="menu-title">Purchase Orders</span>
```

### Action Button
```blade
<button class="btn btn-sm btn-light-primary">
    <i class="ki-outline ki-note-2 fs-4"></i>
    Edit
</button>
```

---

## ✅ Icon Guidelines

1. **Always use Keenicons**: `ki-outline ki-{name}`
2. **Sidebar icons**: `fs-2` (larger)
3. **Button icons**: `fs-4` (smaller)
4. **Be contextual**: Icon harus sesuai fungsi
5. **Be consistent**: Gunakan icon yang sama untuk fungsi yang sama

---

**Date**: April 13, 2026
**Status**: ✅ Complete
