<x-layout title="Input Invoice Pemasok" pageTitle="Input Invoice Pemasok" breadcrumb="Input invoice dari distributor">

    <x-page-header title="Input Invoice Pemasok"
        description="Input invoice yang diterima dari distributor berdasarkan Penerimaan Barang (Goods Receipt).">
    </x-page-header>

    @push('styles')
        <style>
            .gr-search-wrapper {
                position: relative;
            }

            .gr-dropdown {
                position: absolute;
                z-index: 1050;
                background: #fff;
                border: 1px solid #e4e6ef;
                border-radius: 0.475rem;
                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, .1);
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

            .gr-dropdown-item:last-child {
                border-bottom: none;
            }

            .gr-dropdown-item:hover {
                background: #f1f3f9;
            }

            .gr-dropdown-empty {
                padding: 0.75rem 1rem;
                color: #a1a5b7;
                font-size: 0.85rem;
            }
        </style>
    @endpush

    @php
        $grData = $goodsReceipts->map(fn($gr) => [
            'id'            => $gr->id,
            'gr_number'     => $gr->gr_number,
            'po_number'     => $gr->purchaseOrder?->po_number ?? '—',
            'supplier_name' => $gr->purchaseOrder?->supplier?->name ?? '—',
            'items'         => $gr->items->map(fn($item) => [
                'id'                 => $item->id,
                // Nama produk diambil dari purchaseOrderItem->product (bukan item->product langsung)
                'product_name'       => $item->purchaseOrderItem?->product?->name ?? $item->product?->name ?? '—',
                'product_unit'       => $item->purchaseOrderItem?->product?->unit ?? $item->product?->unit ?? 'unit',
                'batch_no'           => $item->batch_no,
                'expiry_date'        => $item->expiry_date?->format('Y-m-d'),
                'quantity_received'  => $item->quantity_received,
                'remaining_quantity' => $item->remaining_ap_quantity,
                // Harga dari PO (cost price) — tidak boleh diubah user
                'unit_price'         => (float) ($item->purchaseOrderItem?->unit_price ?? 0),
                'discount_percent'   => (float) ($item->purchaseOrderItem?->discount_percent ?? 0),
            ])->toArray()
        ])->toArray();
    @endphp

    <div x-data="invoiceForm()" x-init='initData(@json($grData))'>
        <form method="POST" action="{{ route('web.invoices.supplier.store') }}" id="invoice-form">
            @csrf

            {{-- GR Selection --}}
            <x-card title="Pilih Penerimaan Barang" class="mb-5">
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label required fw-semibold fs-6 mb-2">Goods Receipt (Penerimaan
                            Barang)</label>
                        <div class="gr-search-wrapper">
                            <input type="hidden" name="goods_receipt_id" x-model="selectedGrId">
                            <div class="position-relative">
                                <i
                                    class="ki-outline ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                                <input type="text" class="form-control form-control-solid ps-12"
                                    placeholder="Ketik Nomor GR atau Nama Supplier..." x-model="searchQuery"
                                    @focus="showDropdown = true" @blur="setTimeout(() => showDropdown = false, 200)"
                                    @input="selectedGrId = ''; items = []">
                            </div>

                            {{-- Dropdown --}}
                            <div class="gr-dropdown" x-show="showDropdown && filteredGrs().length > 0" x-cloak>
                                <template x-for="gr in filteredGrs()" :key="gr.id">
                                    <div class="gr-dropdown-item" @mousedown.prevent="selectGr(gr)">
                                        <div class="fw-bold text-gray-800 fs-7" x-text="gr.gr_number"></div>
                                        <div class="text-muted fs-8">
                                            <span x-text="gr.supplier_name"></span>
                                            <span class="ms-2 badge badge-light-primary fs-9"
                                                x-text="gr.items.length + ' item'"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="gr-dropdown" x-show="showDropdown && searchQuery && filteredGrs().length === 0"
                                x-cloak>
                                <div class="po-dropdown-empty">Data GR tidak ditemukan</div>
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
                            <span><strong>Supplier:</strong> <span x-text="grInfo.supplier_name"></span></span>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Invoice Details --}}
            <div x-show="selectedGrId" x-transition>
                <x-card title="Detail Invoice Distributor" class="mb-5">
                    <div class="row g-5">
                        <div class="col-md-4">
                            <label class="form-label required fw-semibold fs-6 mb-2">Nomor Invoice Distributor</label>
                            <input type="text" name="distributor_invoice_number"
                                class="form-control form-control-solid" placeholder="Contoh: INV-DIST-2024-001"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required fw-semibold fs-6 mb-2">Tanggal Invoice Distributor</label>
                            <input type="date" name="distributor_invoice_date"
                                class="form-control form-control-solid" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required fw-semibold fs-6 mb-2">Tanggal Jatuh Tempo</label>
                            <input type="date" name="due_date" class="form-control form-control-solid" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold fs-6 mb-2">Catatan (Opsional)</label>
                            <textarea name="notes" class="form-control form-control-solid" rows="3"
                                placeholder="Catatan tambahan untuk invoice ini..."></textarea>
                        </div>
                    </div>
                </x-card>

                {{-- Items --}}
                <x-card title="Item Invoice" class="mb-5">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-7 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4">Produk</th>
                                    <th>Batch</th>
                                    <th>Kadaluarsa</th>
                                    <th class="text-end">Diterima (GR)</th>
                                    <th class="text-end">Sisa Belum Diinvoice</th>
                                    <th class="text-end">Harga Distributor</th>
                                    <th class="text-end">Diskon %</th>
                                    <th class="text-end">Qty Invoice</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="item.id">
                                    <tr>
                                        <td class="ps-4">
                                            <input type="hidden" :name="`items[${index}][goods_receipt_item_id]`" :value="item.id">
                                            {{-- Harga dikirim dari server-side (read-only, tidak bisa dimanipulasi) --}}
                                            <input type="hidden" :name="`items[${index}][unit_price]`" :value="item.unit_price">
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-900 fw-bold" x-text="item.product_name"></span>
                                                <span class="text-gray-500 fs-7" x-text="item.product_unit"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-primary" x-text="item.batch_no || '—'"></span>
                                        </td>
                                        <td>
                                            <span class="text-gray-800" x-text="item.expiry_date ? formatDate(item.expiry_date) : '—'"></span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-700 fw-semibold" x-text="item.quantity_received"></span>
                                            <span class="text-muted fs-8"> unit</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-success fw-bold" x-text="item.remaining_quantity"></span>
                                            <span class="text-muted fs-8"> unit</span>
                                        </td>
                                        <td class="text-end">
                                            {{-- Harga dari master data PO — tidak bisa diubah --}}
                                            <span class="fw-bold text-gray-800" x-text="'Rp ' + item.unit_price.toLocaleString('id-ID')"></span>
                                        </td>
                                        <td class="text-end">
                                            <input type="number" class="form-control text-end bg-white"
                                                :name="`items[${index}][discount_percent]`"
                                                x-model.number="item.discount_percent"
                                                placeholder="0" min="0" max="100"
                                                style="width: 90px;">
                                        </td>
                                        <td class="text-end">
                                            <input type="number" class="form-control text-end bg-white"
                                                :name="`items[${index}][quantity]`"
                                                required :min="1" :max="item.remaining_quantity"
                                                x-model.number="item.invoice_quantity"
                                                style="width: 110px;">
                                            <div class="text-muted fs-9 mt-1" x-show="item.invoice_quantity < item.remaining_quantity">
                                                Sisa: <span x-text="item.remaining_quantity - item.invoice_quantity"></span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </x-card>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-3 pt-5">
                    <a href="{{ route('web.invoices.supplier.index') }}" class="btn btn-light">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary create-confirm" data-type="Invoice Pemasok">
                        <i class="ki-outline ki-check-circle fs-3"></i>
                        Simpan Invoice Pemasok
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

                    initData(data) {
                        this.allGrs = data;
                    },

                    filteredGrs() {
                        if (!this.searchQuery) return this.allGrs;
                        const q = this.searchQuery.toLowerCase();
                        return this.allGrs.filter(gr =>
                            gr.gr_number.toLowerCase().includes(q) ||
                            gr.supplier_name.toLowerCase().includes(q)
                        );
                    },

                    selectGr(gr) {
                        this.selectedGrId = gr.id;
                        this.searchQuery = gr.gr_number;
                        this.showDropdown = false;
                        this.grInfo = {
                            gr_number: gr.gr_number,
                            po_number: gr.po_number,
                            supplier_name: gr.supplier_name
                        };

                        // Load items with remaining qty
                        this.items = gr.items
                            .filter(item => item.remaining_quantity > 0)
                            .map(item => ({
                                ...item,
                                invoice_quantity: item.remaining_quantity,
                                discount_percent: item.discount_percent || 0
                            }));
                    },

                    formatDate(dateString) {
                        if (!dateString) return '—';
                        const date = new Date(dateString);
                        return date.toLocaleDateString('id-ID', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                    }
                }
            }
        </script>
    @endpush

</x-layout>
