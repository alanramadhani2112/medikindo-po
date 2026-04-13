# Medikindo Procurement System - Color System Standard

## Version: 1.0
## Date: April 13, 2026
## Status: ✅ ACTIVE

---

## 🎯 OBJECTIVE

Establish a **SEMANTIC COLOR SYSTEM** where every color communicates specific meaning consistently across the entire application.

**Core Principle:** Same status = Same color = Same meaning (EVERYWHERE)

---

## 🎨 COLOR PALETTE (Metronic Bootstrap)

### Available Colors
- **Primary** (Blue): Main actions, completed states
- **Success** (Green): Positive outcomes, active states, approvals
- **Danger** (Red): Negative outcomes, critical alerts, rejections
- **Warning** (Orange): Pending states, caution, needs attention
- **Info** (Light Blue): In-progress states, neutral information
- **Secondary** (Gray): Inactive states, neutral, disabled
- **Dark** (Dark Gray): System-level information

### Variants
- **Solid**: `badge-primary`, `btn-success` (high emphasis)
- **Light**: `badge-light-primary`, `btn-light-success` (low emphasis)

---

## 📋 STATUS BADGE SYSTEM

### Rule: Use LIGHT variants for all status badges
Exception: High-risk items use SOLID variants for maximum visibility

### Workflow Status

| Status | Badge Class | Color | Meaning |
|--------|-------------|-------|---------|
| Draft | `badge-light-secondary` | Gray | Not started |
| Pending | `badge-light-warning` | Orange | Waiting for action |
| Submitted | `badge-light-warning` | Orange | Waiting approval |
| Under Review | `badge-light-info` | Light Blue | Being processed |
| Approved | `badge-light-success` | Green | Approved |
| Rejected | `badge-light-danger` | Red | Rejected |
| Sent | `badge-light-primary` | Blue | Sent/Dispatched |
| Shipped | `badge-light-info` | Light Blue | In transit |
| In Delivery | `badge-light-info` | Light Blue | Being delivered |
| Delivered | `badge-light-success` | Green | Received |
| Completed | `badge-light-primary` | Blue | Finished |
| Cancelled | `badge-light-secondary` | Gray | Cancelled |

### Financial Status

| Status | Badge Class | Color | Meaning |
|--------|-------------|-------|---------|
| Unpaid | `badge-light-warning` | Orange | Payment pending |
| Paid | `badge-light-success` | Green | Payment complete |
| Overdue | `badge-light-danger` | Red | Past due date |
| Partial | `badge-light-info` | Light Blue | Partially paid |
| Payment Submitted | `badge-light-info` | Light Blue | Payment in process |
| Issued | `badge-light-warning` | Orange | Invoice issued |

### Active/Inactive Status

| Status | Badge Class | Color | Meaning |
|--------|-------------|-------|---------|
| Active / Aktif | `badge-light-success` | Green | Currently active |
| Inactive / Nonaktif | `badge-light-secondary` | Gray | Currently inactive |

### Risk Status (HIGH VISIBILITY)

| Status | Badge Class | Color | Meaning |
|--------|-------------|-------|---------|
| High Risk | `badge-danger` | Red (Solid) | Critical attention |
| Narcotic / Narkotika | `badge-danger` | Red (Solid) | Controlled substance |
| Credit Hold | `badge-light-warning` | Orange | Credit issue |

---

## 🔘 BUTTON SYSTEM

### Primary Actions (Page Level)
Use SOLID variants for main page actions

| Action | Button Class | Color | Usage |
|--------|--------------|-------|-------|
| Create / Tambah | `btn-primary` | Blue | Add new record |
| Submit | `btn-primary` | Blue | Submit form |
| Save / Simpan | `btn-primary` | Blue | Save changes |

### Positive Actions

| Action | Button Class | Color | Usage |
|--------|--------------|-------|-------|
| Approve / Setujui | `btn-success` | Green | Approve request |
| Confirm / Konfirmasi | `btn-success` | Green | Confirm action |
| Pay / Bayar | `btn-success` | Green | Process payment |
| Activate / Aktifkan | `btn-success` | Green | Activate record |

### Negative Actions

| Action | Button Class | Color | Usage |
|--------|--------------|-------|-------|
| Reject / Tolak | `btn-danger` | Red | Reject request |
| Delete / Hapus | `btn-danger` | Red | Delete record |
| Remove | `btn-danger` | Red | Remove item |

### Warning Actions

| Action | Button Class | Color | Usage |
|--------|--------------|-------|-------|
| Deactivate / Nonaktifkan | `btn-warning` | Orange | Deactivate record |
| Hold / Tahan | `btn-warning` | Orange | Put on hold |
| Suspend | `btn-warning` | Orange | Suspend access |

### Neutral Actions

| Action | Button Class | Color | Usage |
|--------|--------------|-------|-------|
| View / Lihat | `btn-light` | Gray | View details |
| Cancel / Batal | `btn-light` | Gray | Cancel action |
| Back / Kembali | `btn-light` | Gray | Go back |
| Reset | `btn-light` | Gray | Reset form |

### Table Action Buttons
Use LIGHT variants for table row actions (less visual weight)

| Action | Button Class | Color | Usage |
|--------|--------------|-------|-------|
| Edit | `btn-light-primary` | Light Blue | Edit record |
| View | `btn-light-info` | Light Blue | View details |
| Approve | `btn-light-success` | Light Green | Approve item |
| Reject | `btn-light-danger` | Light Red | Reject item |
| Toggle Status | `btn-light-warning` | Light Orange | Toggle active/inactive |
| Delete | `btn-light-danger` | Light Red | Delete item |

---

## 🎨 DASHBOARD CARD SYSTEM

### Card Background Colors

| Category | Background Class | Text Class | Usage |
|----------|------------------|------------|-------|
| Business Metrics | `bg-light-primary` | `text-primary` | PO counts, revenue |
| Financial | `bg-light-success` | `text-success` | Money, payments |
| Warnings | `bg-light-warning` | `text-warning` | Pending actions |
| Critical | `bg-light-danger` | `text-danger` | Errors, failures |
| Information | `bg-light-info` | `text-info` | General info |
| System | `bg-light-dark` | `text-dark` | System metrics |

### Card Icon Colors

| Type | Icon Class | Usage |
|------|------------|-------|
| Normal | `text-gray-600` | Default icons |
| Success | `text-success` | Positive metrics |
| Warning | `text-warning` | Attention needed |
| Error | `text-danger` | Critical issues |
| Info | `text-primary` | Information |

---

## 🚨 ALERT SYSTEM

### Alert Types

| Type | Alert Class | Usage |
|------|-------------|-------|
| Success | `alert-success` | Operation successful |
| Warning | `alert-warning` | Warning message |
| Error | `alert-danger` | Error occurred |
| Info | `alert-info` | Information message |

### Alert Structure
```html
<div class="alert alert-success d-flex align-items-center mb-5">
    <i class="ki-outline ki-check-circle fs-2 me-3"></i>
    <div>Success message here</div>
</div>
```

---

## 📝 USAGE EXAMPLES

### Status Badge
```blade
{{-- Workflow Status --}}
<span class="badge badge-light-warning fs-7 fw-semibold">PENDING</span>
<span class="badge badge-light-success fs-7 fw-semibold">APPROVED</span>
<span class="badge badge-light-danger fs-7 fw-semibold">REJECTED</span>

{{-- Financial Status --}}
<span class="badge badge-light-warning fs-7 fw-semibold">UNPAID</span>
<span class="badge badge-light-success fs-7 fw-semibold">PAID</span>
<span class="badge badge-light-danger fs-7 fw-semibold">OVERDUE</span>

{{-- Active/Inactive --}}
<span class="badge badge-light-success fs-7 fw-semibold">AKTIF</span>
<span class="badge badge-light-secondary fs-7 fw-semibold">NONAKTIF</span>

{{-- High Risk (SOLID) --}}
<span class="badge badge-danger fs-7 fw-bold">NARKOTIKA</span>
```

### Buttons
```blade
{{-- Page Actions --}}
<a href="#" class="btn btn-primary">Tambah Data</a>
<button type="submit" class="btn btn-success">Setujui</button>
<button type="button" class="btn btn-danger">Tolak</button>

{{-- Table Actions --}}
<a href="#" class="btn btn-sm btn-light-primary">Edit</a>
<button class="btn btn-sm btn-light-success">Approve</button>
<button class="btn btn-sm btn-light-danger">Delete</button>
```

### Using Status Badge Component
```blade
{{-- Automatic color mapping --}}
<x-status-badge :status="$order->status" />
<x-status-badge :status="$invoice->status" type="financial" />
<x-status-badge :status="$user->is_active" type="active" />
<x-status-badge :status="'narcotic'" type="risk" />
```

---

## ✅ VALIDATION CHECKLIST

Before deploying any UI changes, verify:

- [ ] Same status uses same color across all modules
- [ ] Status badges use LIGHT variants (except high-risk)
- [ ] Table action buttons use LIGHT variants
- [ ] Page action buttons use SOLID variants
- [ ] Alert colors match message severity
- [ ] Dashboard cards use semantic colors
- [ ] No random color usage
- [ ] Color communicates meaning clearly

---

## 🚫 COMMON MISTAKES TO AVOID

### ❌ DON'T DO THIS:
```blade
{{-- Inconsistent status colors --}}
<span class="badge badge-success">AKTIF</span>  <!-- Solid in one place -->
<span class="badge badge-light-success">AKTIF</span>  <!-- Light in another -->

{{-- Random color usage --}}
<span class="badge badge-primary">PENDING</span>  <!-- Wrong! Should be warning -->

{{-- Solid buttons in tables --}}
<a href="#" class="btn btn-primary">Edit</a>  <!-- Too heavy for table -->
```

### ✅ DO THIS:
```blade
{{-- Consistent status colors --}}
<span class="badge badge-light-success fs-7 fw-semibold">AKTIF</span>  <!-- Everywhere -->

{{-- Semantic color usage --}}
<span class="badge badge-light-warning fs-7 fw-semibold">PENDING</span>  <!-- Correct -->

{{-- Light buttons in tables --}}
<a href="#" class="btn btn-sm btn-light-primary">Edit</a>  <!-- Perfect -->
```

---

## 🔧 MAINTENANCE

### Adding New Status
1. Determine status category (workflow/financial/active/risk)
2. Choose appropriate color based on meaning
3. Add to status badge component
4. Update this documentation
5. Apply consistently across all views

### Changing Colors
1. Update color system documentation first
2. Update status badge component
3. Search and replace across all views
4. Test thoroughly
5. Update screenshots/training materials

---

## 📚 REFERENCES

- Metronic Bootstrap Documentation: https://preview.keenthemes.com/metronic8/demo42/
- Bootstrap Color System: https://getbootstrap.com/docs/5.3/utilities/colors/
- Keenicons: https://keenicons.com/

---

## 📞 SUPPORT

For questions about color usage:
1. Check this documentation first
2. Review existing implementations
3. Use status badge component when possible
4. Maintain consistency above all

---

**Last Updated**: April 13, 2026  
**Version**: 1.0  
**Status**: Active Standard
