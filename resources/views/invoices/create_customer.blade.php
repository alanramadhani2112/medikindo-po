<x-layout title="Buat Tagihan ke RS/Klinik" pageTitle="Buat Tagihan ke RS/Klinik" breadcrumb="Buat tagihan berdasarkan penerimaan barang">

    <x-page-header 
        title="Buat Tagihan ke RS/Klinik" 
        description="Buat tagihan (invoice) ke RS/Klinik berdasarkan Penerimaan Barang (Goods Receipt) yang telah dikonfirmasi.">
    </x-page-header>

    @push('styles')
    <style>
    .gr-search-wrapper { position: relative; }
    .gr-dropdown {
        position: absolute;
        z-index: 1050;
        background: #fff;
        border: 1px solid #e4e6ef;
        border-radius: 0.475rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,.1);
        max-height: 250px;
        overflow-y: auto;
        width: 100%;
        margin-top: 2px;
    }
    .gr-dropdown-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f5f5f5;
        transition: background .15s;
    }
    .gr-dropdown-item:last-child { border-bottom: none; }
    .gr-dropdown-item:hover { background: #f1f3f9; }
    </style>
    @endpush

    @php
        $grData = $goodsReceipts->map(fn($gr) => [
            'id' => $gr->id,
            'gr_number' => $gr->gr_number,
            'po_number' => $gr->purchaseOrder?->po_number ?? '—',
            'organization_name' => $gr->purchaseOrder?->organization?->name ?? '—',
            'items' => $gr->items->map(fn($item) => [
                'id' => $item->id,
                'product_name' => $item->product?->name ?? '—',
                'product_unit' => $item->product?->unit ?? 'unit',
                'batch_no' => $item->batch_no,
                'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                'quantity_received' => $item->quantity_received,
                'remaining_quantity' => $item->remaining_ar_quantity,
                'unit_price' => (float)($item->product?->selling_price ?? $item->purchaseOrderItem?->unit_price ?? 0),
                'discount_percent' => $item->purchaseOrderItem?->discount_percent ?? 0,
            ])->toArray()
        ])->toArray();
    @endphp

    <div x-data="invoiceForm()" x-init='initData(@json($grData))'>
        <form method="POST" action="{{ route('web.invoices.customer.store') }}" id="invoice-form">
            @csrf

            {{-- GR Selection --}}
            <x-card title="Pilih Penerimaan Barang" class="mb-5">
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label required fw-semibold fs-6 mb-2">Goods Receipt (Penerimaan Barang)</label>
                        <div class="gr-search-wrapper">
                            <input type="hidden" name="goods_receipt_id" x-model="selectedGrId">
                            <div class="position-relative">
                                <i class="ki-outline ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                                <input type="text"
                                       class="form-control form-control-solid ps-12"
                                       placeholder="Ketik Nomor GR atau Nama RS/Klinik..."
                                       x-model="searchQuery"
                                       @focus="showDropdown = true"
                                       @blur="setTimeout(() => showDropdown = false, 200)"
                                       @input="selectedGrId = ''; items = []">
                            </div>

                            <div class="gr-dropdown" x-show="showDropdown && filteredGrs().length > 0" x-cloak>
                                <template x-for="gr in filteredGrs()" :key="gr.id">
                                    <div class="gr-dropdown-item" @mousedown.prevent="selectGr(gr)">
                                        <div class="fw-bold text-gray-800 fs-7" x-text="gr.gr_number"></div>
                                        <div class="text-muted fs-8">
                                            <span x-text="gr.organization_name"></span>
                                            <span class="ms-2 badge badge-light-info fs-9" x-text="gr.items.length + ' item'"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GR Info --}}
                <div x-show="selectedGrId" x-transition class="mt-5">
                    <div class="alert alert-primary d-flex align-items-center mb-0">
                        <i class="ki-outline ki-information-5 fs-2x text-primary me-4"></i>
                        <div class="d-flex flex-column">
                            <span><strong>GR Number:</strong> <span x-text="grInfo.gr_number"></span></span>
                            <span><strong>PO Reference:</strong> <span x-text="grInfo.po_number"></span></span>
                            <span><strong>RS/Klinik:</strong> <span x-text="grInfo.organization_name"></span></span>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Invoice Details --}}
            <div x-show="selectedGrId" x-transition>
                <x-card title="Detail Tagihan" class="mb-5">
                    <div class="row g-5">
                        <div class="col-md-4">
                            <label class="form-label required fw-semibold fs-6 mb-2">Tanggal Jatuh Tempo</label>
                            <input type="date" name="due_date" class="form-control form-control-solid" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-6 mb-2">Surcharge (Biaya Tambahan)</label>
                            <div class="input-group input-group-solid">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="surcharge" class="form-control form-control-solid" 
                                       x-model.number="surcharge" @input="calculateTotals()" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-6 mb-2">Nomor Invoice (Opsional)</label>
                            <input type="text" name="custom_invoice_number" class="form-control form-control-solid" 
                                   placeholder="Auto-generate jika kosong">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold fs-6 mb-2">Catatan (Opsional)</label>
                            <textarea name="notes" class="form-control form-control-solid" rows="2" 
                                      placeholder="Catatan tambahan untuk tagihan ini..."></textarea>
                        </div>
                    </div>
                </x-card>

                {{-- Items Table --}}
                <x-card title="Item Tagihan" class="mb-5">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-7 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4">Produk</th>
                                    <th>Batch / Exp</th>
                                    <th class="text-end">Diterima</th>
                                    <th class="text-end">Sisa</th>
                                    <th class="text-end">Harga Jual (Nett)</th>
                                    <th class="text-end">Qty Tagihan</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="item.id">
                                    <tr>
                                        <td class="ps-4">
                                            <input type="hidden" :name="`items[${index}][goods_receipt_item_id]`" :value="item.id">
                                            <span class="text-gray-900 fw-bold" x-text="item.product_name"></span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-gray-800 fs-7" x-text="item.batch_no"></span>
                                                <span class="text-muted fs-8" x-text="formatDate(item.expiry_date)"></span>
                                            </div>
                                        </td>
                                        <td class="text-end" x-text="item.quantity_received"></td>
                                        <td class="text-end text-success fw-bold" x-text="item.remaining_quantity"></td>
                                        <td class="text-end">
                                            <span class="fw-bold text-gray-800" x-text="formatCurrency(item.unit_price)"></span>
                                        </td>
                                        <td class="text-end">
                                            <input type="number" class="form-control text-end bg-white" 
                                                   :name="`items[${index}][quantity]`" required
                                                   :max="item.remaining_quantity" min="1"
                                                   x-model.number="item.invoice_quantity" @input="calculateTotals()">
                                        </td>
                                        <td class="text-end fw-bold text-gray-900" x-text="formatCurrency(item.unit_price * item.invoice_quantity)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Summary --}}
                    <div class="d-flex justify-content-end mt-5">
                        <div class="w-100 w-md-400px">
                            <div class="d-flex flex-stack mb-3">
                                <div class="fw-semibold text-gray-600 fs-6">Subtotal:</div>
                                <div class="fw-bold text-gray-800 fs-6" x-text="formatCurrency(summary.subtotal)"></div>
                            </div>
                            <div class="d-flex flex-stack mb-3">
                                <div class="fw-semibold text-gray-600 fs-6">PPN (11%):</div>
                                <div class="fw-bold text-gray-800 fs-6" x-text="formatCurrency(summary.tax)"></div>
                            </div>
                            <div class="d-flex flex-stack mb-3" x-show="surcharge > 0">
                                <div class="fw-semibold text-gray-600 fs-6">Surcharge:</div>
                                <div class="fw-bold text-primary fs-6" x-text="formatCurrency(surcharge)"></div>
                            </div>
                            <div class="d-flex flex-stack mb-3" x-show="summary.ematerai > 0">
                                <div class="fw-semibold text-gray-600 fs-6">e-Meterai:</div>
                                <div class="fw-bold text-gray-800 fs-6" x-text="formatCurrency(summary.ematerai)"></div>
                            </div>
                            <div class="separator separator-dashed my-3"></div>
                            <div class="d-flex flex-stack">
                                <div class="fw-bold text-gray-800 fs-4">Total Tagihan:</div>
                                <div class="fw-bolder text-gray-900 fs-3" x-text="formatCurrency(summary.grandTotal)"></div>
                            </div>
                        </div>
                    </div>
                </x-card>

                <div class="d-flex justify-content-end gap-3 pt-5">
                    <a href="{{ route('web.invoices.customer.index') }}" class="btn btn-light">Batal</a>
                    <button type="submit" class="btn btn-primary create-confirm" data-type="Invoice Pelanggan">
                        <i class="ki-outline ki-check-circle fs-3"></i> Terbitkan Invoice
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function invoiceForm() {
        return {
            allGrs: [],
            selectedGrId: '',
            searchQuery: '',
            showDropdown: false,
            grInfo: {},
            items: [],
            surcharge: 0,
            summary: { subtotal: 0, tax: 0, ematerai: 0, grandTotal: 0 },

            initData(data) { this.allGrs = data; },

            filteredGrs() {
                if (!this.searchQuery) return this.allGrs;
                const q = this.searchQuery.toLowerCase();
                return this.allGrs.filter(gr => 
                    gr.gr_number.toLowerCase().includes(q) || 
                    gr.organization_name.toLowerCase().includes(q)
                );
            },

            selectGr(gr) {
                this.selectedGrId = gr.id;
                this.searchQuery = gr.gr_number;
                this.showDropdown = false;
                this.grInfo = gr;
                this.items = gr.items.filter(i => i.remaining_quantity > 0).map(i => ({
                    ...i, invoice_quantity: i.remaining_quantity
                }));
                this.calculateTotals();
            },

            calculateTotals() {
                let subtotal = this.items.reduce((sum, i) => sum + (i.unit_price * i.invoice_quantity), 0);
                let tax = Math.floor(subtotal * 0.11);
                let nett = subtotal + tax + (parseFloat(this.surcharge) || 0);
                let ematerai = nett >= 5000000 ? 10000 : 0;
                this.summary = { subtotal, tax, ematerai, grandTotal: nett + ematerai };
            },

            formatDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { year:'numeric', month:'short', day:'numeric' }) : '—'; },
            formatCurrency(a) { return 'Rp ' + (a || 0).toLocaleString('id-ID'); }
        }
    }
    </script>
    @endpush
</x-layout>
