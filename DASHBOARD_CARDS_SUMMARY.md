# Dashboard Cards Summary - Quick Reference

## 📊 CARDS BY ROLE

### 🔴 SUPER ADMIN (11 cards)

| # | Card Name | Icon | Color | Metric Example |
|---|-----------|------|-------|----------------|
| 1 | Total Purchase Orders | `ki-purchase` | primary | 245 POs |
| 2 | Active Organizations | `ki-people` | info | 18 orgs |
| 3 | Pending Approvals | `ki-time` | warning | 12 waiting |
| 4 | System Health | `ki-shield-tick` | success | All OK |
| 5 | Total AR | `ki-arrow-up` | success | Rp 125M |
| 6 | Total AP | `ki-arrow-down` | danger | Rp 85M |
| 7 | Net Cash | `ki-wallet` | primary | Rp 40M |
| 8 | Goods Receipts | `ki-questionnaire-tablet` | success | 45 GR |
| 9 | Invoices Generated | `ki-bill` | info | 38 invoices |
| 10 | Payments Processed | `ki-wallet` | success | Rp 95M |
| 11 | Active Users | `ki-user` | primary | 45 users |

---

### 🟢 HEALTHCARE USER (11 cards)

| # | Card Name | Icon | Color | Metric Example |
|---|-----------|------|-------|----------------|
| 1 | My Active POs | `ki-purchase` | primary | 8 POs |
| 2 | Pending Approvals | `ki-time` | warning | 3 waiting |
| 3 | Awaiting Delivery | `ki-questionnaire-tablet` | info | 5 shipped |
| 4 | Completed This Month | `ki-check-circle` | success | 12 done |
| 5 | Outstanding Invoices | `ki-bill` | warning | Rp 15M |
| 6 | Credit Limit Status | `ki-chart-line` | danger | 75% used |
| 7 | Payment Due Soon | `ki-wallet` | warning | Rp 8M |
| 8 | Create New PO | `ki-plus` | primary | Quick action |
| 9 | Check Inventory | `ki-capsule` | info | 125 products |
| 10 | Recent Deliveries | `ki-questionnaire-tablet` | success | 3 this week |
| 11 | Supplier Contacts | `ki-cube-2` | secondary | 5 suppliers |

---

### 🟡 APPROVER (11 cards)

| # | Card Name | Icon | Color | Metric Example |
|---|-----------|------|-------|----------------|
| 1 | Pending My Approval | `ki-basket-ok` | danger | 7 POs |
| 2 | Approved Today | `ki-check-circle` | success | 5 approved |
| 3 | Rejected This Week | `ki-cross-circle` | warning | 2 rejected |
| 4 | Approval Rate | `ki-chart-line` | info | 94% |
| 5 | High Value POs | `ki-information-5` | warning | 3 POs >50M |
| 6 | Narcotic Items | `ki-shield-cross` | danger | 2 POs |
| 7 | Budget Alerts | `ki-chart-line` | warning | 2 orgs |
| 8 | Avg Approval Time | `ki-time` | info | 1.8 hours |
| 9 | Total Approved Value | `ki-wallet` | success | Rp 125M |
| 10 | Organizations Served | `ki-people` | primary | 12 orgs |
| 11 | Compliance Score | `ki-shield-tick` | success | 98% |

---

### 🔵 FINANCE (12 cards)

| # | Card Name | Icon | Color | Metric Example |
|---|-----------|------|-------|----------------|
| 1 | Total AR | `ki-arrow-up` | success | Rp 125M |
| 2 | Total AP | `ki-arrow-down` | danger | Rp 85M |
| 3 | Net Cash Position | `ki-wallet` | primary | Rp 40M |
| 4 | Payment Due This Week | `ki-time` | warning | Rp 25M |
| 5 | Unpaid AR Invoices | `ki-bill` | warning | 12 invoices |
| 6 | Overdue AR | `ki-information-5` | danger | 3 overdue |
| 7 | Unpaid AP Invoices | `ki-arrow-down` | warning | 8 invoices |
| 8 | Overdue AP | `ki-information-5` | danger | 1 overdue |
| 9 | Orgs Near Limit | `ki-chart-line` | warning | 3 orgs |
| 10 | Credit Exceeded | `ki-cross-circle` | danger | 1 org |
| 11 | Payments Received | `ki-wallet` | success | Rp 15M |
| 12 | Payments Made | `ki-wallet` | info | Rp 10M |

---

## 🎯 PRIORITY METRICS BY ROLE

### Super Admin (Top 3):
1. **System Health** - Is everything running?
2. **Pending Approvals** - Any bottlenecks?
3. **Net Cash Position** - Financial health?

### Healthcare User (Top 3):
1. **Pending Approvals** - When will my PO be approved?
2. **Credit Limit Status** - Can I create more POs?
3. **Awaiting Delivery** - When will goods arrive?

### Approver (Top 3):
1. **Pending My Approval** - What needs my decision?
2. **Narcotic Items** - Any high-risk POs?
3. **High Value POs** - Any budget concerns?

### Finance (Top 3):
1. **Overdue AR** - Who hasn't paid?
2. **Overdue AP** - What do we owe?
3. **Credit Exceeded** - Who's blocked?

---

## 🚨 ALERT THRESHOLDS

| Metric | Warning | Danger | Action |
|--------|---------|--------|--------|
| Credit Utilization | >70% | >90% | Block new PO |
| Approval Time | >2 days | >3 days | Escalate |
| Invoice Overdue | >7 days | >14 days | Follow up |
| Payment Due | <7 days | <3 days | Schedule payment |
| Approval Rate | <85% | <75% | Review process |

---

## 📱 CARD LAYOUT

### Desktop (4 columns):
```
┌────┬────┬────┬────┐
│ 1  │ 2  │ 3  │ 4  │
├────┼────┼────┼────┤
│ 5  │ 6  │ 7  │ 8  │
├────┼────┼────┼────┤
│ 9  │ 10 │ 11 │ 12 │
└────┴────┴────┴────┘
```

### Tablet (2 columns):
```
┌────┬────┐
│ 1  │ 2  │
├────┼────┤
│ 3  │ 4  │
├────┼────┤
│ 5  │ 6  │
└────┴────┘
```

### Mobile (1 column):
```
┌────┐
│ 1  │
├────┤
│ 2  │
├────┤
│ 3  │
└────┘
```

---

## 🎨 COLOR SCHEME

| Color | Usage | Example |
|-------|-------|---------|
| 🔴 Red/Danger | Critical, Overdue, Exceeded | Overdue invoices |
| 🟡 Yellow/Warning | Attention, Approaching | Near credit limit |
| 🟢 Green/Success | Healthy, Completed | Approved POs |
| 🔵 Blue/Primary | Informational, Neutral | Total count |
| ⚪ Gray/Secondary | Supporting info | Quick links |

---

## 💡 IMPLEMENTATION TIPS

### 1. **Start Simple**:
- Implement Row 1 (top 4 cards) first
- Add more rows incrementally
- Test with real users

### 2. **Make it Actionable**:
- Every card should have a click action
- Link to relevant page/filter
- Show "View Details" on hover

### 3. **Real-time Updates**:
- Use Laravel Echo for live updates
- Refresh critical metrics every 30s
- Show "Updated X mins ago"

### 4. **Performance**:
- Cache dashboard data (5 mins)
- Use eager loading
- Optimize queries with indexes

### 5. **Mobile First**:
- Design for mobile screens first
- Progressive enhancement for desktop
- Touch-friendly buttons

---

## 📊 DATA SOURCES

### Super Admin:
```php
- PurchaseOrder::count()
- Organization::where('is_active', true)->count()
- Approval::where('status', 'pending')->count()
- CustomerInvoice::sum('total_amount - paid_amount')
- SupplierInvoice::sum('total_amount - paid_amount')
```

### Healthcare User:
```php
- PurchaseOrder::where('organization_id', $user->org)->count()
- PurchaseOrder::where('status', 'submitted')->count()
- CustomerInvoice::where('organization_id', $user->org)->unpaid()->sum()
- CreditLimit::where('organization_id', $user->org)->first()
```

### Approver:
```php
- Approval::where('approver_id', $user->id)->pending()->count()
- Approval::where('approver_id', $user->id)->today()->approved()->count()
- PurchaseOrder::where('total_amount', '>', 50000000)->pending()->count()
- PurchaseOrder::where('has_narcotics', true)->pending()->count()
```

### Finance:
```php
- CustomerInvoice::unpaid()->sum('total_amount - paid_amount')
- SupplierInvoice::unpaid()->sum('total_amount - paid_amount')
- CustomerInvoice::overdue()->count()
- SupplierInvoice::overdue()->count()
- CreditLimit::where('utilization', '>', 0.8)->count()
```

---

**Status**: QUICK REFERENCE
**Date**: 2026-04-14
**System**: Medikindo PO
