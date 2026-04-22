<x-layout>
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-start mb-7">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <h1 class="fs-2 fw-bold text-gray-900 mb-0">{{ $payment->payment_number ?? 'PAY-' . $payment->id }}</h1>
                <span class="badge badge-light-{{ $payment->type === 'incoming' ? 'success' : 'danger' }} fs-7 fw-semibold">
                    {{ $payment->type === 'incoming' ? 'PENERIMAAN' : 'PENGELUARAN' }}
                </span>
                @php
                    $statusColor = match($payment->status ?? 'confirmed') {
                        'confirmed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'primary'
                    };
                @endphp
                <span class="badge badge-light-{{ $statusColor }} fs-7 fw-semibold">
                    {{ strtoupper($payment->status ?? 'CONFIRMED') }}
                </span>
            </div>
            <p class="text-gray-600 fs-6 mb-0">
                {{ $payment->payment_date->format('d M Y, H:i') }} • {{ $payment->payment_method_label }}
                @if($payment->bankAccount)
                    • {{ $payment->bankAccount->bank_name }}
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('web.payments.index') }}" class="btn btn-light btn-sm">
                <i class="ki-outline ki-arrow-left fs-4 me-1"></i>Kembali
            </a>
        </div>
    </div>

    {{-- Payment Summary Cards --}}
    <div class="row g-5 mb-7">
        <div class="col-md-4">
            <div class="card h-100 {{ $payment->type === 'incoming' ? 'bg-light-success' : 'bg-light-danger' }}">
                <div class="card-body">
                    <span class="text-gray-600 fs-8 fw-bold text-uppercase d-block mb-2">Jumlah Pembayaran</span>
                    <div class="text-{{ $payment->type === 'incoming' ? 'success' : 'danger' }} fs-2x fw-bold">
                        {{ $payment->type === 'incoming' ? '+' : '-' }} Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-gray-600 fs-8 fw-bold text-uppercase d-block mb-2">Metode Pembayaran</span>
                    <div class="text-gray-900 fw-bold fs-5 mb-1">{{ $payment->payment_method_label }}</div>
                    @if($payment->bankAccount)
                        <span class="text-muted fs-7">{{ $payment->bankAccount->bank_name }} - {{ $payment->bankAccount->account_number }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-gray-600 fs-8 fw-bold text-uppercase d-block mb-2">Tanggal Transaksi</span>
                    <div class="text-gray-900 fw-bold fs-5">{{ $payment->payment_date->format('d M Y') }}</div>
                    <span class="text-muted fs-7">{{ $payment->payment_date->format('H:i') }} WIB</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaction Flow --}}
    @forelse($payment->allocations as $allocation)
        @php
            $invoice = $allocation->customerInvoice ?? $allocation->supplierInvoice;
            $isCustomerInvoice = $allocation->customerInvoice !== null;
            $step = 0;
            $previousPayments = $isCustomerInvoice
                ? $invoice->paymentAllocations()->with('payment')->whereHas('payment', fn($q) => $q->where('id', '!=', $payment->id))->get()
                : collect();
        @endphp
        
        <div class="card card-flush mb-7">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title fw-bold text-gray-800 fs-3">
                    <i class="ki-outline ki-{{ $isCustomerInvoice ? 'entrance-right' : 'exit-up' }} fs-2 me-2 text-{{ $isCustomerInvoice ? 'success' : 'warning' }}"></i>
                    {{ $isCustomerInvoice ? 'Penerimaan dari Customer' : 'Pembayaran ke Supplier' }}
                </h3>
                <div class="card-toolbar">
                    <span class="badge badge-light-{{ $isCustomerInvoice ? 'success' : 'warning' }} fs-7 fw-bold px-3 py-2">
                        Alokasi: Rp {{ number_format($allocation->allocated_amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>
            <div class="card-body pt-6">
                {{-- Transaction Flow Timeline --}}
                <div class="d-flex flex-column gap-5">
                    {{-- Step 1: Purchase Order --}}
                    @if($invoice->purchaseOrder)
                    @php $step++ @endphp
                    <div class="card border border-primary">
                        <div class="card-header min-h-60px bg-light-primary border-0 py-5">
                            <div class="card-title">
                                <div class="d-flex align-items-center gap-4">
                                    <div class="symbol symbol-40px">
                                        <div class="symbol-label bg-primary">
                                            <i class="ki-outline ki-document fs-3 text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge badge-primary fs-8 fw-bold mb-2">STEP {{ $step }}</span>
                                        <h5 class="fw-bold text-gray-900 fs-5 mb-0">Purchase Order</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-6 pb-6">
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3" style="width: 150px;">PO Number</td>
                                            <td class="text-gray-900 fw-bold fs-6 py-3">
                                                <a href="{{ route('web.po.show', $invoice->purchaseOrder) }}" class="text-gray-900 text-hover-primary text-decoration-underline">
                                                    {{ $invoice->purchaseOrder->po_number }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Supplier</td>
                                            <td class="text-gray-800 fw-semibold fs-6 py-3">{{ $invoice->purchaseOrder->supplier->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Total Amount</td>
                                            <td class="text-gray-800 fw-bold fs-6 py-3">Rp {{ number_format($invoice->purchaseOrder->total_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Step 2: Goods Receipt --}}
                    @if($invoice->goodsReceipt)
                    @php $step++ @endphp
                    <div class="card border border-success">
                        <div class="card-header min-h-60px bg-light-success border-0 py-5">
                            <div class="card-title">
                                <div class="d-flex align-items-center gap-4">
                                    <div class="symbol symbol-40px">
                                        <div class="symbol-label bg-success">
                                            <i class="ki-outline ki-package fs-3 text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge badge-success fs-8 fw-bold mb-2">STEP {{ $step }}</span>
                                        <h5 class="fw-bold text-gray-900 fs-5 mb-0">Goods Receipt</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-6 pb-6">
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3" style="width: 150px;">GR Number</td>
                                            <td class="text-gray-900 fw-bold fs-6 py-3">
                                                <a href="{{ route('web.goods-receipts.show', $invoice->goodsReceipt) }}" class="text-gray-900 text-hover-primary text-decoration-underline">
                                                    {{ $invoice->goodsReceipt->gr_number }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Received Date</td>
                                            <td class="text-gray-800 fw-semibold fs-6 py-3">{{ $invoice->goodsReceipt->received_date->format('d M Y') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Step 3: Supplier Invoice (for Customer Invoice) --}}
                    @if($isCustomerInvoice && $invoice->supplierInvoice)
                    @php $step++ @endphp
                    <div class="card border border-warning">
                        <div class="card-header min-h-60px bg-light-warning border-0 py-5">
                            <div class="card-title">
                                <div class="d-flex align-items-center gap-4">
                                    <div class="symbol symbol-40px">
                                        <div class="symbol-label bg-warning">
                                            <i class="ki-outline ki-bill fs-3 text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge badge-warning fs-8 fw-bold mb-2">STEP {{ $step }}</span>
                                        <h5 class="fw-bold text-gray-900 fs-5 mb-0">Supplier Invoice</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-6 pb-6">
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3" style="width: 150px;">Invoice Number</td>
                                            <td class="text-gray-900 fw-bold fs-6 py-3">
                                                <a href="{{ route('web.invoices.supplier.show', $invoice->supplierInvoice) }}" class="text-gray-900 text-hover-primary text-decoration-underline">
                                                    {{ $invoice->supplierInvoice->invoice_number }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Total Amount</td>
                                            <td class="text-gray-800 fw-bold fs-6 py-3">Rp {{ number_format($invoice->supplierInvoice->total_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Status</td>
                                            <td class="py-3">
                                                <span class="badge {{ $invoice->supplierInvoice->status->getBadgeClass() }} fs-7 fw-bold">
                                                    {{ $invoice->supplierInvoice->status->getLabel() }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Step N: Customer / Supplier Invoice --}}
                    @php $step++ @endphp
                    <div class="card border border-info">
                        <div class="card-header min-h-60px bg-light-info border-0 py-5">
                            <div class="card-title">
                                <div class="d-flex align-items-center gap-4">
                                    <div class="symbol symbol-40px">
                                        <div class="symbol-label bg-info">
                                            <i class="ki-outline ki-receipt-square fs-3 text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge badge-info fs-8 fw-bold mb-2">STEP {{ $step }}</span>
                                        <h5 class="fw-bold text-gray-900 fs-5 mb-0">{{ $isCustomerInvoice ? 'Customer Invoice' : 'Supplier Invoice' }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-6 pb-6">
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3" style="width: 150px;">Invoice Number</td>
                                            <td class="text-gray-900 fw-bold fs-6 py-3">
                                                <a href="{{ $isCustomerInvoice ? route('web.invoices.customer.show', $invoice) : route('web.invoices.supplier.show', $invoice) }}"
                                                   class="text-gray-900 text-hover-primary text-decoration-underline">
                                                    {{ $invoice->invoice_number }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">{{ $isCustomerInvoice ? 'Customer' : 'Supplier' }}</td>
                                            <td class="text-gray-800 fw-semibold fs-6 py-3">
                                                {{ $isCustomerInvoice ? ($invoice->organization->name ?? '-') : ($invoice->supplier->name ?? '-') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Total Tagihan</td>
                                            <td class="text-gray-800 fw-bold fs-6 py-3">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Status</td>
                                            <td class="py-3">
                                                <span class="badge {{ $invoice->status->getBadgeClass() }} fs-7 fw-bold">
                                                    {{ $invoice->status->getLabel() }}
                                                </span>
                                            </td>
                                        </tr>
                                        @if($invoice->outstanding_amount > 0)
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Sisa Tagihan</td>
                                            <td class="text-danger fw-bold fs-6 py-3">Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Step N+1...: Cicilan sebelumnya (jika ada) --}}
                    @foreach($previousPayments as $prevAlloc)
                    @php $step++ @endphp
                    <div class="card border border-success border-dashed">
                        <div class="card-header min-h-60px bg-light-success border-0 py-5">
                            <div class="card-title">
                                <div class="d-flex align-items-center gap-4">
                                    <div class="symbol symbol-40px">
                                        <div class="symbol-label bg-success">
                                            <i class="ki-outline ki-check-circle fs-3 text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge badge-success fs-8 fw-bold mb-2">STEP {{ $step }}</span>
                                        <h5 class="fw-bold text-gray-900 fs-5 mb-0">
                                            Cicilan {{ $loop->iteration }}
                                            <span class="badge badge-light-success fs-8 ms-2">Sudah Dibayar</span>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-6 pb-6">
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3" style="width: 150px;">Payment Number</td>
                                            <td class="text-gray-900 fw-bold fs-6 py-3">
                                                <a href="{{ route('web.payments.show', $prevAlloc->payment) }}" class="text-hover-primary text-decoration-underline">
                                                    {{ $prevAlloc->payment->payment_number ?? 'PAY-' . $prevAlloc->payment->id }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Jumlah</td>
                                            <td class="text-success fw-bold fs-5 py-3">Rp {{ number_format($prevAlloc->allocated_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Tanggal</td>
                                            <td class="text-gray-800 fw-semibold fs-6 py-3">{{ $prevAlloc->payment->payment_date->format('d M Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Metode</td>
                                            <td class="py-3">
                                                <span class="badge badge-light-info fs-7">{{ $prevAlloc->payment->payment_method_label }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Step Final: Pembayaran ini (current) --}}
                    @php $step++ @endphp
                    <div class="card border border-primary">
                        <div class="card-header min-h-60px bg-light-primary border-0 py-5">
                            <div class="card-title">
                                <div class="d-flex align-items-center gap-4">
                                    <div class="symbol symbol-40px">
                                        <div class="symbol-label bg-primary">
                                            <i class="ki-outline ki-check fs-3 text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge badge-primary fs-8 fw-bold mb-2">STEP {{ $step }}</span>
                                        <h5 class="fw-bold text-gray-900 fs-5 mb-0">
                                            {{ $isCustomerInvoice ? 'Payment Received' : 'Payment Sent' }}
                                            @if($previousPayments->isNotEmpty())
                                                <span class="badge badge-light-primary fs-8 ms-2">Cicilan {{ $previousPayments->count() + 1 }}</span>
                                            @endif
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-6 pb-6">
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3" style="width: 150px;">Jumlah Dibayar</td>
                                            <td class="fw-bold text-{{ $isCustomerInvoice ? 'success' : 'danger' }} fs-5 py-3">
                                                Rp {{ number_format($allocation->allocated_amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @if($previousPayments->isNotEmpty())
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Total Terbayar</td>
                                            <td class="fw-bold text-success fs-6 py-3">
                                                Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                                                @if($invoice->outstanding_amount <= 0)
                                                    <span class="badge badge-success ms-2">LUNAS</span>
                                                @else
                                                    <span class="text-muted fs-7 ms-1">dari Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Payment Date</td>
                                            <td class="text-gray-800 fw-semibold fs-6 py-3">{{ $payment->payment_date->format('d M Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-600 fw-semibold fs-7 ps-0 py-3">Method</td>
                                            <td class="py-3">
                                                <span class="badge badge-light-info fs-7 fw-bold">{{ $payment->payment_method_label }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!$loop->last)
        <div class="separator separator-dashed my-7"></div>
        @endif
    @empty
        <div class="card card-flush">
            <div class="card-body text-center py-20">
                <div class="d-flex flex-column align-items-center">
                    <i class="ki-outline ki-information-5 fs-5x text-muted mb-5"></i>
                    <h3 class="text-gray-900 fw-bold fs-2 mb-3">Tidak Ada Alokasi</h3>
                    <p class="text-gray-600 fs-5 max-w-400px">Payment ini belum dialokasikan ke invoice manapun.</p>
                </div>
            </div>
        </div>
    @endforelse

    {{-- Bank & Payment Information --}}
    <div class="row g-5 mb-7">
        {{-- Bank Penerima (Medikindo) --}}
        @if($payment->bankAccount)
        <div class="col-md-6">
            <div class="card h-100 border border-primary">
                <div class="card-header min-h-60px bg-light-primary border-0 py-5">
                    <h3 class="card-title fw-bold text-gray-800 fs-5">
                        <i class="ki-outline ki-bank fs-3 me-2 text-primary"></i>
                        {{ $payment->type === 'incoming' ? 'Bank Penerima (Medikindo)' : 'Bank Pengirim (Medikindo)' }}
                    </h3>
                </div>
                <div class="card-body pt-6 pb-6">
                    <div class="d-flex align-items-center gap-4 mb-5">
                        <div class="symbol symbol-50px">
                            <div class="symbol-label bg-primary">
                                <span class="text-white fw-bold fs-4">{{ strtoupper(substr($payment->bankAccount->bank_name, 0, 2)) }}</span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-gray-900 fs-4 mb-1">{{ $payment->bankAccount->bank_name }}</div>
                            <span class="text-muted fs-7">Bank {{ $payment->type === 'incoming' ? 'Penerima' : 'Pengirim' }}</span>
                        </div>
                    </div>
                    <div class="separator mb-4"></div>
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <span class="text-gray-600 fs-7 fw-semibold d-block mb-1">Nomor Rekening</span>
                            <span class="text-gray-900 fw-bold fs-3 font-monospace">{{ $payment->bankAccount->account_number }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 fs-7 fw-semibold d-block mb-1">Nama Pemegang Rekening</span>
                            <span class="text-gray-900 fw-bold fs-6">{{ $payment->bankAccount->account_name }}</span>
                        </div>
                        @if($payment->bankAccount->branch)
                        <div>
                            <span class="text-gray-600 fs-7 fw-semibold d-block mb-1">Cabang</span>
                            <span class="text-gray-800 fs-6">{{ $payment->bankAccount->branch }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Informasi Transfer / Metode Pembayaran --}}
        <div class="col-md-6">
            <div class="card h-100 border border-success">
                <div class="card-header min-h-60px bg-light-success border-0 py-5">
                    <h3 class="card-title fw-bold text-gray-800 fs-5">
                        <i class="ki-outline ki-{{ $payment->type === 'incoming' ? 'entrance-right' : 'exit-up' }} fs-3 me-2 text-success"></i>
                        Detail Pembayaran
                    </h3>
                </div>
                <div class="card-body pt-6 pb-6">
                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="symbol symbol-40px">
                                <div class="symbol-label bg-light-success">
                                    <i class="ki-outline ki-wallet fs-2 text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <span class="text-gray-600 fs-7 fw-semibold d-block mb-1">Metode Pembayaran</span>
                                <span class="badge badge-success fs-6 fw-bold">{{ $payment->payment_method_label }}</span>
                            </div>
                        </div>
                        
                        @if($payment->reference)
                        <div class="separator"></div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="symbol symbol-40px">
                                <div class="symbol-label bg-light-info">
                                    <i class="ki-outline ki-tag fs-2 text-info"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <span class="text-gray-600 fs-7 fw-semibold d-block mb-1">Nomor Referensi / Bukti Transfer</span>
                                <span class="text-gray-900 fw-bold fs-6 font-monospace">{{ $payment->reference }}</span>
                            </div>
                        </div>
                        @endif

                        @if($payment->description)
                        <div class="separator"></div>
                        <div>
                            <span class="text-gray-600 fs-7 fw-semibold d-block mb-2">Catatan</span>
                            <div class="p-3 rounded bg-light">
                                <span class="text-gray-800 fs-6">{{ $payment->description }}</span>
                            </div>
                        </div>
                        @endif

                        {{-- Info Bank Pengirim (dari RS/Customer) untuk incoming payment --}}
                        @if($payment->type === 'incoming')
                        <div class="separator"></div>
                        <div class="alert alert-primary d-flex align-items-start py-3 px-4 mb-0">
                            <i class="ki-outline ki-information-5 fs-2 text-primary me-3 mt-1"></i>
                            <div>
                                <div class="fw-bold text-primary fs-6 mb-1">Bank Pengirim</div>
                                <div class="text-gray-700 fs-7">
                                    Transfer diterima dari 
                                    <span class="fw-bold">{{ $invoice->organization->name ?? 'Customer' }}</span>
                                    @if($payment->reference)
                                        <br>Ref: {{ $payment->reference }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>

@push('styles')
<style>
/* Metronic Standard Card Styles */
.card {
    box-shadow: 0 0 20px 0 rgba(76, 87, 125, 0.02);
}

.card-header.min-h-60px {
    min-height: 60px;
}

.table-row-dashed tbody tr {
    border-bottom: 1px dashed #e4e6ef;
}

.table-row-dashed tbody tr:last-child {
    border-bottom: none;
}

.symbol-40px {
    width: 40px;
    height: 40px;
}

.symbol-40px .symbol-label {
    width: 40px;
    height: 40px;
}

.text-decoration-underline {
    text-decoration: underline;
}

/* Hover effects */
.card:hover {
    box-shadow: 0 0 30px 0 rgba(76, 87, 125, 0.08);
    transition: box-shadow 0.3s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-header .card-title {
        font-size: 1rem !important;
    }
    
    .card-header.min-h-60px {
        min-height: auto;
        padding: 1rem !important;
    }
    
    .symbol-40px {
        width: 35px;
        height: 35px;
    }
    
    .symbol-40px .symbol-label {
        width: 35px;
        height: 35px;
    }
}
</style>
@endpush
