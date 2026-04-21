# Payment Out Proof & Supplier Notification System - Requirements

**Feature**: Upload Bukti Bayar ke Supplier + Auto Notification  
**User Role**: Finance, Super Admin  
**Priority**: HIGH  
**Status**: Planning

---

## 🎯 Business Goals

### Primary Goals
1. **Streamline Payment Process**: Finance dapat upload bukti transfer ke supplier dengan mudah
2. **Automatic Notification**: Supplier otomatis menerima notifikasi via Email & WhatsApp
3. **Complete Documentation**: Supplier menerima semua dokumen terkait (PO, GR, Invoice, Bukti Bayar)
4. **Audit Trail**: Semua payment out tercatat dengan lengkap untuk audit

### Success Metrics
- Reduce manual notification time by 80%
- 100% payment documentation completeness
- Supplier satisfaction improvement
- Faster payment confirmation from suppliers

---

## 👥 User Stories

### US-1: Upload Bukti Bayar (Finance)
**As a** Finance staff  
**I want to** upload bukti transfer pembayaran ke supplier  
**So that** supplier dapat menerima konfirmasi pembayaran dengan bukti yang valid

**Acceptance Criteria**:
- Finance dapat upload file bukti transfer (PDF, JPG, PNG)
- Sistem menyimpan metadata: tanggal bayar, jumlah, metode, nomor referensi
- Sistem validasi: jumlah bayar tidak boleh melebihi sisa tagihan
- Support partial payment (bayar sebagian)
- File tersimpan dengan aman dan dapat diakses kembali

### US-2: Auto Notification ke Supplier (System)
**As a** System  
**I want to** automatically send payment notification to supplier  
**So that** supplier langsung tahu pembayaran sudah dilakukan

**Acceptance Criteria**:
- Email otomatis terkirim ke supplier email (dari database supplier)
- WhatsApp notification terkirim ke supplier phone (dari database supplier)
- Notifikasi berisi: ringkasan pembayaran, link download dokumen
- Attachment: Bukti bayar, PO, GR, Supplier Invoice
- Notifikasi dapat di-resend jika gagal

### US-3: View Payment History (Finance & Supplier)
**As a** Finance staff or Supplier  
**I want to** view payment history and documents  
**So that** saya dapat tracking pembayaran dan download dokumen kapan saja

**Acceptance Criteria**:
- Finance dapat lihat semua payment out history
- Supplier dapat lihat payment history untuk invoice mereka (via portal)
- Download semua dokumen terkait (PO, GR, Invoice, Bukti Bayar)
- Filter by: supplier, date range, status, amount

### US-4: Payment Status Tracking (Finance)
**As a** Finance staff  
**I want to** track payment status (pending, sent, confirmed)  
**So that** saya tahu mana yang sudah/belum dikonfirmasi supplier

**Acceptance Criteria**:
- Status: Pending → Sent → Confirmed by Supplier
- Supplier dapat konfirmasi penerimaan pembayaran
- Dashboard menampilkan payment status summary
- Alert untuk payment yang belum dikonfirmasi > 7 hari

---

## 📋 Functional Requirements

### FR-1: Payment Out Management
**Priority**: CRITICAL

#### FR-1.1: Upload Bukti Bayar
- Finance dapat upload bukti transfer untuk Supplier Invoice
- Support multiple file formats: PDF, JPG, PNG (max 5MB per file)
- Support multiple files (jika ada beberapa bukti transfer)
- Mandatory fields:
  - Tanggal pembayaran
  - Jumlah yang dibayar
  - Metode pembayaran (Transfer Bank, Giro, Cash, dll)
  - Nomor referensi/bukti transfer
  - Bank pengirim (dari Bank Account Medikindo)
  - Bank penerima (dari Supplier data)
  - Catatan (optional)

#### FR-1.2: Payment Validation
- Validasi jumlah bayar ≤ sisa tagihan (outstanding amount)
- Validasi tanggal bayar tidak boleh di masa depan
- Validasi file format dan size
- Prevent duplicate payment untuk invoice yang sama
- Update Supplier Invoice status: draft → verified → **paid** (atau partial_paid)

#### FR-1.3: Payment Allocation
- Jika partial payment: track remaining amount
- Support multiple payments untuk 1 invoice
- Auto-calculate outstanding balance
- Link payment to specific Supplier Invoice

### FR-2: Notification System
**Priority**: CRITICAL

#### FR-2.1: Email Notification
- Auto-send email ke supplier.email (dari database)
- Email template professional dengan branding Medikindo
- Email content:
  - Subject: "Konfirmasi Pembayaran Invoice #[invoice_number]"
  - Greeting dengan nama supplier
  - Ringkasan pembayaran (tanggal, jumlah, metode, referensi)
  - Tabel detail: PO number, GR number, Invoice number, Amount
  - Link download dokumen (secure link dengan token)
  - Footer: kontak Medikindo finance
- Attachments:
  - Bukti bayar (PDF/image)
  - PO PDF
  - GR PDF
  - Supplier Invoice PDF

#### FR-2.2: WhatsApp Notification
- Auto-send WhatsApp message ke supplier.phone (dari database)
- WhatsApp message content:
  ```
  Halo [Supplier Name],
  
  Pembayaran untuk Invoice #[invoice_number] telah kami lakukan:
  
  📅 Tanggal: [payment_date]
  💰 Jumlah: Rp [amount]
  🏦 Metode: [payment_method]
  📝 Referensi: [reference_number]
  
  Detail lengkap dan dokumen dapat diakses di:
  [secure_link]
  
  Terima kasih atas kerjasamanya.
  
  Medikindo Finance Team
  ```
- Integration dengan WhatsApp Business API atau Fonnte/Wablas

#### FR-2.3: Notification Tracking
- Track notification status: sent, delivered, failed
- Retry mechanism untuk failed notifications
- Manual resend option untuk Finance
- Notification log untuk audit

### FR-3: Document Management
**Priority**: HIGH

#### FR-3.1: Document Generation
- Auto-generate payment receipt PDF
- Payment receipt berisi:
  - Medikindo letterhead
  - Payment details (date, amount, method, reference)
  - Supplier details
  - Invoice details
  - PO & GR reference
  - Authorized signature (digital)

#### FR-3.2: Document Storage
- Secure file storage untuk bukti bayar
- Organized folder structure: `/storage/payment_proofs/[year]/[month]/[supplier_id]/`
- File naming convention: `payment_[invoice_number]_[timestamp].[ext]`
- Backup mechanism

#### FR-3.3: Document Access
- Secure download link dengan token expiry (24 hours)
- Finance dapat download semua dokumen
- Supplier dapat download dokumen mereka (via supplier portal - future)
- Audit log untuk document access

### FR-4: Payment Dashboard
**Priority**: MEDIUM

#### FR-4.1: Payment Out Dashboard
- Summary cards:
  - Total Payment Out (bulan ini)
  - Pending Payments (belum upload bukti)
  - Sent Notifications (sudah kirim notif)
  - Confirmed Payments (sudah dikonfirmasi supplier)
- Chart: Payment trend (monthly)
- Table: Recent payments dengan status

#### FR-4.2: Payment List & Filter
- List semua payment out dengan pagination
- Filter by:
  - Supplier
  - Date range
  - Status (pending, sent, confirmed)
  - Amount range
  - Payment method
- Search by: invoice number, PO number, reference number
- Export to Excel

#### FR-4.3: Payment Detail View
- View payment details
- View all related documents
- View notification history
- View supplier confirmation status
- Action buttons: Resend notification, Download documents, Edit payment

---

## 🔒 Non-Functional Requirements

### NFR-1: Security
- File upload validation (type, size, malware scan)
- Secure file storage dengan encryption
- Access control: only Finance & Super Admin
- Secure download links dengan token
- Audit log untuk semua payment actions

### NFR-2: Performance
- File upload max 5MB per file
- Email sending: async queue (tidak block UI)
- WhatsApp sending: async queue
- Document generation: < 3 seconds
- Page load: < 2 seconds

### NFR-3: Reliability
- Email retry: 3 attempts dengan exponential backoff
- WhatsApp retry: 3 attempts
- File backup: daily backup ke cloud storage
- Transaction rollback jika notification gagal (optional)

### NFR-4: Usability
- Simple upload form dengan drag & drop
- Preview bukti bayar sebelum upload
- Clear error messages
- Progress indicator untuk upload & sending
- Mobile-responsive UI

### NFR-5: Scalability
- Support 1000+ payments per month
- Support 100+ suppliers
- Queue system untuk notification (Laravel Queue)
- CDN untuk file serving (future)

---

## 🗄️ Database Requirements

### New Tables

#### 1. `payment_out_proofs`
```sql
- id (PK)
- supplier_invoice_id (FK to supplier_invoices)
- payment_date (date)
- amount (decimal 15,2)
- payment_method (enum: bank_transfer, giro, cash, other)
- reference_number (string)
- bank_account_id (FK to bank_accounts) - Medikindo bank
- notes (text, nullable)
- status (enum: pending, sent, confirmed)
- confirmed_at (timestamp, nullable)
- confirmed_by (FK to users, nullable)
- uploaded_by (FK to users)
- created_at, updated_at
```

#### 2. `payment_out_documents`
```sql
- id (PK)
- payment_out_proof_id (FK)
- document_type (enum: payment_proof, receipt, other)
- file_name (string)
- file_path (string)
- file_size (integer)
- mime_type (string)
- uploaded_by (FK to users)
- created_at, updated_at
```

#### 3. `payment_notifications`
```sql
- id (PK)
- payment_out_proof_id (FK)
- notification_type (enum: email, whatsapp)
- recipient (string) - email or phone
- status (enum: pending, sent, delivered, failed)
- sent_at (timestamp, nullable)
- delivered_at (timestamp, nullable)
- error_message (text, nullable)
- retry_count (integer, default 0)
- created_at, updated_at
```

### Modified Tables

#### `suppliers` - Add fields
```sql
- email (string, nullable) - untuk email notification
- phone (string, nullable) - untuk WhatsApp notification
- contact_person (string, nullable)
- notification_preference (enum: email, whatsapp, both, default: both)
```

#### `supplier_invoices` - Add fields
```sql
- paid_amount (decimal 15,2, default 0) - total yang sudah dibayar
- outstanding_amount (decimal 15,2) - sisa yang belum dibayar
- payment_status (enum: unpaid, partial_paid, paid, default: unpaid)
- last_payment_date (date, nullable)
```

---

## 🔄 Integration Requirements

### INT-1: Email Service
- Use Laravel Mail with queue
- SMTP configuration (Gmail, SendGrid, or Mailgun)
- Email template dengan Blade
- Attachment support

### INT-2: WhatsApp Service
- Integration options:
  - **Option A**: Fonnte API (recommended for Indonesia)
  - **Option B**: Wablas API
  - **Option C**: WhatsApp Business API (official, more complex)
- API credentials configuration
- Message template approval (for official API)
- Webhook untuk delivery status

### INT-3: File Storage
- Laravel Storage (local for development)
- AWS S3 or Google Cloud Storage (production)
- Backup strategy

---

## 📱 UI/UX Requirements

### UI-1: Payment Out Upload Page
**Location**: `/payments/out/create?invoice={supplier_invoice_id}`

**Layout**:
```
┌─────────────────────────────────────────────┐
│ Upload Bukti Bayar ke Supplier              │
├─────────────────────────────────────────────┤
│ Invoice Details:                            │
│ - Invoice Number: SI-20260421-001           │
│ - Supplier: PT Supplier ABC                 │
│ - Total Amount: Rp 10.000.000               │
│ - Paid Amount: Rp 0                         │
│ - Outstanding: Rp 10.000.000                │
├─────────────────────────────────────────────┤
│ Payment Information:                        │
│ [Tanggal Pembayaran] [Date Picker]          │
│ [Jumlah Dibayar] [Rp ________]              │
│ [Metode Pembayaran] [Dropdown]              │
│ [Nomor Referensi] [________]                │
│ [Bank Pengirim] [Dropdown - Medikindo]      │
│ [Bank Penerima] [Auto from Supplier]        │
│ [Catatan] [Textarea]                        │
├─────────────────────────────────────────────┤
│ Upload Bukti Transfer:                      │
│ [Drag & Drop Area or Click to Upload]       │
│ - Supported: PDF, JPG, PNG (max 5MB)        │
│ - Multiple files allowed                    │
├─────────────────────────────────────────────┤
│ Notification Settings:                      │
│ ☑ Send Email to supplier@email.com          │
│ ☑ Send WhatsApp to +62812345678             │
├─────────────────────────────────────────────┤
│ [Cancel] [Preview] [Upload & Send]          │
└─────────────────────────────────────────────┘
```

### UI-2: Payment Out List Page
**Location**: `/payments/out`

**Features**:
- Table dengan columns: Date, Invoice, Supplier, Amount, Method, Status, Actions
- Filter sidebar: Supplier, Date Range, Status, Payment Method
- Search bar
- Export button
- Bulk actions (future)

### UI-3: Payment Out Detail Page
**Location**: `/payments/out/{id}`

**Sections**:
- Payment summary card
- Related documents (PO, GR, Invoice, Bukti Bayar)
- Notification history
- Supplier confirmation status
- Action buttons: Resend, Download, Edit

---

## 🧪 Testing Requirements

### Test Cases

#### TC-1: Upload Bukti Bayar
- ✅ Upload valid file (PDF, JPG, PNG)
- ❌ Upload invalid file (EXE, ZIP)
- ❌ Upload file > 5MB
- ✅ Upload multiple files
- ✅ Partial payment (amount < outstanding)
- ❌ Overpayment (amount > outstanding)
- ✅ Full payment (amount = outstanding)

#### TC-2: Email Notification
- ✅ Email sent successfully
- ✅ Email with attachments
- ❌ Email failed (invalid email)
- ✅ Email retry mechanism
- ✅ Email delivery tracking

#### TC-3: WhatsApp Notification
- ✅ WhatsApp sent successfully
- ❌ WhatsApp failed (invalid phone)
- ✅ WhatsApp retry mechanism
- ✅ WhatsApp delivery tracking

#### TC-4: Payment Status Update
- ✅ Supplier Invoice status updated to paid
- ✅ Outstanding amount calculated correctly
- ✅ Payment history recorded
- ✅ Audit log created

---

## 📅 Implementation Phases

### Phase 1: Core Payment Upload (Week 1-2)
- Database migration
- Payment upload form
- File storage
- Payment validation
- Status update

### Phase 2: Email Notification (Week 2-3)
- Email template design
- Email sending with queue
- Attachment handling
- Retry mechanism
- Notification tracking

### Phase 3: WhatsApp Notification (Week 3-4)
- WhatsApp API integration
- Message template
- Sending with queue
- Retry mechanism
- Delivery tracking

### Phase 4: Dashboard & Reporting (Week 4-5)
- Payment dashboard
- Payment list & filter
- Payment detail view
- Export functionality
- Analytics

### Phase 5: Testing & Refinement (Week 5-6)
- Unit testing
- Integration testing
- UAT with Finance team
- Bug fixes
- Documentation

---

## 🎯 Success Criteria

### Must Have (MVP)
- ✅ Finance dapat upload bukti bayar
- ✅ Email notification terkirim otomatis
- ✅ WhatsApp notification terkirim otomatis
- ✅ Supplier menerima semua dokumen (PO, GR, Invoice, Bukti Bayar)
- ✅ Payment status tracking
- ✅ Audit trail lengkap

### Should Have
- ✅ Payment dashboard
- ✅ Payment history & filter
- ✅ Resend notification
- ✅ Document download
- ✅ Mobile responsive

### Nice to Have (Future)
- ⏳ Supplier portal (supplier login & confirm payment)
- ⏳ Bulk payment upload
- ⏳ Payment reminder (auto remind supplier)
- ⏳ Payment analytics & reporting
- ⏳ Integration dengan accounting software

---

## 📝 Notes

### Technical Considerations
1. **Queue System**: Use Laravel Queue untuk email & WhatsApp (prevent blocking)
2. **File Security**: Encrypt sensitive files, secure download links
3. **API Rate Limiting**: Handle WhatsApp API rate limits
4. **Error Handling**: Graceful degradation jika notification gagal
5. **Scalability**: Design untuk handle 1000+ payments/month

### Business Considerations
1. **Supplier Data**: Pastikan supplier email & phone sudah lengkap
2. **Notification Cost**: WhatsApp API ada biaya per message
3. **Compliance**: Pastikan sesuai regulasi data privacy
4. **Training**: Finance team perlu training untuk fitur baru

---

**Requirements Approved By**: [Pending]  
**Technical Review By**: [Pending]  
**Estimated Effort**: 6 weeks (1 developer)  
**Priority**: HIGH  
**Target Release**: Q2 2026
