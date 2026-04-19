<x-layout title="Buat Tagihan ke RS/Klinik" pageTitle="Buat Tagihan ke RS/Klinik" breadcrumb="Buat tagihan berdasarkan penerimaan barang">

    <x-page-header 
        title="Buat Tagihan ke RS/Klinik" 
        description="Buat tagihan (invoice) ke RS/Klinik berdasarkan Penerimaan Barang (Goods Receipt) yang telah dikonfirmasi.">
    </x-page-header>

    <div x-data="invoiceForm()" x-init="init()">
        <form method="POST" action="{{ route('web.invoices.customer.store') }}" id="invoice-form">
            @csrf

            {{-- GR Selection --}}
            <x-card title="Pilih Penerimaan Barang" class="mb-5">
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label required fw-semibold fs-6 mb-2">Goods Receipt (Penerimaan Barang)</label>
                        <select name="goods_receipt_id" class="form-select form-select-solid" required 
                                x-model="selectedGrId" @change="loadGrItems()">
                            <option value="">— Pilih Penerimaan Barang yang sudah selesai —</option>
                            @foreach($goodsReceipts as $gr)
                                <option value="{{ $gr->id }}" 
                                        data-gr="{{ json_encode([
                                            'id' => $gr->id,
                                            'gr_number' => $gr->gr_number,
                                            'po_number' => $gr->purchaseOrder->po_number,
                                            'organization_name' => $gr->purchaseOrder->organization->name,
                                            'organization_id' => $gr->purchaseOrder->organization_id,
                                            'items' => $gr->items->map(fn($item) => [
                                                'id' => $item->id,
                                                'product_id' => $item->purchaseOrderItem->product_id,
                                                'product_name' => $item->purchaseOrderItem->product->name,
                                                'product_unit' => $item->purchaseOrderItem->product->unit,
                                                'batch_no' => $item->batch_no,
                                                'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                                                'quantity_received' => $item->quantity_received,
                                                'remaining_quantity' => $item->remaining_quantity,
                                                'invoiced_quantity' => $item->invoiced_quantity,
                                                // IMPORTANT: Use selling_price for customer invoice
                                                'unit_price' => $item->purchaseOrderItem->product->selling_price ?? $item->purchaseOrderItem->product->price,
                                                'discount_percent' => $item->purchaseOrderItem->discount_percent,
                                                'tax_percent' => 11, // Standard PPN
                                            ])
                                        ]) }}">
                                    {{ $gr->gr_number }} - {{ $gr->purchaseOrder->organization->name }} ({{ $gr->items->count() }} items)
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted mt-2">
                            Hanya menampilkan GR yang sudah memiliki Invoice Pemasok (Supplier Invoice).
                        </div>
                        @error('goods_receipt_id')
                            <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- GR Info --}}
                <div x-show="selectedGrId" x-transition class="mt-5">
                    <div class="alert alert-primary d-flex align-items-center">
                        <i class="ki-outline ki-information-5 fs-2x text-primary me-4"></i>
                        <div class="d-flex flex-column">
                            <h5 class="mb-1">Informasi Penerimaan Barang</h5>
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
                            @error('due_date')
                                <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-6 mb-2">Surcharge (Biaya Tambahan)</label>
                            <div class="input-group input-group-solid">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="surcharge" class="form-control form-control-solid" 
                                       x-model.number="surcharge" @input="calculateTotals()" placeholder="0">
                            </div>
                            <div class="form-text text-primary">Akan ditambahkan ke nilai total tagihan</div>
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

                {{-- Items --}}
                <x-card title="Item Tagihan" class="mb-5">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-7 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4">Produk</th>
                                    <th>Batch / Kadaluarsa</th>
                                    <th class="text-end">Sisa GR</th>
                                    <th class="text-end">Harga Jual (Rp)</th>
                                    <th class="text-end">Qty Tagih</th>
                                    <th class="text-end">Subtotal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="item.id">
                                    <tr>
                                        <td class="ps-4">
                                            <input type="hidden" :name="`items[${index}][goods_receipt_item_id]`" :value="item.id">
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-900 fw-bold" x-text="item.product_name"></span>
                                                <span class="text-gray-500 fs-7" x-text="item.product_unit"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="badge badge-light-primary mb-1" x-text="item.batch_no || '—'"></span>
                                                <span class="text-gray-600 fs-8" x-text="item.expiry_date ? formatDate(item.expiry_date) : '—'"></span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-success fw-bold" x-text="item.remaining_quantity"></span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-900 fw-semibold" x-text="formatCurrency(item.unit_price)"></span>
                                        </td>
                                        <td class="text-end">
                                            <input type="number" 
                                                   class="form-control form-control-solid text-end" 
                                                   :name="`items[${index}][quantity]`" 
                                                   required 
                                                   :min="1" 
                                                   :max="item.remaining_quantity" 
                                                   x-model.number="item.invoice_quantity"
                                                   @input="calculateTotals()"
                                                   style="width: 100px; margin-left: auto;">
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-900 fw-bold" x-text="formatCurrency(item.unit_price * item.invoice_quantity)"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals Summary --}}
                    <div class="d-flex justify-content-end mt-5">
                        <div class="w-100 w-md-350px">
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

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-3 pb-10">
                    <a href="{{ route('web.invoices.customer.index') }}" class="btn btn-light-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-success create-confirm" data-type="Tagihan ke RS/Klinik">
                        <i class="ki-outline ki-picture fs-3"></i>
                        Terbitkan Tagihan ke RS/Klinik
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function invoiceForm() {
        return {
            selectedGrId: '',
            grInfo: {},
            items: [],
            surcharge: 0,
            summary: {
                subtotal: 0,
                tax: 0,
                grandTotal: 0
            },
            
            init() {
                // Initial calculation
            },

            loadGrItems() {
                if (!this.selectedGrId) {
                    this.items = [];
                    this.grInfo = {};
                    this.calculateTotals();
                    return;
                }
                
                const select = document.querySelector('select[name="goods_receipt_id"]');
                const option = select.options[select.selectedIndex];
                
                try {
                    const grData = JSON.parse(option.getAttribute('data-gr'));
                    
                    this.grInfo = {
                        gr_number: grData.gr_number,
                        po_number: grData.po_number,
                        organization_name: grData.organization_name,
                        organization_id: grData.organization_id
                    };
                    
                    this.items = grData.items
                        .filter(item => item.remaining_quantity > 0)
                        .map(item => ({
                            ...item,
                            invoice_quantity: item.remaining_quantity
                        }));
                    
                    this.calculateTotals();
                } catch (e) {
                    console.error('Error parsing GR data:', e);
                    this.items = [];
                    this.grInfo = {};
                }
            },

            calculateTotals() {
                let subtotal = 0;
                this.items.forEach(item => {
                    subtotal += (item.unit_price * item.invoice_quantity);
                });

                let tax = Math.floor(subtotal * 0.11);
                let nett = subtotal + tax + (parseFloat(this.surcharge) || 0);
                
                // e-Meterai logic (Threshold Rp 5.000.000)
                let ematerai = nett >= 5000000 ? 10000 : 0;
                let grandTotal = nett + ematerai;

                this.summary = {
                    subtotal: subtotal,
                    tax: tax,
                    ematerai: ematerai,
                    grandTotal: grandTotal
                };
            },
            
            formatDate(dateString) {
                if (!dateString) return '—';
                const date = new Date(dateString);
                const options = { year: 'numeric', month: 'short', day: 'numeric' };
                return date.toLocaleDateString('id-ID', options);
            },
            
            formatCurrency(amount) {
                return 'Rp ' + (amount || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }
        }
    }
    </script>
    @endpush

</x-layout>
