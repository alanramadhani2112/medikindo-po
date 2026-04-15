# Dashboard Essential Data by Role

**Tanggal**: 14 April 2026  
**Prinsip**: Quick Reading, Quick Decision, Actionable Insights  
**Filosofi**: "Show me what I need to ACT on, not just what I need to KNOW"

---

## 🎯 Design Philosophy

### The 3-Second Rule
**User harus bisa jawab 3 pertanyaan dalam 3 detik:**
1. **What's wrong?** (Alerts/Problems)
2. **What's my status?** (KPIs)
3. **What should I do?** (Actions)

### The 5-Card Rule
**Maximum 5 KPI cards per section** (lebih dari itu = information overload)

### The Visual First Rule
**Chart > Number > Table** (otak proses visual 60,000x lebih cepat)

---

## 👨‍⚕️ ROLE 1: Healthcare User (RS/Klinik)

### Primary Goal:
**"Saya perlu order obat, apakah saya bisa? Berapa sisa kredit saya?"**

### Essential Data (Priority Order):

#### 🔴 CRITICAL (Must See First - 3 seconds)
```
1. CREDIT STATUS
   - Sisa Kredit: Rp 50 juta
   - Utilisasi: 75% ⚠️
   - Status: AMAN / WARNING / DANGER
   
   Visual: Progress bar dengan color coding
   ┌────────────────────────────────┐
   │ Credit Limit                   │
   │ ████████████░░░░░░░░ 75%      │
   │ Rp 75M / Rp 100M              │
   │ Sisa: Rp 25M                  │
   └────────────────────────────────┘
```

#### 🟡 IMPORTANT (Need to Know - 10 seconds)
```
2. MY ACTIVE POs
   - PO Aktif: 5 PO
   - Menunggu Approval: 2 PO ⏳
   - Dalam Pengiriman: 3 PO 🚚
   
3. PAYMENT DUE SOON
   - Jatuh Tempo 7 Hari: Rp 10 juta ⚠️
   - Overdue: Rp 0 ✅
   
4. OUTSTANDING INVOICES
   - Total Belum Bayar: Rp 30 juta
   - Jumlah Invoice: 5 invoice
```

#### 🟢 NICE TO KNOW (Context - 30 seconds)
```
5. RECENT DELIVERIES
   - Minggu Ini: 3 deliveries
   - Status: All received ✅
   
6. QUICK STATS
   - Total PO Bulan Ini: 12 PO
   - Total Spending: Rp 150 juta
```

### Visual Layout:
```
┌─────────────────────────────────────────────────┐
│ 🔴 ALERT: Credit 75% - Bayar invoice segera!   │
└─────────────────────────────────────────────────┘

┌──────────────┬──────────────┬──────────────────┐
│ Credit Status│ PO Aktif     │ Payment Due Soon │
│ 75% ⚠️       │ 5 PO         │ Rp 10M (7 hari)  │
│ Rp 25M sisa  │ 2 pending    │ ⚠️ Urgent        │
└──────────────┴──────────────┴──────────────────┘

┌─────────────────────────────────────────────────┐
│ CREDIT UTILIZATION TREND (Line Chart)          │
│ 100% ┤                                    ╭─── │
│  75% ┤                          ╭────────╯     │
│  50% ┤                ╭────────╯               │
│  25% ┤      ╭────────╯                         │
│   0% └──────────────────────────────────       │
│      Jan   Feb   Mar   Apr   May   Jun         │
└─────────────────────────────────────────────────┘

┌──────────────────────┬──────────────────────────┐
│ MY RECENT POs        │ QUICK ACTIONS            │
│ (Table - 5 rows)     │ [Buat PO Baru]          │
│                      │ [Bayar Invoice]          │
│                      │ [Cek Delivery]           │
└──────────────────────┴──────────────────────────┘
```

### Actionable Insights:
```
✅ "Kredit Anda masih aman, bisa order hingga Rp 25M"
⚠️ "Utilisasi 75%, segera bayar invoice untuk hindari limit"
🔴 "Kredit PENUH! Bayar Rp 10M untuk bisa order lagi"
```

---

## 👔 ROLE 2: Approver

### Primary Goal:
**"Ada berapa PO yang harus saya approve? Mana yang urgent?"**

### Essential Data (Priority Order):

#### 🔴 CRITICAL (Must See First - 3 seconds)
```
1. PENDING APPROVAL QUEUE
   - Total Pending: 15 PO
   - Urgent (>3 hari): 5 PO 🔴
   - High Value (>50M): 3 PO ⚠️
   - Narkotika: 2 PO 🚨
   
   Visual: Big number dengan breakdown
   ┌────────────────────────────────┐
   │ PENDING APPROVAL               │
   │        15 PO                   │
   │                                │
   │ 🔴 5 Urgent (>3 days)         │
   │ ⚠️ 3 High Value (>50M)        │
   │ 🚨 2 Narkotika                │
   └────────────────────────────────┘
```

#### 🟡 IMPORTANT (Need to Know - 10 seconds)
```
2. TODAY'S ACTIVITY
   - Approved Today: 8 PO ✅
   - Rejected Today: 1 PO ❌
   - Avg Approval Time: 1.5 hours ⚡
   
3. THIS WEEK SUMMARY
   - Total Approved: 45 PO
   - Total Rejected: 3 PO
   - Approval Rate: 94% 📈
   
4. RISK ALERTS
   - Budget Exceeded: 2 orgs ⚠️
   - Compliance Issues: 0 ✅
```

#### 🟢 NICE TO KNOW (Context - 30 seconds)
```
5. PERFORMANCE METRICS
   - Avg Approval Time: 1.5h (Target: <2h) ✅
   - Total Approved Value: Rp 500M
   - Organizations Served: 25 orgs
```

### Visual Layout:
```
┌─────────────────────────────────────────────────┐
│ 🔴 URGENT: 5 PO menunggu >3 hari!              │
│ 🚨 ALERT: 2 PO narkotika perlu Level 2 approval│
└─────────────────────────────────────────────────┘

┌──────────────┬──────────────┬──────────────────┐
│ Pending      │ Approved     │ Avg Time         │
│ 15 PO        │ 8 today      │ 1.5 hours ⚡     │
│ 5 urgent 🔴  │ 45 this week │ Target: <2h ✅   │
└──────────────┴──────────────┴──────────────────┘

┌─────────────────────────────────────────────────┐
│ APPROVAL QUEUE (Priority Sorted)               │
│ ┌─────────────────────────────────────────────┐│
│ │🔴 PO-123 | RS Harapan | Rp 60M | 5 days   ││
│ │🚨 PO-124 | Klinik X   | Rp 10M | NARKOTIKA││
│ │⚠️ PO-125 | RS Sentosa | Rp 55M | 4 days   ││
│ │   PO-126 | Klinik Y   | Rp 5M  | 2 days   ││
│ │   PO-127 | RS Sehat   | Rp 8M  | 1 day    ││
│ └─────────────────────────────────────────────┘│
│ [Approve All] [Review One by One]              │
└─────────────────────────────────────────────────┘

┌──────────────────────┬──────────────────────────┐
│ APPROVAL TREND       │ REJECTION REASONS        │
│ (Line Chart)         │ (Pie Chart)              │
│ 50 ┤     ╭───╮       │ Budget: 40%             │
│ 40 ┤   ╭─╯   ╰─╮     │ Compliance: 30%         │
│ 30 ┤ ╭─╯       ╰─    │ Duplicate: 20%          │
│ 20 ┤─╯               │ Other: 10%              │
└──────────────────────┴──────────────────────────┘
```

### Actionable Insights:
```
🔴 "5 PO urgent! Approve sekarang atau akan delay pengiriman"
🚨 "2 PO narkotika perlu Level 2 approval dari Anda"
✅ "Approval time Anda 1.5h, lebih cepat dari target 2h!"
```

---

## 💰 ROLE 3: Finance

### Primary Goal:
**"Berapa uang masuk? Berapa uang keluar? Apakah cashflow sehat?"**

### Essential Data (Priority Order):

#### 🔴 CRITICAL (Must See First - 3 seconds)
```
1. CASH FLOW STATUS (TODAY/THIS WEEK/THIS MONTH)
   - Kas Masuk: Rp 100 juta ↑
   - Kas Keluar: Rp 80 juta ↓
   - Saldo Netto: Rp 20 juta ✅
   
   Visual: Big numbers dengan trend
   ┌────────────────────────────────┐
   │ SALDO NETTO (Bulan Ini)        │
   │     Rp 20.000.000 ✅          │
   │                                │
   │ Masuk:  Rp 100M ↑ +15%        │
   │ Keluar: Rp 80M  ↓ -5%         │
   └────────────────────────────────┘
```

#### 🟡 IMPORTANT (Need to Know - 10 seconds)
```
2. OVERDUE INVOICES
   - Supplier Overdue: 5 invoice (Rp 25M) 🔴
   - Customer Overdue: 3 invoice (Rp 15M) ⚠️
   
3. PAYMENT DUE THIS WEEK
   - Harus Bayar: Rp 30 juta
   - Akan Terima: Rp 40 juta
   - Net: +Rp 10 juta ✅
   
4. PENDING PAYMENTS
   - Menunggu Konfirmasi: 8 payments
   - Total Amount: Rp 50 juta
```

#### 🟢 NICE TO KNOW (Context - 30 seconds)
```
5. COLLECTION RATE
   - On-time Payment: 85%
   - Avg Days to Pay: 25 days
   
6. MONTHLY SUMMARY
   - Total Transactions: 150
   - Total Value: Rp 500 juta
```

### Visual Layout:
```
┌─────────────────────────────────────────────────┐
│ 🔴 ALERT: 5 invoice overdue - Total Rp 25M!    │
└─────────────────────────────────────────────────┘

┌──────────────┬──────────────┬──────────────────┐
│ Kas Masuk    │ Kas Keluar   │ Saldo Netto      │
│ Rp 100M      │ Rp 80M       │ Rp 20M ✅        │
│ ↑ +15%       │ ↓ -5%        │ ↑ +25%           │
└──────────────┴──────────────┴──────────────────┘

┌─────────────────────────────────────────────────┐
│ CASH FLOW TREND (6 Months)                     │
│ 150M ┤                                    ╭─── │
│ 100M ┤              ╭────────────────────╯     │
│  50M ┤    ╭────────╯                           │
│   0M └────────────────────────────────────     │
│      Jan   Feb   Mar   Apr   May   Jun         │
│      ── Masuk  ── Keluar  ── Netto            │
└─────────────────────────────────────────────────┘

┌──────────────────────┬──────────────────────────┐
│ OVERDUE INVOICES     │ PAYMENT DUE THIS WEEK    │
│ (Table - Top 10)     │ (Table - Sorted by date) │
│ Supplier A: Rp 10M   │ Mon: Rp 5M              │
│ Supplier B: Rp 8M    │ Wed: Rp 10M             │
│ Supplier C: Rp 7M    │ Fri: Rp 15M             │
└──────────────────────┴──────────────────────────┘

┌──────────────────────┬──────────────────────────┐
│ COLLECTION RATE      │ QUICK ACTIONS            │
│ (Gauge Chart)        │ [Bayar Invoice]         │
│      85%             │ [Konfirmasi Payment]     │
│   ████████░░         │ [Export Report]          │
└──────────────────────┴──────────────────────────┘
```

### Actionable Insights:
```
🔴 "5 invoice overdue Rp 25M - Bayar sekarang untuk hindari denda!"
⚠️ "Minggu ini harus bayar Rp 30M, pastikan saldo cukup"
✅ "Cashflow positif Rp 20M, bisa investasi atau bayar hutang"
```

---

## 👨‍💼 ROLE 4: Super Admin

### Primary Goal:
**"Apakah sistem berjalan normal? Ada masalah yang perlu saya handle?"**

### Essential Data (Priority Order):

#### 🔴 CRITICAL (Must See First - 3 seconds)
```
1. SYSTEM HEALTH STATUS
   - System Status: HEALTHY ✅ / WARNING ⚠️ / ERROR 🔴
   - Active Errors: 0 errors
   - Failed Transactions: 2 (last 24h)
   - Pending Actions: 5 items
   
   Visual: Traffic light system
   ┌────────────────────────────────┐
   │ SYSTEM STATUS                  │
   │        ✅ HEALTHY              │
   │                                │
   │ Errors: 0                      │
   │ Failed: 2 (24h)                │
   │ Pending: 5 actions             │
   └────────────────────────────────┘
```

#### 🟡 IMPORTANT (Need to Know - 10 seconds)
```
2. BUSINESS ACTIVITY (TODAY/THIS WEEK/THIS MONTH)
   - PO Created: 125 PO (↑ +20%)
   - PO Value: Rp 500M (↑ +15%)
   - Active Users: 50 users
   - Active Orgs: 25 orgs
   
3. OPERATIONS STATUS
   - Pending Approval: 15 PO
   - In Progress: 30 PO
   - Completed: 80 PO
   
4. FINANCIAL OVERVIEW
   - Total AR: Rp 200M
   - Total AP: Rp 150M
   - Net Position: +Rp 50M ✅
```

#### 🟢 NICE TO KNOW (Context - 30 seconds)
```
5. TOP PERFORMERS
   - Top Product: Paracetamol (1000 units)
   - Top Supplier: PT Pharma (50 PO)
   - Top Customer: RS Harapan (Rp 100M)
   
6. ANALYTICS
   - Approval Rate: 95%
   - On-time Delivery: 92%
   - Collection Rate: 85%
```

### Visual Layout:
```
┌─────────────────────────────────────────────────┐
│ 🔴 ALERT: 2 failed transactions in last 24h    │
│ ⚠️ WARNING: 5 PO pending >3 days               │
└─────────────────────────────────────────────────┘

┌──────────────┬──────────────┬──────────────────┐
│ System Status│ PO Today     │ Active Users     │
│ ✅ HEALTHY   │ 125 PO       │ 50 users         │
│ 0 errors     │ ↑ +20%       │ 25 orgs          │
└──────────────┴──────────────┴──────────────────┘

┌─────────────────────────────────────────────────┐
│ BUSINESS ACTIVITY TREND (6 Months)             │
│ 150 ┤                                    ╭───  │
│ 100 ┤              ╭────────────────────╯      │
│  50 ┤    ╭────────╯                            │
│   0 └────────────────────────────────────      │
│     Jan   Feb   Mar   Apr   May   Jun          │
│     ── PO Count  ── PO Value  ── Users        │
└─────────────────────────────────────────────────┘

┌──────────────────────┬──────────────────────────┐
│ PO BY STATUS         │ FINANCIAL OVERVIEW       │
│ (Donut Chart)        │ (Bar Chart)              │
│   ╭───────╮          │ AR  ████████████         │
│ ╭─╯       ╰─╮        │ AP  ██████████           │
│ │  Draft   │         │ Net ██████               │
│ │ Approved │         │                          │
│ ╰─╮       ╭─╯        │                          │
│   ╰───────╯          │                          │
└──────────────────────┴──────────────────────────┘

┌──────────────────────┬──────────────────────────┐
│ TOP 5 PRODUCTS       │ TOP 5 SUPPLIERS          │
│ (Horizontal Bar)     │ (Horizontal Bar)         │
│ Paracetamol ████████ │ PT Pharma    ████████   │
│ Amoxicillin ██████   │ PT Medika    ██████     │
│ Vitamin C   ████     │ PT Supplier  ████       │
└──────────────────────┴──────────────────────────┘

┌─────────────────────────────────────────────────┐
│ RECENT SYSTEM ACTIVITY (Last 10)               │
│ • 10:30 - User A approved PO-123               │
│ • 10:15 - Payment received Rp 10M              │
│ • 09:45 - Invoice INV-456 issued               │
└─────────────────────────────────────────────────┘
```

### Actionable Insights:
```
🔴 "2 transaksi gagal - Cek error log sekarang!"
⚠️ "5 PO pending >3 days - Reminder ke approver"
✅ "Sistem sehat, semua metrik normal"
📈 "PO naik 20% bulan ini - Pertimbangkan scale up"
```

---

## 📊 Comparison Matrix

| Role | Primary Concern | Critical Data | Update Frequency | Chart Priority |
|------|----------------|---------------|------------------|----------------|
| **Healthcare** | Credit & Orders | Credit Status, PO Status | Real-time | Credit Trend |
| **Approver** | Approval Queue | Pending Count, Urgent Items | Real-time | Approval Trend |
| **Finance** | Cash Flow | Kas Masuk/Keluar, Overdue | Daily | Cash Flow Trend |
| **Super Admin** | System Health | Errors, Activity, Performance | Real-time | Activity Trend |

---

## 🎨 Visual Hierarchy Rules

### Level 1: ALERTS (Top of page)
```
🔴 Critical alerts (red)
⚠️ Warnings (yellow)
ℹ️ Info (blue)
```

### Level 2: KPI CARDS (Big numbers)
```
┌──────────────┐
│ METRIC NAME  │
│   BIG NUMBER │
│ ↑ +15% trend │
└──────────────┘
```

### Level 3: CHARTS (Visual trends)
```
Line Chart: Trends over time
Bar Chart: Comparisons
Donut Chart: Distributions
Gauge Chart: Percentages
```

### Level 4: TABLES (Detailed data)
```
Top 5-10 items only
Sorted by priority
Action buttons
```

### Level 5: QUICK ACTIONS (Bottom)
```
[Primary Action] [Secondary Action]
```

---

## 🚀 Implementation Priority

### Phase 1: CRITICAL (Week 1)
1. ✅ Alerts system (all roles)
2. ✅ KPI cards dengan trend (↑ ↓ %)
3. ✅ Period selector (Hari/Minggu/Bulan)

### Phase 2: IMPORTANT (Week 2)
4. ✅ Primary chart per role:
   - Healthcare: Credit Trend
   - Approver: Approval Queue
   - Finance: Cash Flow Trend
   - Super Admin: Activity Trend

### Phase 3: ENHANCEMENT (Week 3)
5. ✅ Secondary charts
6. ✅ Drill-down capability
7. ✅ Export functionality

---

## 💡 Key Takeaways

### For Healthcare:
**"Show me my credit status and let me order quickly"**
- Credit status = #1 priority
- PO status = #2 priority
- Payment due = #3 priority

### For Approver:
**"Show me what needs approval NOW"**
- Pending queue = #1 priority
- Urgent items = #2 priority
- Performance metrics = #3 priority

### For Finance:
**"Show me the money flow"**
- Cash flow = #1 priority
- Overdue invoices = #2 priority
- Payment schedule = #3 priority

### For Super Admin:
**"Show me if everything is OK"**
- System health = #1 priority
- Business activity = #2 priority
- Analytics = #3 priority

---

**Apakah breakdown ini sudah sesuai dengan kebutuhan? Ada yang perlu ditambah/dikurangi?**

Saya siap implementasikan dashboard yang optimal untuk setiap role! 😊
