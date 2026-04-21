# 💰 Payment Out Proof & Supplier Notification System - Proposal

**Tanggal**: 21 April 2026  
**Status**: Proposal  
**Priority**: HIGH  
**Estimated Effort**: 6 weeks

---

## 🎯 Executive Summary

### Problem Statement
Saat ini, setelah Customer Invoice (AR) dibuat dan menunggu pembayaran dari RS, Finance Medikindo perlu:
1. Upload bukti transfer pembayaran ke Supplier secara manual
2. Mengirim notifikasi ke Supplier secara manual (email/WhatsApp)
3. Melampirkan dokumen (PO, GR, Invoice, Bukti Bayar) secara manual
4. Tracking status pembayaran secara manual

Proses ini **memakan waktu** dan **rawan human error**.

### Proposed Solution
Sistem terintegrasi untuk:
- ✅ Upload bukti bayar ke Supplier dengan mudah
- ✅ Auto-send Email & WhatsApp ke Supplier
- ✅ Auto-attach semua dokumen terkait (PO, GR, Invoice, Bukti Bayar)
- ✅ Tracking status pembayaran (pending, sent, confirmed)
- ✅ Dashboard & reporting untuk Finance

### Business Impact
- ⏱️ **Reduce manual work by 80%** (dari 15 menit → 3 menit per payment)
- 📧 **100% notification delivery** (tidak ada yang terlewat)
- 📄 **Complete documentation** (semua dokumen otomatis terlampir)
- 😊 **Supplier satisfaction** (notifikasi cepat & lengkap)
- 🔍 **Better audit trail** (semua tercatat di sistem)

---

## 📊 Feature Overview

### 1. Payment Upload Module
**Location**: Menu baru "Payment Out" di sidebar

**Features**:
- Upload bukti transfer (PDF, JPG, PNG)
- Input payment details (tanggal, jumlah, metode, referensi)
- Support partial payment
- Validation: amount ≤ outstanding
- Auto-update Supplier Invoice status

**User Flow**:
```
Finance → Payment Out → Create New Payment
  → Select Supplier Invoice
  → Upload bukti transfer
  → Fill payment details
  → Click "Upload & Send"
  → System auto-send Email & WhatsApp
  → Done!
```

### 2. Auto Notification Module

#### Email Notification
- **To**: supplier.email (dari database)
- **Subject**: "Konfirmasi Pembayaran Invoice #[invoice_number]"
- **Content**: 
  - Greeting dengan nama supplier
  - Ringkasan pembayaran (tanggal, jumlah, metode, referensi)
  - Tabel detail (PO, GR, Invoice)
  - Link download dokumen
- **Attachments**:
  - Bukti bayar (uploaded file)
  - PO PDF
  - GR PDF
  - Supplier Invoice PDF

#### WhatsApp Notification
- **To**: supplier.whatsapp_number (dari database)
- **Message**:
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

### 3. Payment Dashboard

**Metrics**:
- Total Payment Out (bulan ini)
- Pending Payments (belum upload bukti)
- Sent Notifications (sudah kirim notif)
- Confirmed Payments (sudah dikonfirmasi supplier)

**Features**:
- Payment list dengan filter & search
- Payment detail view
- Resend notification
- Download documents
- Export to Excel

---

## 🗄️ Database Changes

### New Tables (3 tables)
1. **payment_out_proofs** - Main payment records
2. **payment_out_documents** - Uploaded files
3. **payment_notifications** - Notification tracking

### Modified Tables (2 tables)
1. **suppliers** - Add: email, whatsapp_number, contact_person, notification_preference
2. **supplier_invoices** - Add: paid_amount, outstanding_amount, payment_status, last_payment_date

**Total**: 5 tables (3 new, 2 modified)

---

## 🔧 Technical Stack

### Backend
- **Framework**: Laravel 13.4.0
- **Queue**: Laravel Queue (Redis recommended)
- **Storage**: Laravel Storage (S3 for production)
- **Email**: Laravel Mail (SMTP/API)
- **WhatsApp**: Fonnte API or Wablas API

### Frontend
- **Template**: Blade
- **JS Framework**: Alpine.js
- **CSS**: Tailwind CSS
- **File Upload**: Dropzone.js or FilePond

### External Services
- **Email Service**: Gmail SMTP / SendGrid / Mailgun
- **WhatsApp Service**: Fonnte (recommended) / Wablas
- **File Storage**: AWS S3 / Google Cloud Storage (production)

---

## 💰 Cost Estimation

### Development Cost
- **Developer**: 1 Full-stack Developer
- **Duration**: 6 weeks
- **Effort**: ~240 hours
- **Rate**: [Your rate]
- **Total**: [Calculate based on rate]

### Operational Cost (Monthly)
- **Email Service**: 
  - Gmail SMTP: FREE (up to 500 emails/day)
  - SendGrid: $15/month (40,000 emails)
  - Mailgun: $35/month (50,000 emails)
  
- **WhatsApp Service**:
  - Fonnte: Rp 150/message (prepaid)
  - Wablas: Rp 200/message (prepaid)
  - Estimated: 100 messages/month = Rp 15,000 - 20,000
  
- **File Storage**:
  - AWS S3: ~$5/month (100GB storage + transfer)
  - Google Cloud Storage: ~$5/month
  
- **Total Monthly**: ~$25 - $60 (Rp 400,000 - 950,000)

---

## 📅 Implementation Timeline

### Phase 1: Core Payment Upload (Week 1-2)
**Deliverables**:
- Database migration
- Payment upload form
- File storage
- Payment validation
- Status update

**Tasks**:
- [ ] Create database migrations
- [ ] Create models (PaymentOutProof, PaymentOutDocument, PaymentNotification)
- [ ] Create PaymentOutService
- [ ] Create upload form UI
- [ ] Implement file upload & validation
- [ ] Update Supplier Invoice status
- [ ] Testing

### Phase 2: Email Notification (Week 2-3)
**Deliverables**:
- Email template
- Email sending with queue
- Attachment handling
- Retry mechanism
- Notification tracking

**Tasks**:
- [ ] Design email template (Blade)
- [ ] Create SendPaymentNotificationEmail job
- [ ] Implement email sending with attachments
- [ ] Implement retry mechanism
- [ ] Create notification tracking
- [ ] Testing

### Phase 3: WhatsApp Notification (Week 3-4)
**Deliverables**:
- WhatsApp API integration
- Message template
- Sending with queue
- Retry mechanism
- Delivery tracking

**Tasks**:
- [ ] Setup Fonnte/Wablas account
- [ ] Create WhatsAppService
- [ ] Create SendPaymentNotificationWhatsApp job
- [ ] Implement message sending
- [ ] Implement retry mechanism
- [ ] Testing

### Phase 4: Dashboard & Reporting (Week 4-5)
**Deliverables**:
- Payment dashboard
- Payment list & filter
- Payment detail view
- Export functionality
- Analytics

**Tasks**:
- [ ] Create dashboard UI
- [ ] Create payment list page
- [ ] Create payment detail page
- [ ] Implement filter & search
- [ ] Implement export to Excel
- [ ] Create analytics charts
- [ ] Testing

### Phase 5: Testing & Refinement (Week 5-6)
**Deliverables**:
- Unit tests
- Integration tests
- UAT with Finance team
- Bug fixes
- Documentation

**Tasks**:
- [ ] Write unit tests
- [ ] Write integration tests
- [ ] Conduct UAT with Finance
- [ ] Fix bugs from UAT
- [ ] Write user documentation
- [ ] Deploy to production

---

## ✅ Success Criteria

### Must Have (MVP)
- [x] Finance dapat upload bukti bayar
- [x] Email notification terkirim otomatis
- [x] WhatsApp notification terkirim otomatis
- [x] Supplier menerima semua dokumen (PO, GR, Invoice, Bukti Bayar)
- [x] Payment status tracking
- [x] Audit trail lengkap

### Should Have
- [x] Payment dashboard
- [x] Payment history & filter
- [x] Resend notification
- [x] Document download
- [x] Mobile responsive

### Nice to Have (Future)
- [ ] Supplier portal (supplier login & confirm payment)
- [ ] Bulk payment upload
- [ ] Payment reminder (auto remind supplier)
- [ ] Payment analytics & reporting
- [ ] Integration dengan accounting software

---

## 🚨 Risks & Mitigation

### Risk 1: WhatsApp API Rate Limiting
**Impact**: HIGH  
**Probability**: MEDIUM  
**Mitigation**: 
- Use queue with delay between messages
- Implement exponential backoff
- Monitor API usage
- Have fallback to email only

### Risk 2: Email Delivery Failure
**Impact**: MEDIUM  
**Probability**: LOW  
**Mitigation**:
- Use reliable email service (SendGrid/Mailgun)
- Implement retry mechanism (3 attempts)
- Log all failures
- Manual resend option

### Risk 3: File Storage Full
**Impact**: MEDIUM  
**Probability**: LOW  
**Mitigation**:
- Use cloud storage (S3) with auto-scaling
- Implement file size limits (5MB per file)
- Archive old files (> 1 year)
- Monitor storage usage

### Risk 4: Supplier Data Incomplete
**Impact**: HIGH  
**Probability**: MEDIUM  
**Mitigation**:
- Validate supplier email & phone before sending
- Show warning if data incomplete
- Allow Finance to update supplier data
- Fallback to manual notification

---

## 📝 Prerequisites

### Before Implementation
1. **Supplier Data Cleanup**:
   - Ensure all suppliers have valid email
   - Ensure all suppliers have valid WhatsApp number
   - Update contact person information

2. **Email Service Setup**:
   - Choose email service (Gmail/SendGrid/Mailgun)
   - Setup SMTP credentials
   - Configure email domain (optional)

3. **WhatsApp Service Setup**:
   - Create Fonnte/Wablas account
   - Get API key
   - Top-up balance (prepaid)
   - Test API connection

4. **File Storage Setup**:
   - Setup AWS S3 or Google Cloud Storage (production)
   - Configure Laravel Storage
   - Test file upload & download

5. **Queue Setup**:
   - Setup Redis (recommended) or database queue
   - Configure queue worker
   - Test queue processing

---

## 🎯 Next Steps

### Immediate Actions
1. **Review & Approve** this proposal
2. **Validate** supplier data (email & phone)
3. **Setup** external services (Email, WhatsApp, Storage)
4. **Allocate** developer resource
5. **Start** Phase 1 implementation

### Questions to Answer
1. Which email service to use? (Gmail SMTP / SendGrid / Mailgun)
2. Which WhatsApp service to use? (Fonnte / Wablas / Official API)
3. Which file storage to use? (Local / AWS S3 / Google Cloud)
4. Budget approval for monthly operational cost?
5. Timeline approval (6 weeks)?

---

## 📞 Contact

**For Questions or Clarifications**:
- Technical: [Developer Name]
- Business: [Finance Manager]
- Project Manager: [PM Name]

---

## 📄 Related Documents

1. **Requirements**: `.kiro/specs/payment-out-proof/requirements.md`
2. **Technical Design**: `.kiro/specs/payment-out-proof/design.md`
3. **Database Schema**: See design.md
4. **API Documentation**: [To be created]
5. **User Guide**: [To be created]

---

**Proposal Created**: 21 April 2026  
**Status**: Awaiting Approval  
**Priority**: HIGH  
**Estimated ROI**: 80% time savings + better supplier relations
