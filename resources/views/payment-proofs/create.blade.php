<x-layout>
    <x-page-header title="Submit Bukti Pembayaran" :breadcrumbs="$breadcrumbs" />

    <div class="row g-5 g-xl-10" x-data="{ 
        invoiceId: '{{ $invoice->id ?? '' }}',
        amount: '{{ $invoice ? ($invoice->total_amount - $invoice->paid_amount) : '' }}',
        invoices: @js($invoices),
        updateAmount() {
            const selected = this.invoices.find(i => i.id == this.invoiceId);
            if (selected) {
                this.amount = selected.total_amount - selected.paid_amount;
            } else {
                this.amount = '';
            }
        }
    }">
        <div class="col-lg-8">
            <x-card title="Form Bukti Pembayaran" icon="file-up">
                <form action="{{ route('web.payment-proofs.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-8">
                        <label class="required form-label">Pilih Invoice</label>
                        <select name="customer_invoice_id" class="form-select form-select-solid" x-model="invoiceId" @change="updateAmount()" required>
                            <option value="">-- Pilih Invoice --</option>
                            @foreach($invoices as $inv)
                                <option value="{{ $inv->id }}">{{ $inv->invoice_number }} - {{ $inv->organization->name }} (Sisa: Rp {{ number_format($inv->total_amount - $inv->paid_amount, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-8">
                        <div class="col-md-6">
                            <label class="required form-label">Jumlah Pembayaran</label>
                            <div class="input-group input-group-solid">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount" class="form-control" x-model="amount" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="required form-label">Tanggal Pembayaran</label>
                            <input type="date" name="payment_date" class="form-control form-control-solid" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-8">
                        <label class="form-label">Referensi Bank / No. Reff</label>
                        <input type="text" name="bank_reference" class="form-control form-control-solid" placeholder="Contoh: TRX-12345678">
                    </div>

                    <div class="mb-8">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea name="notes" class="form-control form-control-solid" rows="3"></textarea>
                    </div>

                    <div class="mb-10">
                        <label class="form-label">Dokumen Bukti Transfer (Opsional)</label>
                        <input type="file" name="file" class="form-control form-control-solid" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="text-muted fs-7 mt-2">Format: JPG, PNG, PDF. Maksimal 5MB.</div>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('web.payment-proofs.index') }}" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-outline ki-check fs-2"></i>
                            Submit Bukti Pembayaran
                        </button>
                    </div>
                </form>
            </x-card>
        </div>

        <div class="col-lg-4">
            <div class="card card-flush shadow-sm">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">Informasi Penting</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-5">
                        <div class="symbol symbol-40px me-4">
                            <span class="symbol-label bg-light-primary">
                                <i class="ki-outline ki-information-5 fs-2 text-primary"></i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fw-bold fs-6">Verifikasi Manual</span>
                            <span class="text-muted fw-semibold fs-7">Setiap bukti bayar akan diverifikasi oleh Tim Finance Medikindo.</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-5">
                        <div class="symbol symbol-40px me-4">
                            <span class="symbol-label bg-light-info">
                                <i class="ki-outline ki-notification-on fs-2 text-info"></i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fw-bold fs-6">Update Status Otomatis</span>
                            <span class="text-muted fw-semibold fs-7">Status Invoice akan berubah menjadi "Paid" atau "Partial" setelah disetujui.</span>
                        </div>
                    </div>
                    <div class="separator separator-dashed my-5"></div>
                    <div class="bg-light-warning rounded border-warning border border-dashed p-5">
                        <div class="d-flex flex-stack">
                            <div class="d-flex align-items-center">
                                <i class="ki-outline ki-shield-search fs-2x text-warning me-4"></i>
                                <div class="ms-1">
                                    <h4 class="text-gray-800 fw-bold">Butuh Bantuan?</h4>
                                    <div class="fs-7 text-gray-600">Hubungi Support Medikindo jika ada kendala dalam upload bukti bayar.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
