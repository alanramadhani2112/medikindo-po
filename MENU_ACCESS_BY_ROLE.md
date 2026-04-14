# MENU ACCESS BY ROLE

**System**: Medikindo PO Management v2.0  
**Last Updated**: April 14, 2026

---

## 📋 MENU STRUCTURE

### 1. DASHBOARD
- **Icon**: 📊 Element Grid
- **Route**: `/dashboard`
- **Permission**: None (all authenticated users)

---

### 2. PROCUREMENT SECTION

#### 2.1 Purchase Orders
- **Icon**: 🛒 Purchase
- **Route**: `/po`
- **Permission**: `view_purchase_orders`
- **Features**:
  - View all PO
  - Create new PO (if has `create_po`)
  - Edit draft PO (if has `update_po`)
  - Submit PO (if has `submit_po`)
  - View PO details

#### 2.2 Approvals
- **Icon**: ✅ Check Square
- **Route**: `/approvals`
- **Permission**: `view_approvals`
- **Features**:
  - View pending approvals
  - Approve/Reject PO (if has `approve_purchase_orders`)
  - Badge showing pending count

#### 2.3 Goods Receipt
- **Icon**: 📦 Package
- **Route**: `/goods-receipts`
- **Permission**: `view_goods_receipt`
- **Features**:
  - View all GR
  - Create GR from approved PO (if has `confirm_receipt`)
  - View GR details
  - See batch & expiry data

---

### 3. FINANCE SECTION

#### 3.1 Hutang Pemasok (Supplier Invoice / AP)
- **Icon**: ⬇️ Arrow Down (Red)
- **Route**: `/invoices?tab=supplier`
- **Permission**: `view_invoices`
- **Features**:
  - View supplier invoices (AP)
  - Create invoice from GR (if has `create_invoices`)
  - View invoice details
  - Export PDF

#### 3.2 Tagihan ke RS/Klinik (Customer Invoice / AR)
- **Icon**: ⬆️ Arrow Up (Green)
- **Route**: `/invoices?tab=customer`
- **Permission**: `view_invoices`
- **Features**:
  - View customer invoices (AR)
  - Create invoice from GR (if has `create_invoices`)
  - View invoice details
  - Export PDF

#### 3.3 Payments
- **Icon**: 💰 Wallet
- **Route**: `/payments`
- **Permission**: `view_payments`
- **Features**:
  - View all payments (IN & OUT)
  - Process payment IN (if has `confirm_payment`)
  - Process payment OUT (if has `process_payments`)
  - View payment history

#### 3.4 Credit Control
- **Icon**: 📈 Chart Simple
- **Route**: `/financial-controls`
- **Permission**: `view_credit_control`
- **Features**:
  - View credit limits per organization
  - View credit usage
  - Monitor outstanding balances

---

### 4. MASTER DATA SECTION

#### 4.1 Organizations
- **Icon**: 🏦 Bank
- **Route**: `/organizations`
- **Permission**: `manage_organizations`
- **Features**:
  - View all organizations (RS/Klinik)
  - Create new organization
  - Edit organization
  - Set credit limits

#### 4.2 Suppliers
- **Icon**: 🚚 Delivery
- **Route**: `/suppliers`
- **Permission**: `manage_suppliers`
- **Features**:
  - View all suppliers
  - Create new supplier
  - Edit supplier
  - Manage supplier products

#### 4.3 Products
- **Icon**: 💊 Capsule
- **Route**: `/products`
- **Permission**: `manage_products`
- **Features**:
  - View all products
  - Create new product
  - Edit product
  - Set pricing & margins

#### 4.4 Users
- **Icon**: 👤 Profile User
- **Route**: `/users`
- **Permission**: `manage_users`
- **Features**:
  - View all users
  - Create new user
  - Edit user
  - Assign roles

---

## 👥 MENU ACCESS BY ROLE

### 🔴 SUPER ADMIN
**Access**: ALL MENUS

```
✅ Dashboard
✅ PROCUREMENT
   ✅ Purchase Orders (full access)
   ✅ Approvals (full access)
   ✅ Goods Receipt (full access)
✅ FINANCE
   ✅ Hutang Pemasok (full access)
   ✅ Tagihan ke RS/Klinik (full access)
   ✅ Payments (full access)
   ✅ Credit Control (full access)
✅ MASTER DATA
   ✅ Organizations (full access)
   ✅ Suppliers (full access)
   ✅ Products (full access)
   ✅ Users (full access)
```

**Capabilities**:
- Create, edit, delete anything
- Override all permissions
- Access all organizations
- Manage system configuration

---

### 🟠 ADMIN PUSAT (Central Admin)
**Access**: OPERATIONAL MENUS (No Master Data)

```
✅ Dashboard
✅ PROCUREMENT
   ✅ Purchase Orders
      - Create PO
      - Edit draft PO
      - Submit PO
      - View all PO
   ✅ Approvals
      - View pending approvals
      - Approve/Reject PO
   ✅ Goods Receipt
      - Create GR from approved PO
      - View all GR
✅ FINANCE
   ✅ Hutang Pemasok
      - Create supplier invoice from GR
      - View all supplier invoices
   ✅ Tagihan ke RS/Klinik
      - Create customer invoice from GR
      - View all customer invoices
   ✅ Payments
      - Process payment IN
      - Process payment OUT (with validation)
      - View all payments
   ✅ Credit Control
      - View credit limits
      - Monitor usage
❌ MASTER DATA (No Access)
```

**Use Case**: Medikindo operational staff yang handle daily operations

---

### 🟢 HEALTHCARE USER (RS/Klinik Staff)
**Access**: LIMITED OPERATIONAL

```
✅ Dashboard
✅ PROCUREMENT
   ✅ Purchase Orders
      - Create PO for their organization
      - Edit draft PO
      - Submit PO
      - View their PO only
   ❌ Approvals (No Access)
   ✅ Goods Receipt
      - Create GR when goods arrive
      - View their GR only
✅ FINANCE (Limited)
   ✅ Hutang Pemasok (View Only)
      - View supplier invoices related to their PO
   ✅ Tagihan ke RS/Klinik (View Only)
      - View invoices addressed to them
      - Confirm payment
   ❌ Payments (No Direct Access)
      - Can only confirm their own payments
   ❌ Credit Control (No Access)
❌ MASTER DATA (No Access)
```

**Use Case**: Hospital/Clinic staff yang order barang dan terima barang

---

### 🔵 APPROVER
**Access**: APPROVAL ONLY

```
✅ Dashboard
✅ PROCUREMENT
   ✅ Purchase Orders (View Only)
      - View PO details for approval context
   ✅ Approvals
      - View pending approvals
      - Approve PO (if not self-created)
      - Reject PO with notes
   ❌ Goods Receipt (No Access)
❌ FINANCE (No Access)
❌ MASTER DATA (No Access)
```

**Use Case**: Medikindo manager yang approve PO

---

### 🟡 FINANCE
**Access**: FINANCE OPERATIONS

```
✅ Dashboard
✅ PROCUREMENT (View Only for Context)
   ✅ Purchase Orders (View Only)
      - View PO details for invoice context
   ❌ Approvals (No Access)
   ✅ Goods Receipt (View Only)
      - View GR to create invoice
✅ FINANCE
   ✅ Hutang Pemasok
      - Create supplier invoice from GR
      - View all supplier invoices
      - Approve discrepancies
   ✅ Tagihan ke RS/Klinik
      - Create customer invoice from GR
      - View all customer invoices
      - Approve discrepancies
   ✅ Payments
      - Process payment IN from RS/Klinik
      - Process payment OUT to supplier (with validation)
      - Verify payments
      - View all payment history
   ✅ Credit Control
      - View credit limits
      - Monitor credit usage
      - View outstanding balances
❌ MASTER DATA (No Access)
```

**Use Case**: Medikindo finance staff yang handle invoicing & payments

---

## 📊 MENU ACCESS MATRIX

| Menu | Super Admin | Admin Pusat | Healthcare User | Approver | Finance |
|------|-------------|-------------|-----------------|----------|---------|
| **Dashboard** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **PROCUREMENT** |
| Purchase Orders | ✅ Full | ✅ Full | ✅ Limited | ✅ View | ✅ View |
| Approvals | ✅ Full | ✅ Full | ❌ | ✅ Full | ❌ |
| Goods Receipt | ✅ Full | ✅ Full | ✅ Limited | ❌ | ✅ View |
| **FINANCE** |
| Hutang Pemasok | ✅ Full | ✅ Full | ✅ View | ❌ | ✅ Full |
| Tagihan RS/Klinik | ✅ Full | ✅ Full | ✅ View | ❌ | ✅ Full |
| Payments | ✅ Full | ✅ Full | ✅ Confirm | ❌ | ✅ Full |
| Credit Control | ✅ Full | ✅ Full | ❌ | ❌ | ✅ View |
| **MASTER DATA** |
| Organizations | ✅ Full | ❌ | ❌ | ❌ | ❌ |
| Suppliers | ✅ Full | ❌ | ❌ | ❌ | ❌ |
| Products | ✅ Full | ❌ | ❌ | ❌ | ❌ |
| Users | ✅ Full | ❌ | ❌ | ❌ | ❌ |

**Legend**:
- ✅ Full = Create, Read, Update, Delete
- ✅ Limited = Create & Read (own organization only)
- ✅ View = Read only
- ❌ = No access (menu hidden)

---

## 🔐 ACCESS CONTROL IMPLEMENTATION

### Sidebar Menu
- **File**: `resources/views/components/partials/sidebar.blade.php`
- **Method**: `@can()` and `@canany()` directives
- **Behavior**: Menu items hidden if user lacks permission

### Route Protection
- **File**: `routes/web.php`
- **Method**: `->middleware('can:permission_name')`
- **Behavior**: 403 Forbidden if accessed without permission

### Controller Authorization
- **Method**: `$this->authorize('permission', $model)`
- **Behavior**: Exception thrown if unauthorized

---

## 🎯 TYPICAL USER JOURNEYS

### Healthcare User Journey
```
1. Login → Dashboard
2. Create PO → Purchase Orders menu
3. Submit PO → Wait for approval
4. Receive goods → Goods Receipt menu (create GR)
5. View invoice → Tagihan ke RS/Klinik menu
6. Confirm payment → Payment confirmation
```

### Approver Journey
```
1. Login → Dashboard (see pending approvals badge)
2. View approvals → Approvals menu
3. Review PO details → Click PO link
4. Approve/Reject → Decision with notes
```

### Finance Journey
```
1. Login → Dashboard
2. Check completed GR → Goods Receipt menu (view only)
3. Create supplier invoice → Hutang Pemasok menu
4. Create customer invoice → Tagihan ke RS/Klinik menu
5. Receive payment from RS → Payments menu (IN)
6. Pay supplier → Payments menu (OUT, validated)
7. Monitor credit → Credit Control menu
```

### Admin Pusat Journey
```
1. Login → Dashboard
2. Full operational access to all procurement & finance
3. Can do everything except manage master data
4. Handle escalations and complex cases
```

### Super Admin Journey
```
1. Login → Dashboard
2. Full system access
3. Manage master data (organizations, suppliers, products, users)
4. Override any permission
5. System configuration
```

---

## 📱 RESPONSIVE BEHAVIOR

### Desktop (> 1024px)
- Sidebar always visible
- Full menu labels shown
- Icons + text

### Tablet (768px - 1024px)
- Sidebar collapsible
- Drawer overlay
- Icons + text

### Mobile (< 768px)
- Sidebar hidden by default
- Toggle button in header
- Drawer overlay
- Icons + text

---

## 🔔 NOTIFICATIONS & BADGES

### Approval Badge
- **Location**: Approvals menu item
- **Condition**: `$pendingApprovalCount > 0`
- **Display**: Red circle badge with count
- **Visible to**: Approver, Admin Pusat, Super Admin

---

## 💡 TIPS FOR USERS

### Healthcare User
- Fokus pada menu **Procurement** untuk order barang
- Gunakan **Goods Receipt** saat barang tiba
- Cek **Tagihan ke RS/Klinik** untuk invoice yang harus dibayar

### Approver
- Cek badge di menu **Approvals** untuk pending items
- Klik PO number untuk lihat detail sebelum approve
- Tidak bisa approve PO yang dibuat sendiri

### Finance
- Pastikan GR sudah **completed** sebelum buat invoice
- Cek **Goods Receipt** menu untuk lihat GR yang siap di-invoice
- Payment OUT hanya bisa jika Payment IN sudah cukup

### Admin Pusat
- Akses penuh ke semua operasional
- Tidak bisa manage master data (minta Super Admin)
- Bisa handle semua proses dari PO sampai Payment

### Super Admin
- Satu-satunya yang bisa manage **Master Data**
- Setup organizations, suppliers, products, users
- Override semua permission jika diperlukan

---

## 🚨 COMMON ISSUES & SOLUTIONS

### "Menu tidak muncul"
- **Cause**: User tidak punya permission
- **Solution**: Minta Super Admin assign role yang sesuai

### "403 Forbidden saat akses halaman"
- **Cause**: Route protected, user tidak punya permission
- **Solution**: Cek role assignment, minta permission yang sesuai

### "Tidak bisa approve PO"
- **Cause**: Self-approval prevention atau tidak punya permission
- **Solution**: Minta user lain yang punya role Approver

### "Tidak bisa create invoice"
- **Cause**: GR belum completed atau tidak punya permission
- **Solution**: Pastikan GR status = completed, cek role Finance

### "Tidak bisa bayar supplier"
- **Cause**: Payment IN dari RS belum cukup
- **Solution**: Tunggu RS bayar dulu, atau minta partial payment

---

## 📝 NOTES

1. **Menu visibility** controlled by permissions (automatic)
2. **Route access** protected by middleware (403 if unauthorized)
3. **Button visibility** in pages also controlled by permissions
4. **Organization scope** applied automatically (except Super Admin)
5. **Self-approval** blocked at service level (not just UI)

---

**Last Updated**: April 14, 2026  
**Maintained By**: System Administrator  
**Review Schedule**: When roles/permissions change

