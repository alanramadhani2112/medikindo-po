<x-layout>
    <x-page-header title="Submit Bukti Pembayaran" :breadcrumbs="$breadcrumbs" />

    <div class="row g-5 g-xl-10" x-data="{
        invoiceId: '{{ $invoice->id ?? '' }}',
        paymentType: 'full',
        outstanding: {{ $invoice ? ($invoice->total_amount - $invoice->paid_amount) : 0 }},
        partialAmount: '',
        invoices: @js($invoices->map(fn($i) => ['id' => $i->id, 'invoice_number' => $i->invoice_number, 'organization_name' => $i->organization->name, 'total_amount' => $i->total_amount, 'paid_amount' => $i->paid_amount, 'outstanding' => $i->total_amount - $i->paid_amount])),

        get amount() {
            if (this.paymentType === 'full') return this.outstanding;
            return parseFloat(this.partialAmount) || 0;
        },

        get amountFormatted() {
            return 'Rp ' + this.amount.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        },

        get isPartialValid() {
            const v = parseFloat(this.partialAmount);
            return v > 0 && v < this.outstanding;
        },

        selectInvoice() {
            const inv = this.invoices.find(i => i.id == this.invoiceId);
            if (inv) {
                this.outstanding = inv.outstanding;
                this.partialAmount = '';
            } else {
                this.outstanding = 0;
                this.partialAmount = '';
            }
        }
    }">
        <div class="col-lg-8">
            <x-card title="Form Bukti Pembayaran" icon="file-up">
                <form action="{{ route('web.payment-proofs.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Invoice Selection --}}
                    <div class="mb-8">
                        <label class="required form-label fw-bold">Pilih Invoice Pelanggan</label>

                        @if($invoice)
                            {{-- LOCKED: Coming from "Lanjutkan Pelunasan" — invoice is fixed --}}
                            @php $sisa = $invoice->total_amount - $invoice->paid_amount; @endphp
                            <input type="hidden" name="customer_invoice_id" value="{{ $invoice->id }}">
                            <select class="form-select form-select-solid" disabled>
                                <option selected>
                                    {{ $invoice->invoice_number }} — {{ $invoice->organization->name }}
                                    (Sisa: Rp {{ number_format($sisa, 0, ',', '.') }})
                                </option>
                            </select>
                            <div class="text-muted fs-7 mt-1">
                                <i class="ki-outline ki-lock-2 fs-7"></i>
                                Invoice terkunci — pelunasan untuk pembayaran sebelumnya.
                            </div>
                        @else
                            {{-- OPEN: Fresh submission — user picks an invoice --}}
                            <select name="customer_invoice_id" class="form-select form-select-solid"
                                    x-model="invoiceId" @change="selectInvoice()" required>
                                <option value="">-- Pilih Invoice yang Belum Lunas --</option>
                                @foreach($invoices as $inv)
                                    @php $sisa = $inv->total_amount - $inv->paid_amount; @endphp
                                    <option value="{{ $inv->id }}" {{ old('customer_invoice_id') == $inv->id ? 'selected' : '' }}>
                                        {{ $inv->invoice_number }} — {{ $inv->organization->name }}
                                        (Sisa: Rp {{ number_format($sisa, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('customer_invoice_id')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Payment Type Toggle --}}
                    <div class="mb-8" x-show="invoiceId !== ''">
                        <label class="required form-label fw-bold">Jenis Pembayaran</label>
                        <div class="d-flex gap-3">
                            {{-- Bayar Penuh --}}
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="_payment_type_ui" id="type_full"
                                       x-model="paymentType" value="full">
                                <label class="btn btn-outline btn-outline-dashed btn-active-primary w-100 py-4" for="type_full">
                                    <span class="d-flex flex-column align-items-center gap-1">
                                        <i class="ki-outline ki-check-circle fs-2"></i>
                                        <span class="fw-bold fs-6">Bayar Penuh</span>
                                        <span class="text-muted fs-7">Lunasi seluruh tagihan</span>
                                    </span>
                                </label>
                            </div>
                            {{-- Bayar Sebagian --}}
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="_payment_type_ui" id="type_partial"
                                       x-model="paymentType" value="partial">
                                <label class="btn btn-outline btn-outline-dashed btn-active-warning w-100 py-4" for="type_partial">
                                    <span class="d-flex flex-column align-items-center gap-1">
                                        <i class="ki-outline ki-abstract-26 fs-2"></i>
                                        <span class="fw-bold fs-6">Bayar Sebagian</span>
                                        <span class="text-muted fs-7">Cicil sebagian tagihan</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Outstanding Info Banner --}}
                    <div class="mb-6 rounded p-4 bg-light-primary border border-primary border-dashed"
                         x-show="invoiceId !== ''" x-cloak>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div>
                                <div class="text-muted fs-7 mb-1">Total Tagihan Tersisa</div>
                                <div class="fs-4 fw-bolder text-primary"
                                     x-text="'Rp ' + outstanding.toLocaleString('id-ID')"></div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted fs-7 mb-1">Jumlah yang Akan Dibayar</div>
                                <div class="fs-4 fw-bolder"
                                     :class="paymentType === 'full' ? 'text-success' : (isPartialValid ? 'text-warning' : 'text-danger')"
                                     x-text="amountFormatted"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Partial Amount Input --}}
                    <div class="mb-8" x-show="paymentType === 'partial' && invoiceId !== ''" x-cloak>
                        <label class="required form-label fw-bold">Nominal Bayar (Rp)</label>
                        <div class="input-group input-group-solid">
                            <span class="input-group-text fw-bold">Rp</span>
                            <input type="number" class="form-control form-control-solid"
                                   x-model="partialAmount"
                                   :max="outstanding - 1"
                                   min="1" step="1"
                                   placeholder="Masukkan nominal pembayaran sebagian">
                        </div>
                        <div class="mt-2 fs-7"
                             :class="isPartialValid ? 'text-success' : 'text-danger'"
                             x-show="partialAmount !== ''" x-cloak>
                            <span x-show="!isPartialValid">
                                ⚠ Nominal harus lebih dari 0 dan kurang dari total tagihan tersisa.
                            </span>
                            <span x-show="isPartialValid">
                                ✓ Sisa tagihan setelah pembayaran:
                                Rp <span x-text="(outstanding - parseFloat(partialAmount)).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Hidden real amount field --}}
                    <input type="hidden" name="amount" :value="amount">
                    <input type="hidden" name="payment_type" :value="paymentType">

                    {{-- Date & Bank Reference --}}
                    <div class="row mb-8">
                        <div class="col-md-6">
                            <label class="required form-label fw-bold">Tanggal Pembayaran</label>
                            <input type="date" name="payment_date" class="form-control form-control-solid"
                                   value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Referensi Bank / No. Reff</label>
                            <input type="text" name="bank_reference" class="form-control form-control-solid"
                                   placeholder="Contoh: TRX-12345678"
                                   value="{{ old('bank_reference') }}">
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-8">
                        <label class="form-label fw-bold">Catatan Tambahan <span class="text-muted fs-7">(Opsional)</span></label>
                        <textarea name="notes" class="form-control form-control-solid" rows="3"
                                  placeholder="Informasi tambahan untuk tim Finance...">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Upload Bukti Transfer --}}
                    <div class="mb-10">
                        <label class="required form-label fw-bold">
                            Upload Bukti Transfer
                            <span class="badge badge-light-danger ms-2">Wajib</span>
                        </label>
                        <input type="file" name="file" class="form-control form-control-solid"
                               accept=".jpg,.jpeg,.png,.pdf" required>
                        @error('file')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                        <div class="text-muted fs-7 mt-2">
                            <i class="ki-outline ki-information-4 fs-6"></i>
                            Format: JPG, PNG, PDF. Maks 5MB. Upload screenshot/foto bukti transfer bank.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                        <a href="{{ route('web.payment-proofs.index') }}" class="btn btn-light">
                            <i class="ki-outline ki-arrow-left fs-2"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary"
                                :disabled="invoiceId === '' || (paymentType === 'partial' && !isPartialValid)">
                            <i class="ki-outline ki-check fs-2"></i>
                            Submit Bukti Pembayaran
                        </button>
                    </div>
                </form>
            </x-card>
        </div>

        {{-- Sidebar Info --}}
        <div class="col-lg-4">
            {{-- Bank Account Info (if invoice selected) --}}
            @if($invoice && $invoice->bankAccount)
                <div class="card card-flush border border-success shadow-sm mb-5">
                    <div class="card-header pt-5 bg-light-success">
                        <h3 class="card-title fw-bold text-success">
                            <i class="ki-outline ki-bank fs-2 me-2"></i>
                            Rekening Tujuan Transfer
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="symbol symbol-50px">
                                <div class="symbol-label bg-light-success text-success fw-bold fs-4">
                                    {{ strtoupper(substr($invoice->bankAccount->bank_name, 0, 2)) }}
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold text-gray-900 fs-5">{{ $invoice->bankAccount->bank_name }}</div>
                                <div class="text-muted fs-7">Bank Tujuan</div>
                            </div>
                        </div>
                        <div class="p-4 rounded bg-light-success mb-3">
                            <div class="text-gray-500 fs-8 fw-bold text-uppercase mb-1">Nomor Rekening</div>
                            <div class="fw-bold text-gray-900 fs-3 font-monospace">{{ $invoice->bankAccount->account_number }}</div>
                        </div>
                        <div class="text-gray-600 fs-7 mb-3">
                            <i class="ki-outline ki-profile-user fs-7 me-1"></i>
                            Atas nama: <span class="fw-semibold text-gray-800">{{ $invoice->bankAccount->account_holder_name }}</span>
                        </div>
                        <div class="alert alert-info d-flex align-items-center p-3 mb-0">
                            <i class="ki-outline ki-information-5 fs-5 text-info me-2"></i>
                            <span class="text-gray-700 fs-8">Pastikan transfer ke rekening ini sesuai dengan jumlah yang Anda input.</span>
                        </div>
                    </div>
                </div>
            @elseif($invoice && !$invoice->bankAccount)
                <div class="card card-flush border border-warning shadow-sm mb-5">
                    <div class="card-body py-5 text-center">
                        <i class="ki-outline ki-bank fs-3x text-warning mb-3"></i>
                        <div class="fw-bold text-gray-800 fs-6 mb-2">Rekening Belum Ditentukan</div>
                        <div class="text-muted fs-7">Hubungi Medikindo untuk informasi rekening tujuan transfer.</div>
                    </div>
                </div>
            @endif

            <div class="card card-flush shadow-sm mb-5">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-outline ki-information-5 fs-2 text-primary me-2"></i>
                        Panduan Pembayaran
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-5 p-3 rounded bg-light-success">
                        <i class="ki-outline ki-check-circle fs-2 text-success mt-1"></i>
                        <div>
                            <div class="fw-bold text-gray-800 fs-6">Bayar Penuh</div>
                            <div class="text-muted fs-7">Invoice akan langsung lunas setelah disetujui Finance.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3 mb-5 p-3 rounded bg-light-warning">
                        <i class="ki-outline ki-abstract-26 fs-2 text-warning mt-1"></i>
                        <div>
                            <div class="fw-bold text-gray-800 fs-6">Bayar Sebagian</div>
                            <div class="text-muted fs-7">Invoice menjadi "Partial" — sisa tagihan bisa dibayar di pengajuan berikutnya.</div>
                        </div>
                    </div>
                    <div class="separator separator-dashed my-4"></div>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <i class="ki-outline ki-shield-tick fs-2 text-primary"></i>
                        <div class="text-muted fs-7">Setiap bukti bayar akan diverifikasi tim Finance Medikindo sebelum dicatat.</div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <i class="ki-outline ki-notification-on fs-2 text-info"></i>
                        <div class="text-muted fs-7">Status invoice akan otomatis terupdate setelah disetujui.</div>
                    </div>
                </div>
            </div>

            {{-- Live Summary Card --}}
            <div class="card card-flush border border-primary shadow-sm" x-show="invoiceId !== ''" x-cloak>
                <div class="card-header pt-4 pb-2">
                    <h3 class="card-title fw-bold fs-6 text-gray-700">Ringkasan Pembayaran</h3>
                </div>
                <div class="card-body pt-2 pb-4">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fs-7">Jenis</span>
                        <span class="fw-bold fs-7">
                            <span x-show="paymentType === 'full'" class="badge badge-light-success">Bayar Penuh</span>
                            <span x-show="paymentType === 'partial'" class="badge badge-light-warning">Bayar Sebagian</span>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fs-7">Tagihan Tersisa</span>
                        <span class="fw-bold fs-7 text-primary"
                              x-text="'Rp ' + outstanding.toLocaleString('id-ID')"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted fs-7">Jumlah Dibayar</span>
                        <span class="fw-bolder fs-6"
                              :class="paymentType === 'full' ? 'text-success' : (isPartialValid ? 'text-warning' : 'text-danger')"
                              x-text="amountFormatted"></span>
                    </div>
                    <div class="separator separator-dashed my-3"></div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted fs-7">Sisa Setelah Bayar</span>
                        <span class="fw-bold fs-7"
                              :class="paymentType === 'full' ? 'text-success' : 'text-muted'"
                              x-text="paymentType === 'full' ? 'Rp 0 (Lunas)' : (isPartialValid ? 'Rp ' + (outstanding - parseFloat(partialAmount || 0)).toLocaleString('id-ID') : '-')">
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
