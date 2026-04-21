<x-index-layout title="Tagihan ke RS/Klinik (AR)" description="Kelola tagihan piutang yang diterbitkan ke RS/Klinik"
    :breadcrumbs="[['label' => 'Account Receivable']]">

    <x-slot name="actions">
        {{-- AR tidak dibuat manual — dibuat otomatis saat AP diverifikasi --}}
        <a href="{{ route('web.invoices.supplier.index') }}" class="btn btn-light-primary btn-sm">
            <i class="ki-outline ki-arrow-right fs-4 me-1"></i>
            Verifikasi Invoice Pemasok
        </a>
    </x-slot>

    <x-slot name="tabs">
        @foreach (['', 'draft', 'issued', 'partial_paid', 'paid', 'void'] as $statusKey)
            @php
                $statusEnum = $statusKey ? App\Enums\CustomerInvoiceStatus::tryFrom($statusKey) : null;
                $isActive = $tab === $statusKey;
                $label = $statusEnum ? $statusEnum->getLabel() : 'Semua Tagihan';
                $icon = match ($statusKey) {
                    'draft' => 'document',
                    'issued' => 'notification-on',
                    'partial_paid' => 'information-5',
                    'paid' => 'check-circle',
                    'void' => 'cross-circle',
                    default => 'list',
                };
            @endphp
            <li class="nav-item">
                <a class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}"
                    href="{{ route('web.invoices.customer.index', ['tab' => $statusKey]) }}">
                    <i class="ki-outline ki-{{ $icon }} fs-4 me-3"></i>
                    <span class="fs-6 fw-bold me-3">{{ $label }}</span>
                    @if ($statusKey !== '')
                        <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-2">
                            {{ $stats[$statusKey] ?? 0 }}
                        </span>
                    @endif
                </a>
            </li>
        @endforeach
    </x-slot>

    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.invoices.customer.index')">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" class="form-control form-control-solid ps-12"
                        placeholder="No. Invoice atau RS/Klinik..." value="{{ request('search') }}">
                </div>
            </div>
        </x-filter-bar>
    </x-slot>

    <x-slot name="tableHeader">Daftar Tagihan Pelanggan</x-slot>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>Nomor Invoice</th>
                <th>RS/Klinik</th>
                <th>Referensi Logistik</th>
                <th class="text-end">Total Tagihan</th>
                <th class="text-center">Status</th>
                <th class="text-end pe-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customerInvoices as $invoice)
                <tr>
                    <td>
                        <a href="{{ route('web.invoices.customer.show', $invoice) }}"
                            class="fw-bold text-gray-900 text-hover-primary">
                            {{ $invoice->invoice_number }}
                        </a>
                        <div class="text-muted fs-7 mt-1">{{ $invoice->created_at->format('d M Y') }}</div>
                    </td>
                    <td>
                        <span class="fw-bold text-gray-800 fs-6">{{ $invoice->organization?->name ?? '—' }}</span>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <span class="text-gray-700 fs-7">PO:
                                {{ $invoice->purchaseOrder?->po_number ?? '—' }}</span>
                            <span class="text-primary fs-7 fw-semibold">GR:
                                {{ $invoice->goodsReceipt?->gr_number ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-gray-900 fs-6">Rp
                            {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                        @if($invoice->outstanding_amount > 0)
                            <div class="text-danger fs-8 mt-1">
                                Sisa: Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}
                            </div>
                        @endif
                    </td>
                    <td class="text-center">
                        <span
                            class="badge {{ $invoice->status->getBadgeClass() }} fw-bold">{{ $invoice->status->getLabel() }}</span>
                        @if($invoice->isOverdueByDate())
                            <div class="mt-1">
                                <span class="badge badge-danger fs-9">+{{ $invoice->days_overdue }}h lewat</span>
                            </div>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <x-table-action>
                            <x-table-action.item :href="route('web.invoices.customer.show', $invoice)" icon="eye" label="Lihat Detail" />
                            @if($invoice->status->canAcceptPayment())
                                <x-table-action.item :href="route('web.payments.create.incoming', ['invoice_id' => $invoice->id])" icon="dollar" label="Tambah Pembayaran" color="success" />
                            @endif
                            <x-table-action.divider />
                            <x-table-action.item :href="route('web.invoices.customer.pdf', $invoice)" icon="file-down" label="Cetak PDF" color="info" target="_blank" />
                        </x-table-action>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-10">
                        <x-empty-state icon="document" title="Tidak Ada Data"
                            message="Belum ada tagihan pelanggan yang terdaftar." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($customerInvoices->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $customerInvoices->links() }}
        </div>
    @endif
</x-index-layout>
