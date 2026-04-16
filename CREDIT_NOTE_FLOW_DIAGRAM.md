# CREDIT NOTE FLOW DIAGRAM

## 🔄 COMPLETE CREDIT NOTE WORKFLOW

### Phase 1: Problem Identification & Request
```
[Customer] ──complaint──> [Medikindo CS] ──verify──> [Finance Team]
    │                           │                         │
    │                           │                         │
    ▼                           ▼                         ▼
[Evidence]                 [Investigation]           [Approval Decision]
- Photo barang rusak       - Check delivery record   - Approve/Reject claim
- Batch number             - Verify invoice          - Determine CN amount
- Complaint details        - Contact supplier        - Set CN type
```

### Phase 2: Credit Note Creation (DRAFT)
```
[Finance User] ──login──> [System] ──create──> [Credit Note DRAFT]
    │                        │                      │
    │                        │                      │
    ▼                        ▼                      ▼
[Select Invoice]        [Auto-generate]        [CN-YYYY-MM-XXXX]
- Find customer invoice  - CN Number            - Status: DRAFT
- Verify amount         - Calculate tax        - Editable
- Check permissions     - Line items           - Not yet effective
```

### Phase 3: Credit Note Issuance (ISSUED)
```
[Finance Manager] ──review──> [Credit Note] ──issue──> [Status: ISSUED]
    │                           │                         │
    │                           │                         │
    ▼                           ▼                         ▼
[Approval Check]           [System Validation]      [Document Generated]
- Verify amounts           - Check business rules   - PDF credit note
- Review justification     - Validate permissions   - Email notification
- Final approval           - Audit logging          - Ready to apply
```

### Phase 4: Credit Note Application (APPLIED)
```
[System] ──auto/manual──> [Apply to Invoice] ──update──> [Invoice Balance]
    │                           │                           │
    │                           │                           │
    ▼                           ▼                           ▼
[Trigger Event]            [Balance Calculation]      [Status Update]
- Manual application       - Reduce outstanding       - PARTIAL_PAID
- Auto on issuance        - Update paid_amount       - PAID (if full)
- Scheduled batch         - Recalculate balance      - Audit trail
```

## 📊 STATE MACHINE DIAGRAM

```
    ┌─────────┐    create    ┌─────────┐    issue    ┌─────────┐
    │ INITIAL │ ──────────> │  DRAFT  │ ──────────> │ ISSUED  │
    └─────────┘             └─────────┘             └─────────┘
                                 │                       │
                                 │ cancel                │ apply
                                 ▼                       ▼
                            ┌─────────┐             ┌─────────┐
                            │CANCELLED│             │ APPLIED │
                            └─────────┘             └─────────┘
                                                         │
                                                         │ reverse
                                                         ▼
                                                    ┌─────────┐
                                                    │REVERSED │
                                                    └─────────┘
```

## 🎯 BUSINESS RULES & VALIDATIONS

### Creation Rules:
- ✅ Must reference existing invoice
- ✅ CN amount ≤ Invoice remaining balance
- ✅ User must have 'create_invoices' permission
- ✅ Must belong to same organization
- ✅ Invoice must not be VOID

### Issuance Rules:
- ✅ Only DRAFT credit notes can be issued
- ✅ Must have Finance Manager approval
- ✅ All line items must be validated
- ✅ Tax calculations must be correct

### Application Rules:
- ✅ Only ISSUED credit notes can be applied
- ✅ Target invoice must allow modifications
- ✅ Cannot exceed invoice total amount
- ✅ Auto-update invoice status based on balance

## 🔐 PERMISSION MATRIX

| Role            | Create | Issue | Apply | Cancel | View |
|-----------------|--------|-------|-------|--------|------|
| Super Admin     | ✅     | ✅    | ✅    | ✅     | ✅   |
| Admin Pusat     | ✅     | ✅    | ✅    | ✅     | ✅   |
| Finance         | ✅     | ✅    | ✅    | ❌     | ✅   |
| Healthcare User | ❌     | ❌    | ❌    | ❌     | ✅   |
| Approver        | ❌     | ❌    | ❌    | ❌     | ✅   |

## 📈 IMPACT ON FINANCIAL REPORTS

### Invoice Aging Report:
```
Before CN: Outstanding = Rp 10.000.000
After CN:  Outstanding = Rp 7.000.000 (reduced by CN amount)
```

### Cash Flow Impact:
```
- Reduces Accounts Receivable
- May trigger refund process
- Affects revenue recognition
- Updates customer credit limit
```

## 🚨 ERROR HANDLING & ROLLBACK

### Common Errors:
1. **Insufficient Balance**: CN amount > Invoice balance
2. **Permission Denied**: User lacks required permissions  
3. **Invalid State**: Trying to modify applied CN
4. **Organization Mismatch**: CN and Invoice different orgs

### Rollback Scenarios:
1. **Reverse Application**: Undo CN application to invoice
2. **Cancel Issued CN**: Mark as cancelled (audit trail preserved)
3. **Delete Draft CN**: Permanent removal (only drafts)

## 📋 AUDIT TRAIL EVENTS

```json
{
  "credit_note.created": {
    "cn_number": "CN-2024-01-001",
    "invoice_id": 123,
    "amount": 3000000,
    "type": "return",
    "created_by": "user_id"
  },
  "credit_note.issued": {
    "cn_number": "CN-2024-01-001", 
    "issued_by": "manager_id",
    "issued_at": "2024-01-15T10:30:00Z"
  },
  "credit_note.applied": {
    "cn_number": "CN-2024-01-001",
    "invoice_id": 123,
    "old_balance": 10000000,
    "new_balance": 7000000,
    "applied_by": "system"
  }
}
```