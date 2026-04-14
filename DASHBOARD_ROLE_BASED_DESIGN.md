# Dashboard Role-Based Design - Medikindo PO System

## 🎯 OBJECTIVE
Setiap role melihat **data yang relevan** dengan tanggung jawab mereka untuk **decision making** yang lebih baik.

---

## 👥 ROLE ANALYSIS

### 1. **SUPER ADMIN** (System-wide view)
**Tanggung Jawab**: Oversight seluruh sistem, monitoring performa, manage users
**Kebutuhan**: High-level metrics, system health, cross-organization analytics

### 2. **HEALTHCARE USER** (Organization-specific)
**Tanggung Jawab**: Buat PO, track pengiriman, manage inventory untuk klinik/RS mereka
**Kebutuhan**: PO status, inventory alerts, pending deliveries

### 3. **APPROVER** (Decision maker)
**Tanggung Jawab**: Approve/reject PO, ensure compliance, budget control
**Kebutuhan**: Pending approvals, approval history, budget utilization

### 4. **FINANCE** (Financial oversight)
**Tanggung Jawab**: Manage invoices, payments, credit control, financial reporting
**Kebutuhan**: AR/AP status, payment due dates, cash flow, credit limits

---

## 📊 DASHBOARD CARDS BY ROLE

### 🔴 SUPER ADMIN DASHBOARD

#### **Row 1: System Overview (4 cards)**

**Card 1: Total Purchase Orders**
```
Icon: ki-purchase
Color: primary
Metric: Total PO count (all status)
Sub-metric: "vs last month: +12%"
Action: View all POs
```

**Card 2: Active Organizations**
```
Icon: ki-people
Color: info
Metric: Active klinik/RS count
Sub-metric: "Total: X hospitals, Y clinics"
Action: Manage organizations
```

**Card 3: Pending Approvals**
```
Icon: ki-time
Color: warning
Metric: PO waiting approval
Sub-metric: "Avg approval time: 2.3 days"
Action: View approval queue
```

**Card 4: System Health**
```
Icon: ki-shield-tick
Color: success
Metric: "All Systems Operational"
Sub-metric: "Last check: 2 mins ago"
Action: View system logs
```

#### **Row 2: Financial Overview (3 cards)**

**Card 5: Total AR (Accounts Receivable)**
```
Icon: ki-arrow-up
Color: success
Metric: Rp 125,000,000
Sub-metric: "Outstanding from 15 organizations"
Action: View AR details
```

**Card 6: Total AP (Accounts Payable)**
```
Icon: ki-arrow-down
Color: danger
Metric: Rp 85,000,000
Sub-metric: "Due to 8 suppliers"
Action: View AP details
```

**Card 7: Net Cash Position**
```
Icon: ki-wallet
Color: primary
Metric: Rp 40,000,000
Sub-metric: "AR - AP balance"
Action: View cash flow
```

#### **Row 3: Activity Metrics (4 cards)**

**Card 8: Goods Receipts (This Month)**
```
Icon: ki-questionnaire-tablet
Color: success
Metric: 45 GR processed
Sub-metric: "Total items: 1,250"
Action: View GR history
```

**Card 9: Invoices Generated**
```
Icon: ki-bill
Color: info
Metric: 38 invoices
Sub-metric: "Total value: Rp 210M"
Action: View invoices
```

**Card 10: Payments Processed**
```
Icon: ki-wallet
Color: success
Metric: Rp 95,000,000
Sub-metric: "23 transactions"
Action: View payments
```

**Card 11: Active Users**
```
Icon: ki-user
Color: primary
Metric: 45 users
Sub-metric: "Last login: 2 mins ago"
Action: Manage users
```

#### **Charts & Tables:**
- **Chart 1**: PO Trend (Last 6 months) - Line chart
- **Chart 2**: Top 5 Organizations by PO Value - Bar chart
- **Chart 3**: Approval Rate by Level - Donut chart
- **Table 1**: Recent PO Activity (Last 10)
- **Table 2**: Overdue Invoices Alert

---

### 🟢 HEALTHCARE USER DASHBOARD

#### **Row 1: My Organization Overview (4 cards)**

**Card 1: My Active POs**
```
Icon: ki-purchase
Color: primary
Metric: 8 active POs
Sub-metric: "Total value: Rp 45M"
Action: View my POs
```

**Card 2: Pending Approvals**
```
Icon: ki-time
Color: warning
Metric: 3 POs waiting
Sub-metric: "Oldest: 2 days ago"
Action: View pending
```

**Card 3: Awaiting Delivery**
```
Icon: ki-questionnaire-tablet
Color: info
Metric: 5 POs shipped
Sub-metric: "Expected this week"
Action: Track deliveries
```

**Card 4: Completed This Month**
```
Icon: ki-check-circle
Color: success
Metric: 12 POs completed
Sub-metric: "On-time: 92%"
Action: View history
```

#### **Row 2: Financial Status (3 cards)**

**Card 5: Outstanding Invoices**
```
Icon: ki-bill
Color: warning
Metric: Rp 15,000,000
Sub-metric: "3 invoices unpaid"
Action: View invoices
```

**Card 6: Credit Limit Status**
```
Icon: ki-chart-line
Color: danger (if >80%)
Metric: 75% utilized
Sub-metric: "Available: Rp 12.5M"
Action: View credit details
```

**Card 7: Payment Due Soon**
```
Icon: ki-wallet
Color: warning
Metric: Rp 8,000,000
Sub-metric: "Due in 7 days"
Action: Make payment
```

#### **Row 3: Quick Actions (4 cards)**

**Card 8: Create New PO**
```
Icon: ki-plus
Color: primary
Action: Quick create PO
```

**Card 9: Check Inventory**
```
Icon: ki-capsule
Color: info
Metric: 125 products
Action: View products
```

**Card 10: Recent Deliveries**
```
Icon: ki-questionnaire-tablet
Color: success
Metric: 3 this week
Action: View GR
```

**Card 11: Supplier Contacts**
```
Icon: ki-cube-2
Color: secondary
Metric: 5 suppliers
Action: View suppliers
```

#### **Charts & Tables:**
- **Chart 1**: My PO Status Distribution - Donut chart
- **Chart 2**: Monthly Spending Trend - Line chart
- **Table 1**: My Recent POs (Last 10)
- **Table 2**: Upcoming Deliveries

---

### 🟡 APPROVER DASHBOARD

#### **Row 1: Approval Queue (4 cards)**

**Card 1: Pending My Approval**
```
Icon: ki-basket-ok
Color: danger (urgent)
Metric: 7 POs waiting
Sub-metric: "2 urgent (>3 days)"
Action: Review now
```

**Card 2: Approved Today**
```
Icon: ki-check-circle
Color: success
Metric: 5 POs approved
Sub-metric: "Avg time: 1.2 hours"
Action: View approved
```

**Card 3: Rejected This Week**
```
Icon: ki-cross-circle
Color: warning
Metric: 2 POs rejected
Sub-metric: "Reasons: Budget, Compliance"
Action: View rejected
```

**Card 4: Approval Rate**
```
Icon: ki-chart-line
Color: info
Metric: 94% approved
Sub-metric: "This month"
Action: View analytics
```

#### **Row 2: Risk Alerts (3 cards)**

**Card 5: High Value POs**
```
Icon: ki-information-5
Color: warning
Metric: 3 POs > Rp 50M
Sub-metric: "Require extra review"
Action: Review high value
```

**Card 6: Narcotic Items**
```
Icon: ki-shield-cross
Color: danger
Metric: 2 POs with narcotics
Sub-metric: "Level 2 approval needed"
Action: Review narcotics
```

**Card 7: Budget Alerts**
```
Icon: ki-chart-line
Color: warning
Metric: 2 orgs near limit
Sub-metric: ">80% credit utilized"
Action: View budget status
```

#### **Row 3: Performance Metrics (4 cards)**

**Card 8: Avg Approval Time**
```
Icon: ki-time
Color: info
Metric: 1.8 hours
Sub-metric: "Target: <2 hours"
Action: View details
```

**Card 9: Total Approved Value**
```
Icon: ki-wallet
Color: success
Metric: Rp 125,000,000
Sub-metric: "This month"
Action: View breakdown
```

**Card 10: Organizations Served**
```
Icon: ki-people
Color: primary
Metric: 12 organizations
Sub-metric: "Active this month"
Action: View orgs
```

**Card 11: Compliance Score**
```
Icon: ki-shield-tick
Color: success
Metric: 98% compliant
Sub-metric: "All checks passed"
Action: View compliance
```

#### **Charts & Tables:**
- **Chart 1**: Approval Trend (Last 30 days) - Line chart
- **Chart 2**: Rejection Reasons - Bar chart
- **Table 1**: Urgent Approvals (Priority sorted)
- **Table 2**: Recent Approval History

---

### 🔵 FINANCE DASHBOARD

#### **Row 1: Cash Flow Overview (4 cards)**

**Card 1: Total AR (Receivables)**
```
Icon: ki-arrow-up
Color: success
Metric: Rp 125,000,000
Sub-metric: "From 15 organizations"
Action: View AR aging
```

**Card 2: Total AP (Payables)**
```
Icon: ki-arrow-down
Color: danger
Metric: Rp 85,000,000
Sub-metric: "To 8 suppliers"
Action: View AP aging
```

**Card 3: Net Cash Position**
```
Icon: ki-wallet
Color: primary
Metric: Rp 40,000,000
Sub-metric: "AR - AP balance"
Action: View cash flow
```

**Card 4: Payment Due This Week**
```
Icon: ki-time
Color: warning
Metric: Rp 25,000,000
Sub-metric: "5 invoices due"
Action: Schedule payments
```

#### **Row 2: Invoice Status (4 cards)**

**Card 5: Unpaid AR Invoices**
```
Icon: ki-bill
Color: warning
Metric: 12 invoices
Sub-metric: "Total: Rp 45M"
Action: Send reminders
```

**Card 6: Overdue AR**
```
Icon: ki-information-5
Color: danger
Metric: 3 invoices overdue
Sub-metric: "Total: Rp 8M"
Action: Follow up
```

**Card 7: Unpaid AP Invoices**
```
Icon: ki-arrow-down
Color: warning
Metric: 8 invoices
Sub-metric: "Total: Rp 35M"
Action: Process payments
```

**Card 8: Overdue AP**
```
Icon: ki-information-5
Color: danger
Metric: 1 invoice overdue
Sub-metric: "Total: Rp 2M"
Action: Pay now
```

#### **Row 3: Credit Control (4 cards)**

**Card 9: Organizations Near Limit**
```
Icon: ki-chart-line
Color: warning
Metric: 3 organizations
Sub-metric: ">80% credit used"
Action: Review limits
```

**Card 10: Credit Limit Exceeded**
```
Icon: ki-cross-circle
Color: danger
Metric: 1 organization
Sub-metric: "Blocked from new PO"
Action: Take action
```

**Card 11: Payments Received Today**
```
Icon: ki-wallet
Color: success
Metric: Rp 15,000,000
Sub-metric: "5 transactions"
Action: View payments
```

**Card 12: Payments Made Today**
```
Icon: ki-wallet
Color: info
Metric: Rp 10,000,000
Sub-metric: "3 transactions"
Action: View payments
```

#### **Charts & Tables:**
- **Chart 1**: AR vs AP Trend (6 months) - Line chart
- **Chart 2**: AR Aging Analysis - Bar chart
- **Chart 3**: Top 5 Debtors - Bar chart
- **Chart 4**: Payment Collection Rate - Gauge chart
- **Table 1**: Overdue Invoices (Priority)
- **Table 2**: Upcoming Payment Schedule

---

## 🎨 DESIGN PRINCIPLES

### Visual Hierarchy:
1. **Critical Metrics** (Top row) - Largest, most prominent
2. **Secondary Metrics** (Middle row) - Medium size
3. **Quick Actions** (Bottom row) - Smaller, action-oriented

### Color Coding:
- 🔴 **Red/Danger**: Urgent action needed, overdue, exceeded
- 🟡 **Yellow/Warning**: Attention needed, approaching limit
- 🟢 **Green/Success**: Healthy status, completed, on-track
- 🔵 **Blue/Primary**: Informational, neutral metrics
- ⚪ **Gray/Secondary**: Supporting information

### Card Structure:
```
┌─────────────────────────────┐
│ Icon (top-right)            │
│                             │
│ METRIC (large, bold)        │
│ Label (small, muted)        │
│                             │
│ Sub-metric (badge/text)     │
│ Action link (optional)      │
└─────────────────────────────┘
```

---

## 📱 RESPONSIVE BEHAVIOR

### Desktop (xl):
- 4 cards per row
- Full charts visible
- Tables with all columns

### Tablet (md):
- 2 cards per row
- Simplified charts
- Tables with key columns

### Mobile (sm):
- 1 card per row
- Chart summaries only
- Tables with minimal columns

---

## 🔔 ALERT SYSTEM

### Priority Levels:
1. **Critical** (Red badge): Immediate action required
2. **High** (Orange badge): Action needed today
3. **Medium** (Yellow badge): Action needed this week
4. **Low** (Blue badge): Informational

### Alert Types:
- 🚨 **Overdue invoices** (Finance)
- ⚠️ **Pending approvals >3 days** (Approver)
- 📊 **Credit limit >90%** (Healthcare User, Finance)
- 🔒 **Credit limit exceeded** (Healthcare User, Finance)
- 📦 **Delayed deliveries** (Healthcare User)
- 💊 **Narcotic PO pending** (Approver)

---

## 🎯 ACTIONABLE INSIGHTS

### Each card should answer:
1. **What?** - The metric/status
2. **So what?** - Why it matters (sub-metric)
3. **Now what?** - What action to take (button/link)

### Example:
```
Card: "Pending Approvals"
What: 7 POs waiting
So what: 2 urgent (>3 days)
Now what: [Review Now] button
```

---

## 📊 RECOMMENDED CHARTS

### Super Admin:
1. PO Volume Trend (6 months)
2. Organization Performance Comparison
3. Approval Rate by Level
4. Financial Health Score

### Healthcare User:
1. My PO Status Distribution
2. Monthly Spending Trend
3. Delivery Performance
4. Credit Utilization

### Approver:
1. Approval Volume Trend
2. Rejection Reasons
3. Approval Time Distribution
4. Organization Risk Score

### Finance:
1. AR vs AP Trend
2. AR Aging Analysis
3. Payment Collection Rate
4. Cash Flow Forecast

---

## 🚀 IMPLEMENTATION PRIORITY

### Phase 1 (Critical):
1. ✅ Role-based card filtering
2. ✅ Critical metrics (Row 1 for each role)
3. ✅ Alert system
4. ✅ Quick actions

### Phase 2 (Important):
1. Charts integration
2. Drill-down functionality
3. Export capabilities
4. Real-time updates

### Phase 3 (Nice to have):
1. Customizable dashboards
2. Widget drag-and-drop
3. Saved filters
4. Email alerts

---

## 💡 KEY RECOMMENDATIONS

### 1. **Super Admin**:
Focus on **system-wide health** and **cross-organization analytics**
- Total metrics across all organizations
- System performance indicators
- User activity monitoring
- Financial overview (AR + AP)

### 2. **Healthcare User**:
Focus on **their organization's operations**
- My PO status and tracking
- Credit limit monitoring
- Delivery tracking
- Quick PO creation

### 3. **Approver**:
Focus on **decision-making efficiency**
- Pending approval queue (priority sorted)
- Risk alerts (high value, narcotics)
- Approval performance metrics
- Compliance monitoring

### 4. **Finance**:
Focus on **cash flow and credit control**
- AR/AP aging analysis
- Overdue invoice alerts
- Credit limit monitoring
- Payment scheduling

---

## 📋 NEXT STEPS

1. **Review & Approve** this design with stakeholders
2. **Create wireframes** for each role dashboard
3. **Implement backend** data aggregation in DashboardService
4. **Build frontend** components with role-based rendering
5. **Test with real users** from each role
6. **Iterate based on feedback**

---

**Status**: DESIGN PROPOSAL
**Date**: 2026-04-14
**System**: Medikindo PO
**Designer**: Kiro AI Assistant
