<x-layout title="Buat Tagihan ke RS/Klinik" pageTitle="Buat Tagihan ke RS/Klinik" breadcrumb="Buat tagihan berdasarkan penerimaan barang">

    <x-page-header 
        title="Buat Tagihan ke RS/Klinik" 
        description="Buat tagihan (invoice) ke RS/Klinik berdasarkan Penerimaan Barang (Goods Receipt) yang telah dikonfirmasi.">
    </x-page-header>

    <div x-data="invoiceForm()">
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
                                                'unit_price' => $item->purchaseOrderItem->unit_price,
                                                'discount_percent' => $item->purchaseOrderItem->discount_percent,
                                                'tax_percent' => $item->purchaseOrderItem->tax_percent,
                                            ])
                                        ]) }}">
                                    {{ $gr->gr_number }} - {{ $gr->purchaseOrder->organization->name }} ({{ $gr->items->count() }} items)
                                </option>
                            @endforeach
                        </select>
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
                    <div class="alert alert-success d-flex align-items-center mb-5">
                        <i class="ki-outline ki-shield-tick fs-2x text-success me-4"></i>
                        <div>
                            <strong>Informasi:</strong> Tagihan ini akan diterbitkan kepada RS/Klinik berdasarkan barang yang telah diterima. 
                            Harga dan detail produk sesuai dengan Purchase Order yang telah disetujui.
                        </div>
                    </div>

                    <div class="row g-5">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold fs-6 mb-2">Tanggal Jatuh Tempo</label>
                            <input type="date" name="due_date" class="form-control form-control-solid" required>
                            <div class="form-text">Tanggal jatuh tempo pembayaran dari RS/Klinik</div>
                            @error('due_date')
                                <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-6 mb-2">Nomor Invoice (Opsional)</label>
                            <input type="text" name="custom_invoice_number" class="form-control form-control-solid" 
                                   placeholder="Kosongkan untuk generate otomatis">
                            <div class="form-text">Akan di-generate otomatis jika kosong</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold fs-6 mb-2">Catatan (Opsional)</label>
                            <textarea name="notes" class="form-control form-control-solid" rows="3" 
                                      placeholder="Catatan tambahan untuk tagihan ini..."></textarea>
                        </div>
                    </div>
                </x-card>

                {{-- Items --}}
                <x-card title="Item Tagihan" class="mb-5">
                    <div class="alert alert-info d-flex align-items-center mb-5">
                        <i class="ki-outline ki-information fs-2x text-info me-4"></i>
                        <div>
                            <strong>Informasi:</strong> Batch dan tanggal kadaluarsa diambil dari Penerimaan Barang (tidak dapat diubah). 
                            Harga sesuai dengan Purchase Order yang telah disetujui RS/Klinik.
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-300 align-middle gs-7 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4">Produk</th>
                                    <th>Batch</th>
                                    <th>Kadaluarsa</th>
                                    <th class="text-end">Diterima</th>
                                    <th class="text-end">Sudah Diinvoice</th>
                                    <th class="text-end">Sisa</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Qty Invoice</th>
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
                                            <span class="badge badge-light-primary" x-text="item.batch_no || '—'"></span>
                                        </td>
                                        <td>
                                            <span class="text-gray-800" x-text="item.expiry_date ? formatDate(item.expiry_date) : '—'"></span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-800 fw-semibold" x-text="item.quantity_received"></span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-primary fw-semibold" x-text="item.invoiced_quantity"></span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-success fw-bold" x-text="item.remaining_quantity"></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex flex-column align-items-end">
                                                <span class="text-gray-900 fw-semibold" x-text="formatCurrency(item.unit_price)"></span>
                                                <span class="text-gray-500 fs-8" x-show="item.discount_percent > 0">
                                                    Disc: <span x-text="item.discount_percent"></span>%
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <input type="number" 
                                                   class="form-control form-control-solid text-end" 
                                                   :name="`items[${index}][quantity]`" 
                                                   required 
                                                   :min="1" 
                                                   :max="item.remaining_quantity" 
                                                   x-model.number="item.invoice_quantity"
                                                   style="width: 120px;">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </x-card>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-3">
                    <a href="{{ route('web.invoices.customer.index') }}" class="btn btn-light-secondary">
                        <i class="ki-outline ki-arrow-zigzag fs-3"></i>
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
            
            loadGrItems() {
                if (!this.selectedGrId) {
                    this.items = [];
                    this.grInfo = {};
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
                    
                    // Only include items with remaining quantity > 0
                    this.items = grData.items
                        .filter(item => item.remaining_quantity > 0)
                        .map(item => ({
                            ...item,
                            invoice_quantity: item.remaining_quantity // Default to remaining quantity
                        }));
                    
                    if (this.items.length === 0) {
                        alert('Semua item dari Goods Receipt ini sudah diinvoice sepenuhnya.');
                        this.selectedGrId = '';
                        this.grInfo = {};
                    }
                } catch (e) {
                    console.error('Error parsing GR data:', e);
                    this.items = [];
                    this.grInfo = {};
                }
            },
            
            formatDate(dateString) {
                if (!dateString) return '—';
                const date = new Date(dateString);
                const options = { year: 'numeric', month: 'short', day: 'numeric' };
                return date.toLocaleDateString('id-ID', options);
            },
            
            formatCurrency(amount) {
                if (!amount) return 'Rp 0';
                return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
            }
        }
    }
    </script>
    @endpush

</x-layout>
