<x-layout :title="'Credit Note: ' . $creditNote->cn_number" :breadcrumbs="$breadcrumbs">
    <x-page-header :title="'Credit Note: ' . $creditNote->cn_number">
        <x-slot name="actions">
            <a href="{{ route('web.credit-notes.index') }}" class="btn btn-light me-3">
                <i class="ki-outline ki-arrow-left fs-3"></i> Kembali
            </a>
            @if($creditNote->isDraft())
                <form action="{{ route('web.credit-notes.issue', $creditNote) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-outline ki-check-circle fs-3"></i> Terbitkan (Issue)
                    </button>
                </form>
            @elseif($creditNote->isIssued())
                <form action="{{ route('web.credit-notes.apply', $creditNote) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="ki-outline ki-verify fs-3"></i> Terapkan (Apply)
                    </button>
                </form>
            @endif
        </x-slot>
    </x-page-header>

    <div class="row g-5">
        <div class="col-xl-8">
            <div class="card card-flush mb-5">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold">Detail Item</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th>Deskripsi / Produk</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($creditNote->lineItems as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-gray-800">{{ $item->description }}</div>
                                            @if($item->product)
                                                <div class="text-muted fs-7">{{ $item->product->name }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">{{ number_format($item->quantity, 0) }}</td>
                                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Subtotal</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($creditNote->amount, 0, ',', '.') }}</td>
                                </tr>
                                @if($creditNote->tax_amount > 0)
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Pajak</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($creditNote->tax_amount, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr class="bg-light">
                                    <td colspan="3" class="text-end fw-bold fs-5">TOTAL</td>
                                    <td class="text-end fw-bold fs-5 text-primary">Rp {{ number_format($creditNote->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold">Catatan & Alasan</h3>
                </div>
                <div class="card-body">
                    <div class="p-5 bg-light rounded">
                        <div class="fw-bold text-gray-800 mb-2">Alasan:</div>
                        <p class="text-gray-700 mb-5">{{ $creditNote->reason }}</p>

                        @if($creditNote->notes)
                            <div class="fw-bold text-gray-800 mb-2">Catatan Tambahan:</div>
                            <p class="text-gray-700 mb-0">{{ $creditNote->notes }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card card-flush mb-5">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold">Informasi Status</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-stack mb-3">
                        <span class="text-gray-500 fw-bold">Status:</span>
                        @php
                            $statusColor = match($creditNote->status) {
                                'applied' => 'success',
                                'issued' => 'warning',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge badge-light-{{ $statusColor }} fw-bold">{{ strtoupper($creditNote->status) }}</span>
                    </div>
                    <div class="d-flex flex-stack mb-3">
                        <span class="text-gray-500 fw-bold">Tipe:</span>
                        <span class="badge badge-light-primary fw-bold">{{ strtoupper($creditNote->type) }}</span>
                    </div>
                    <div class="d-flex flex-stack mb-3">
                        <span class="text-gray-500 fw-bold">Diterbitkan Oleh:</span>
                        <span class="text-gray-800 fw-bold">{{ $creditNote->issuedBy?->name ?? '—' }}</span>
                    </div>
                    <div class="d-flex flex-stack">
                        <span class="text-gray-500 fw-bold">Tanggal Terbit:</span>
                        <span class="text-gray-800 fw-bold">{{ $creditNote->issued_at?->format('d M Y H:i') ?? '—' }}</span>
                    </div>
                </div>
            </div>

            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold">Invoice Terkait</h3>
                </div>
                <div class="card-body">
                    @if($creditNote->customerInvoice)
                        <div class="d-flex align-items-center mb-5">
                            <div class="symbol symbol-40px me-3">
                                <div class="symbol-label bg-light-info text-info"><i class="ki-outline ki-document fs-2"></i></div>
                            </div>
                            <div class="d-flex flex-column">
                                <a href="{{ route('web.invoices.customer.show', $creditNote->customerInvoice) }}" class="text-gray-800 text-hover-primary fw-bold">
                                    {{ $creditNote->customerInvoice->invoice_number }} (AR)
                                </a>
                                <span class="text-muted fs-7">Nilai: Rp {{ number_format($creditNote->customerInvoice->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @elseif($creditNote->supplierInvoice)
                        <div class="d-flex align-items-center mb-5">
                            <div class="symbol symbol-40px me-3">
                                <div class="symbol-label bg-light-danger text-danger"><i class="ki-outline ki-document fs-2"></i></div>
                            </div>
                            <div class="d-flex flex-column">
                                <a href="{{ route('web.invoices.supplier.show', $creditNote->supplierInvoice) }}" class="text-gray-800 text-hover-primary fw-bold">
                                    {{ $creditNote->supplierInvoice->invoice_number }} (AP)
                                </a>
                                <span class="text-muted fs-7">Nilai: Rp {{ number_format($creditNote->supplierInvoice->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted fst-italic">Tidak ada invoice terkait</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layout>
