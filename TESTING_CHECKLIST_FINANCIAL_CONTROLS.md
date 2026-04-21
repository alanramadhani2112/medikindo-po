# Financial Controls - Testing Checklist

## URL to Test
`http://medikindo-po.test/financial-controls`

## Pre-requisites
- Login as Super Admin
- Have at least one organization with credit limit configured
- Have at least one organization without credit limit (for testing new limit creation)

---

## Test 1: Action Button Functionality ✓

### Steps:
1. Navigate to Financial Controls page
2. Locate the "Aksi" button in the table
3. Click the "Aksi" button

### Expected Results:
- ✓ Dropdown menu appears below the button
- ✓ Menu shows "Edit Plafon" option with pencil icon (yellow/warning color)
- ✓ Menu shows divider line
- ✓ Menu shows "Aktifkan" or "Nonaktifkan" option with appropriate icon
- ✓ Clicking outside closes the menu
- ✓ Pressing ESC key closes the menu

---

## Test 2: Edit Plafon Modal ✓

### Steps:
1. Click "Aksi" button
2. Click "Edit Plafon"

### Expected Results:
- ✓ Modal opens with title "Edit Plafon: [Organization Name]"
- ✓ Input field shows current credit limit value
- ✓ Info box shows current AR and utilization percentage
- ✓ Checkbox shows current active status
- ✓ "Simpan Perubahan" button is visible

### Test Edit with Valid Amount:
1. Change limit to valid amount (within maximum for org type)
2. Click "Simpan Perubahan"

**Expected**: Success message appears, modal closes, table updates

### Test Edit with Excessive Amount:
1. For Hospital: Enter amount > 20,000,000,000
2. For Clinic: Enter amount > 500,000,000
3. Click "Simpan Perubahan"

**Expected**: Error message appears showing maximum allowed limit

---

## Test 3: Activate/Deactivate Credit Limit ✓

### Steps:
1. Click "Aksi" button
2. Click "Aktifkan" or "Nonaktifkan"

### Expected Results:
- ✓ SweetAlert confirmation dialog appears
- ✓ Dialog shows appropriate message with organization name
- ✓ Clicking "Ya, lanjutkan" submits the form
- ✓ Status badge updates in table
- ✓ Success message appears

---

## Test 4: KPI Cards Display ✓

### Verify:
- ✓ Card 1: "Total Fasilitas Kredit Aktif" shows correct sum
- ✓ Card 1: Badge shows correct count of active organizations
- ✓ Card 2: "Total AR Berjalan (Piutang)" shows correct total (not Rp 0)
- ✓ Card 3: "Plafon Maksimum" shows:
  - RS: Rp 20 Miliar
  - Klinik: Rp 500 Juta
- ✓ All amounts formatted with thousand separators

---

## Test 5: Create New Credit Limit - Auto Suggestion ✓

### Steps:
1. Scroll to "Terapkan Limit Baru" form
2. Select an organization from dropdown

### Expected Results for Hospital/RS:
- ✓ Input field auto-fills with: 20000000000
- ✓ Hint text changes to: "Maksimum: Rp 20.000.000.000"
- ✓ Hint text color changes to blue/primary
- ✓ Info box appears showing:
  - Tipe: Hospital (or Rs)
  - Plafon Maksimum: Rp 20.000.000.000

### Expected Results for Clinic/Klinik:
- ✓ Input field auto-fills with: 500000000
- ✓ Hint text changes to: "Maksimum: Rp 500.000.000"
- ✓ Hint text color changes to blue/primary
- ✓ Info box appears showing:
  - Tipe: Clinic (or Klinik)
  - Plafon Maksimum: Rp 500.000.000

---

## Test 6: Real-time Validation ✓

### Steps:
1. Select an organization (Hospital)
2. Manually change input to amount > 20,000,000,000 (e.g., 25000000000)

### Expected Results:
- ✓ Hint text changes to: "⚠️ Melebihi maksimum! Maksimum: Rp 20.000.000.000"
- ✓ Hint text color changes to red/danger
- ✓ Input field gets red border (is-invalid class)

### Steps:
1. Change input back to valid amount (e.g., 15000000000)

### Expected Results:
- ✓ Hint text changes back to: "Maksimum: Rp 20.000.000.000"
- ✓ Hint text color changes back to blue/primary
- ✓ Red border removed from input field

---

## Test 7: Form Submission Validation ✓

### Test Valid Submission:
1. Select organization
2. Keep suggested amount or enter valid amount
3. Check/uncheck "Aktifkan pemblokiran otomatis"
4. Click "Simpan Kebijakan"

**Expected**: 
- ✓ Form submits successfully
- ✓ Success message appears
- ✓ New row appears in table
- ✓ Form resets

### Test Invalid Submission - Exceeds Maximum:
1. Select Hospital organization
2. Enter amount: 25000000000 (25 Miliar)
3. Click "Simpan Kebijakan"

**Expected**:
- ✓ JavaScript alert appears: "Plafon tidak boleh melebihi Rp 20.000.000.000 untuk hospital"
- ✓ Form does NOT submit

### Test Invalid Submission - No Organization:
1. Leave organization dropdown empty
2. Enter amount
3. Click "Simpan Kebijakan"

**Expected**:
- ✓ JavaScript alert appears: "Pilih organisasi terlebih dahulu"
- ✓ Form does NOT submit

### Test Invalid Submission - Zero or Negative:
1. Select organization
2. Enter amount: 0 or negative number
3. Click "Simpan Kebijakan"

**Expected**:
- ✓ JavaScript alert appears: "Plafon harus lebih dari Rp 0"
- ✓ Form does NOT submit

---

## Test 8: Backend Validation ✓

### Test by bypassing JavaScript:
1. Open browser console
2. Execute: `document.getElementById('creditLimitForm').noValidate = true;`
3. Select Hospital organization
4. Enter amount: 25000000000
5. Submit form

**Expected**:
- ✓ Form submits to server
- ✓ Server returns validation error
- ✓ Error message appears at top of page
- ✓ Error message shows: "Plafon tidak boleh melebihi Rp 20.000.000.000 untuk tipe organisasi Hospital"

---

## Test 9: Responsive Design ✓

### Desktop (>1200px):
- ✓ All columns visible in table
- ✓ KPI cards in 3-column layout
- ✓ Form sidebar visible
- ✓ Action dropdown aligns properly

### Tablet (768px - 1199px):
- ✓ Table scrolls horizontally if needed
- ✓ KPI cards stack appropriately
- ✓ Form moves below table
- ✓ Action dropdown still functional

### Mobile (<768px):
- ✓ Table scrolls horizontally
- ✓ KPI cards stack vertically
- ✓ Form full width
- ✓ Action button text/icon visible
- ✓ Dropdown menu doesn't overflow screen

---

## Test 10: Browser Console Check ✓

### Steps:
1. Open browser developer tools (F12)
2. Go to Console tab
3. Refresh the Financial Controls page
4. Interact with all features

### Expected Results:
- ✓ No JavaScript errors
- ✓ No 404 errors for CSS/JS files
- ✓ No CSRF token errors
- ✓ action-menu.js loads successfully
- ✓ All event listeners attach properly

---

## Test 11: Data Accuracy ✓

### Verify Total AR Calculation:
1. Note the "Total AR Berjalan" amount in KPI card
2. Open database or check individual invoices
3. Calculate manually: Sum of (total_amount - paid_amount) for all invoices with status 'issued', 'partial_paid', or 'overdue'

**Expected**: KPI card amount matches manual calculation

### Verify Utilization Percentage:
1. Note utilization % for an organization
2. Calculate manually: (total_active_ar / max_limit) × 100
3. Compare with displayed percentage

**Expected**: Displayed percentage matches calculation

---

## Test 12: Permission Check ✓

### Test as Non-Super Admin:
1. Logout
2. Login as regular user (not Super Admin)
3. Try to access: `http://medikindo-po.test/financial-controls`

**Expected**:
- ✓ Redirected to dashboard
- ✓ Error message: "Akses ditolak. Anda tidak memiliki izin untuk mengakses kontrol kredit."

---

## Test 13: Edge Cases ✓

### Test with No Organizations:
- ✓ Dropdown shows "— Pilih Organisasi —" only
- ✓ Form cannot be submitted

### Test with All Organizations Having Limits:
- ✓ Dropdown is empty or shows message
- ✓ Form shows appropriate message

### Test with Very Large Numbers:
- ✓ Number formatting works correctly
- ✓ No JavaScript errors with large numbers
- ✓ Database stores values correctly

---

## Performance Tests ✓

### Page Load Time:
- ✓ Page loads in < 2 seconds
- ✓ No slow database queries
- ✓ JavaScript executes without delay

### Action Button Response:
- ✓ Dropdown opens instantly
- ✓ No lag when clicking items
- ✓ Smooth animations

---

## Accessibility Tests ✓

### Keyboard Navigation:
- ✓ Can tab through form fields
- ✓ Can select dropdown with keyboard
- ✓ Can submit form with Enter key
- ✓ Can close dropdown with ESC key

### Screen Reader:
- ✓ Form labels are readable
- ✓ Error messages are announced
- ✓ Button purposes are clear

---

## Final Verification ✓

- [ ] All action buttons work consistently across the page
- [ ] No console errors
- [ ] All validations work (frontend + backend)
- [ ] Auto-suggestion works for both org types
- [ ] KPI cards show accurate data
- [ ] Total AR is not Rp 0 (if there are outstanding invoices)
- [ ] Success/error messages display correctly
- [ ] Page is responsive on all devices
- [ ] Permissions are enforced
- [ ] Data persists correctly in database

---

## Sign-off

**Tested By**: _________________  
**Date**: _________________  
**Status**: ☐ PASS  ☐ FAIL  
**Notes**: _________________
