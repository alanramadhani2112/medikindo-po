import { test, expect, Page } from "@playwright/test";

/**
 * 🧪 MEDIKINDO PO SYSTEM - INDEPENDENT E2E BUSINESS FLOW
 *
 * Anda bisa menjalankan skenario mana saja secara terpisah.
 * Jika variabel state kosong, skrip akan mencari data valid di tabel secara otomatis.
 */

// Global State (untuk meneruskan data jika dijalankan berurutan)
let poUrl = "";
let poNumber = "";
let grNumber = "";
let supplierInvoiceUrl = "";
let customerInvoiceUrl = "";

const USERS = {
    rs: {
        email: "budi.santoso@testhospital.com",
        password: "Healthcare@2026!",
    },
    approver: {
        email: "siti.nurhaliza@medikindo.com",
        password: "Approver@2026!",
    },
    admin: { email: "alanramadhani21@gmail.com", password: "Medikindo@2026!" },
};

// HELPER: Login
async function performLogin(page: Page, role: keyof typeof USERS) {
    await page.context().clearCookies();
    await page.goto("/login");
    await page.fill('input[name="email"]', USERS[role].email);
    await page.fill('input[name="password"]', USERS[role].password);
    await Promise.all([
        page.waitForNavigation({ waitUntil: "networkidle" }),
        page.click('button[type="submit"]'),
    ]);
}

// HELPER: SweetAlert Confirm
async function clickAndConfirm(page: Page, buttonSelector: string) {
    const btn = page.locator(buttonSelector);
    await btn.waitFor({ state: "visible" });
    await btn.click();
    const swal = page.locator(".swal2-confirm");
    if (await swal.isVisible({ timeout: 5000 })) {
        await swal.click();
    }
}

// HELPER: Success Alert (Supports Standard Alert, SweetAlert, and Hidden Data Attributes)
async function verifySuccess(page: Page, message: string) {
    // Tunggu sampai salah satu indikator sukses muncul di DOM (tidak harus visible)
    const selector = '.alert-success, .swal2-html-container, div[data-success-message]';
    const locator = page.locator(selector);
    
    // Tunggu sampai ada elemen yang mengandung pesan kita
    await page.waitForFunction((msg) => {
        const els = document.querySelectorAll('.alert-success, .swal2-html-container, div[data-success-message]');
        for (const el of Array.from(els)) {
            const content = el.textContent + (el.getAttribute('data-success-message') || '');
            if (content.toLowerCase().includes(msg.toLowerCase())) return true;
        }
        return false;
    }, message, { timeout: 25000 });

    console.log(`✨ Success verified: ${message}`);
}

// ------------------------------------------------------------------------------
// SKENARIO 1: RS/KLINIK MEMBUAT PO
// ------------------------------------------------------------------------------
test("Skenario 1: RS/Klinik Membuat dan Mengajukan PO", async ({ page }) => {
    await performLogin(page, "rs");
    await page.goto("/purchase-orders/create");

    await page.locator('select[name="supplier_id"]').selectOption({ index: 1 });
    await page.waitForTimeout(1000);
    await page.click('button:has-text("Tambah Produk")');

    const searchInput = page.locator(
        'input[placeholder="Ketik nama atau SKU produk..."]',
    );
    await searchInput.pressSequentially("Para", { delay: 100 });
    await page.waitForSelector(".product-dropdown-item", { state: "visible" });
    await page.locator(".product-dropdown-item").first().click();

    await page.fill('input[name="items[0][quantity]"]', "10");
    await clickAndConfirm(page, 'button:has-text("Simpan sebagai Draft")');

    poUrl = page.url();
    const headingText = await page.locator("h1").first().innerText();
    poNumber = headingText.replace("Detail PO ", "").trim();
    console.log(`📦 Created PO: ${poNumber}`);

    page.once("dialog", (dialog) => dialog.accept());
    await page.click('button:has-text("Ajukan ke Medikindo")');
    await verifySuccess(page, "berhasil diajukan");
});

// ------------------------------------------------------------------------------
// SKENARIO 2: MEDIKINDO APPROVE PO
// ------------------------------------------------------------------------------
test("Skenario 2: Medikindo Menyetujui PO", async ({ page }) => {
    await performLogin(page, "admin");
    await page.goto("/approvals");

    // DISCOVERY: Jika poNumber kosong, ambil PO pertama yang berstatus 'Diajukan'
    if (!poNumber) {
        console.log(
            "🔍 No PO context found, picking first available pending approval...",
        );
        const firstPoLink = page.locator("tr td a.text-gray-900").first();
        await expect(firstPoLink).toBeVisible({ timeout: 10000 });
        poNumber = (await firstPoLink.innerText()).trim();
    }

    const searchInput = page.locator('input[name="search"]');
    await searchInput.fill(poNumber);
    await searchInput.press("Enter");

    const poRow = page.locator("tr", { hasText: poNumber });
    await expect(poRow.first()).toBeVisible({ timeout: 15000 });
    await poRow
        .first()
        .locator('button.btn-success:has-text("Setujui")')
        .click();

    const swalConfirmBtn = page.locator(".swal2-confirm");
    await expect(swalConfirmBtn).toBeVisible();
    await swalConfirmBtn.click();

    await verifySuccess(page, `PO #${poNumber} berhasil disetujui`);
});

// ------------------------------------------------------------------------------
// SKENARIO 3: PENERIMAAN BARANG (GR)
// ------------------------------------------------------------------------------
test("Skenario 3: Penerimaan Barang (GR Full Receive)", async ({ page }) => {
    await performLogin(page, "admin");

    // DISCOVERY: Cari PO yang sudah Approved jika poNumber kosong
    if (!poNumber) {
        await page.goto("/purchase-orders?status=approved");
        const firstApproved = page.locator("tr td a.text-gray-900").first();
        await expect(firstApproved).toBeVisible();
        poNumber = (await firstApproved.innerText()).trim();
    }

    await page.goto("/goods-receipts/create");
    const poSearch = page.locator(
        'input[placeholder="Ketik Nomor PO atau Nama Supplier..."]',
    );
    await poSearch.pressSequentially(poNumber, { delay: 100 });
    await page.waitForSelector(".po-dropdown-item", { state: "visible" });
    await page
        .locator(".po-dropdown-item", { hasText: poNumber })
        .first()
        .click();

    await page.waitForTimeout(2000);
    await page.fill(
        'input[name="delivery_order_number"]',
        `DO-TEST-${Date.now()}`,
    );
    await page.fill('input[name*="[batch_no]"]', `BATCH-E2E-${Date.now()}`);

    const futureDate = new Date();
    futureDate.setFullYear(futureDate.getFullYear() + 2);
    await page.fill(
        'input[name*="[expiry_date]"]',
        futureDate.toISOString().split("T")[0],
    );

    page.once("dialog", (dialog) => dialog.accept());
    await clickAndConfirm(
        page,
        'button:has-text("Konfirmasi Penerimaan Barang")',
    );
    await verifySuccess(page, "berhasil dikonfirmasi");

    const detailHeading = await page.locator("h1").first().innerText();
    grNumber = detailHeading.replace("Detail Penerimaan ", "").trim();
    console.log(`🚚 GR Number: ${grNumber}`);
});

// ------------------------------------------------------------------------------
// SKENARIO 4: INVOICE SUPPLIER (AP)
// ------------------------------------------------------------------------------
test("Skenario 4: Input Invoice Supplier (AP)", async ({ page }) => {
    await performLogin(page, "admin");

    // DISCOVERY: Cari GR yang sudah Selesai jika grNumber kosong
    if (!grNumber) {
        await page.goto("/goods-receipts");
        const firstGr = page.locator("tr td a.text-gray-900").first();
        await expect(firstGr).toBeVisible();
        grNumber = (await firstGr.innerText()).trim();
    }

    await page.goto("/invoices/supplier/create");
    const grSearch = page.locator(
        'input[placeholder="Ketik Nomor GR atau Nama Supplier..."]',
    );
    await grSearch.pressSequentially(grNumber, { delay: 100 });
    await page.waitForSelector(".gr-dropdown-item", { state: "visible" });
    await page
        .locator(".gr-dropdown-item", { hasText: grNumber })
        .first()
        .click();

    await page.waitForTimeout(2000);
    await page.fill(
        'input[name="distributor_invoice_number"]',
        `INV-DIST-${Date.now()}`,
    );
    await page.fill(
        'input[name="distributor_invoice_date"]',
        new Date().toISOString().split("T")[0],
    );
    await page.fill(
        'input[name="due_date"]',
        new Date(Date.now() + 14 * 24 * 60 * 60 * 1000)
            .toISOString()
            .split("T")[0],
    );

    page.once("dialog", (dialog) => dialog.accept());
    await clickAndConfirm(page, 'button:has-text("Simpan Invoice Pemasok")');
    await verifySuccess(page, "berhasil disimpan");
});

// ------------------------------------------------------------------------------
// SKENARIO 5: INVOICE PELANGGAN (AR)
// ------------------------------------------------------------------------------
test("Skenario 5: Terbitkan Invoice Pelanggan (AR) + Surcharge", async ({
    page,
}) => {
    await performLogin(page, "admin");

    if (!grNumber) {
        await page.goto("/goods-receipts");
        grNumber = (
            await page.locator("tr td a.text-gray-900").first().innerText()
        ).trim();
    }

    await page.goto("/invoices/customer/create");
    const grSearch = page.locator(
        'input[placeholder="Ketik Nomor GR atau Nama RS/Klinik..."]',
    );
    await grSearch.pressSequentially(grNumber, { delay: 100 });
    await page.waitForSelector(".gr-dropdown-item", { state: "visible" });
    await page
        .locator(".gr-dropdown-item", { hasText: grNumber })
        .first()
        .click();

    await page.waitForTimeout(2000);
    await page.fill(
        'input[name="due_date"]',
        new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
            .toISOString()
            .split("T")[0],
    );

    const surcharge = page.locator('input[name="surcharge"]');
    await surcharge.fill("5000000");
    await surcharge.evaluate((e) =>
        e.dispatchEvent(new Event("input", { bubbles: true })),
    );

    page.once("dialog", (dialog) => dialog.accept());
    await clickAndConfirm(page, 'button:has-text("Terbitkan Invoice")');
    await verifySuccess(page, "berhasil diterbitkan");
    customerInvoiceUrl = page.url();
});

// ------------------------------------------------------------------------------
// SKENARIO 6: PEMBAYARAN & SETTLEMENT
// ------------------------------------------------------------------------------
test('Skenario 6: Pembayaran RS & Alokasi Otomatis Kas', async ({ page }) => {
    let invoiceId = '';

    if (!customerInvoiceUrl) {
        await performLogin(page, 'admin');
        await page.goto('/invoices/customer?status=issued');
        const firstAr = page.locator('tr td a.text-gray-900').first();
        await expect(firstAr).toBeVisible();
        customerInvoiceUrl = await firstAr.getAttribute('href') || '';
        if (!customerInvoiceUrl.startsWith('http')) {
            customerInvoiceUrl = 'http://medikindo-po.test' + customerInvoiceUrl;
        }
    }

    // Ekstrak ID invoice dari URL (mendukung format /invoices/customer/{id})
    const urlParts = customerInvoiceUrl.split('/');
    invoiceId = urlParts[urlParts.length - 1];

    // RS Upload Bukti Bayar
    await performLogin(page, 'rs');
    await page.goto(`/payment-proofs/create?invoice_id=${invoiceId}`);
    
    // Pastikan invoice sudah terpilih
    await page.waitForSelector('select[name="customer_invoice_id"]');
    
    // Form Bukti Bayar
    await page.fill('input[name="payment_date"]', new Date().toISOString().split('T')[0]);
    await page.fill('input[name="bank_reference"]', `TRF-E2E-${Date.now()}`);
    await page.fill('textarea[name="notes"]', 'Pembayaran via E2E Test');
    
    // Submit
    await page.click('button:has-text("Submit Bukti Pembayaran")');
    await verifySuccess(page, 'berhasil disubmit');

    const proofUrl = page.url();

    // Admin Verifikasi
    await performLogin(page, 'admin');
    await page.goto(`${proofUrl}/verify`);
    
    // Di halaman Approve/Verify
    await page.fill('textarea[name="approval_notes"]', 'Sesuai dengan mutasi bank (E2E)');
    await page.click('button:has-text("Setujui & Verifikasi Pembayaran")');
    
    await verifySuccess(page, 'berhasil disetujui');
    
    // Pastikan invoice lunas
    await page.goto(customerInvoiceUrl);
    await expect(page.locator('.badge:has-text("PAID"), .badge:has-text("LUNAS")').first()).toBeVisible();
});
