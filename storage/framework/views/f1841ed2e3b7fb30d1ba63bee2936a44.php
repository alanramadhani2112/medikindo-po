<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Input Invoice Pemasok','pageTitle' => 'Input Invoice Pemasok','breadcrumb' => 'Input invoice dari distributor']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Input Invoice Pemasok','pageTitle' => 'Input Invoice Pemasok','breadcrumb' => 'Input invoice dari distributor']); ?>

    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Input Invoice Pemasok','description' => 'Input invoice yang diterima dari distributor berdasarkan Penerimaan Barang (Goods Receipt).']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Input Invoice Pemasok','description' => 'Input invoice yang diterima dari distributor berdasarkan Penerimaan Barang (Goods Receipt).']); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>

    <div x-data="invoiceForm()">
        <form method="POST" action="<?php echo e(route('web.invoices.supplier.store')); ?>" id="invoice-form">
            <?php echo csrf_field(); ?>

            
            <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['title' => 'Pilih Penerimaan Barang','class' => 'mb-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pilih Penerimaan Barang','class' => 'mb-5']); ?>
                <div class="alert alert-warning d-flex align-items-center mb-5">
                    <i class="ki-outline ki-information-5 fs-2x text-warning me-4"></i>
                    <div>
                        <strong>Penting:</strong> Pilih Goods Receipt yang sesuai dengan invoice fisik yang diterima dari distributor. 
                        Batch dan expiry date harus match dengan GR untuk validasi.
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label required fw-semibold fs-6 mb-2">Goods Receipt (Penerimaan Barang)</label>
                        <select name="goods_receipt_id" class="form-select form-select-solid" required 
                                x-model="selectedGrId" @change="loadGrItems()">
                            <option value="">— Pilih Penerimaan Barang yang sudah selesai —</option>
                            <?php $__currentLoopData = $goodsReceipts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($gr->id); ?>" 
                                        data-gr="<?php echo e(json_encode([
                                            'id' => $gr->id,
                                            'gr_number' => $gr->gr_number,
                                            'po_number' => $gr->purchaseOrder->po_number,
                                            'supplier_name' => $gr->purchaseOrder->supplier->name,
                                            'supplier_id' => $gr->purchaseOrder->supplier_id,
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
                                        ])); ?>">
                                    <?php echo e($gr->gr_number); ?> - <?php echo e($gr->purchaseOrder->supplier->name); ?> (<?php echo e($gr->items->count()); ?> items)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['goods_receipt_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger fs-7 mt-2"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                
                <div x-show="selectedGrId" x-transition class="mt-5">
                    <div class="alert alert-primary d-flex align-items-center">
                        <i class="ki-outline ki-information-5 fs-2x text-primary me-4"></i>
                        <div class="d-flex flex-column">
                            <h5 class="mb-1">Informasi Penerimaan Barang</h5>
                            <span><strong>GR Number:</strong> <span x-text="grInfo.gr_number"></span></span>
                            <span><strong>PO Reference:</strong> <span x-text="grInfo.po_number"></span></span>
                            <span><strong>Supplier:</strong> <span x-text="grInfo.supplier_name"></span></span>
                        </div>
                    </div>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>

            
            <div x-show="selectedGrId" x-transition>
                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['title' => 'Detail Invoice Distributor','class' => 'mb-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Detail Invoice Distributor','class' => 'mb-5']); ?>
                    <div class="alert alert-info d-flex align-items-center mb-5">
                        <i class="ki-outline ki-document fs-2x text-info me-4"></i>
                        <div>
                            <strong>Petunjuk:</strong> Input data sesuai dengan invoice fisik/PDF yang diterima dari distributor.
                        </div>
                    </div>

                    <div class="row g-5">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold fs-6 mb-2">Nomor Invoice Distributor</label>
                            <input type="text" name="distributor_invoice_number" class="form-control form-control-solid" 
                                   placeholder="Contoh: INV-DIST-2024-001" required>
                            <div class="form-text">Nomor invoice dari dokumen distributor</div>
                            <?php $__errorArgs = ['distributor_invoice_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger fs-7 mt-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold fs-6 mb-2">Tanggal Invoice Distributor</label>
                            <input type="date" name="distributor_invoice_date" class="form-control form-control-solid" required>
                            <div class="form-text">Tanggal terbit invoice dari distributor</div>
                            <?php $__errorArgs = ['distributor_invoice_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger fs-7 mt-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold fs-6 mb-2">Tanggal Jatuh Tempo</label>
                            <input type="date" name="due_date" class="form-control form-control-solid" required>
                            <div class="form-text">Tanggal jatuh tempo pembayaran</div>
                            <?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger fs-7 mt-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold fs-6 mb-2">Nomor Invoice Internal (Opsional)</label>
                            <input type="text" name="internal_invoice_number" class="form-control form-control-solid" 
                                   placeholder="Nomor invoice internal Medikindo (opsional)">
                            <div class="form-text">Akan di-generate otomatis jika kosong</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold fs-6 mb-2">Catatan (Opsional)</label>
                            <textarea name="notes" class="form-control form-control-solid" rows="3" 
                                      placeholder="Catatan tambahan untuk invoice ini..."></textarea>
                        </div>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>

                
                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['title' => 'Item Invoice','class' => 'mb-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Item Invoice','class' => 'mb-5']); ?>
                    <div class="alert alert-warning d-flex align-items-center mb-5">
                        <i class="ki-outline ki-shield-tick fs-2x text-warning me-4"></i>
                        <div>
                            <strong>Validasi:</strong> Batch dan expiry date diambil dari GR (tidak dapat diubah). 
                            <strong>Harga distributor</strong> dapat berbeda dengan harga jual Medikindo ke RS/Klinik.
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
                                            <input type="number" 
                                                   class="form-control form-control-solid text-end" 
                                                   :name="`items[${index}][unit_price]`" 
                                                   required 
                                                   step="0.01"
                                                   min="0"
                                                   x-model.number="item.distributor_price"
                                                   placeholder="Harga"
                                                   style="width: 150px;">
                                            <div class="form-text text-end fs-8">
                                                Harga dari invoice distributor
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <input type="number" 
                                                   class="form-control form-control-solid text-end" 
                                                   :name="`items[${index}][discount_percent]`" 
                                                   step="0.01"
                                                   min="0"
                                                   max="100"
                                                   x-model.number="item.discount_percent"
                                                   placeholder="0"
                                                   style="width: 100px;">
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

                    <div class="alert alert-light-primary d-flex align-items-center mt-5">
                        <i class="ki-outline ki-information fs-2x text-primary me-4"></i>
                        <div>
                            <strong>Catatan Harga:</strong> Harga yang diinput di sini adalah <strong>harga beli dari distributor</strong>. 
                            Harga jual Medikindo ke RS/Klinik sudah tercatat di PO dan akan digunakan saat membuat invoice ke RS/Klinik.
                        </div>
                    </div>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>

                
                <div class="d-flex justify-content-end gap-3">
                    <a href="<?php echo e(route('web.invoices.index', ['tab' => 'supplier'])); ?>" class="btn btn-light-secondary">
                        <i class="ki-outline ki-cross fs-3"></i>
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-outline ki-check fs-3"></i>
                        Simpan Invoice Pemasok
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php $__env->startPush('scripts'); ?>
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
                        supplier_name: grData.supplier_name,
                        supplier_id: grData.supplier_id
                    };
                    
                    // Only include items with remaining quantity > 0
                    this.items = grData.items
                        .filter(item => item.remaining_quantity > 0)
                        .map(item => ({
                            ...item,
                            invoice_quantity: item.remaining_quantity, // Default to remaining quantity
                            distributor_price: item.unit_price, // Default to PO price (can be changed)
                            discount_percent: item.discount_percent || 0
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
    <?php $__env->stopPush(); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $attributes = $__attributesOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__attributesOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $component = $__componentOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__componentOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\medikindo-po\resources\views/invoices/create_supplier.blade.php ENDPATH**/ ?>