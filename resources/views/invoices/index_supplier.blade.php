<x-index-layout title="Hutang ke Supplier (AP)" description="Kelola tagihan yang diterima dari distributor/supplier" :breadcrumbs="[['label' => 'Account Payable']]">
    
    <x-slot name="actions">
        @can('create_invoices')
            <x-button :href="route('web.invoices.supplier.create')" icon="plus" label="Input Invoice Pemasok" />
        @endcan
    </x-slot>

    <x-slot name="tabs">
        @foreach(['', 'draft', 'verified', 'paid', 'overdue'] as $statusKey)
            @php 
                $statusEnum = $statusKey ? App\Enums\SupplierInvoiceStatus::tryFrom($statusKey) : null;
                $isActive = ($tab === $statusKey) || ($statusKey === '' && ($tab === 'all' || $tab === ''));
                $label = $statusEnum ? $statusEnum->getLabel() : 'Semua Tagihan';
                $icon = match($statusKey) {
                    'draft' => 'document',
                    'verified' => 'shield-search',
                    'paid' => 'check-circle',
                    'overdue' => 'warning',
                    default => 'list'
                };
            @endphp
            <li class="nav-item">
                <a class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}" 
                   href="{{ route('web.invoices.supplier.index', ['tab' => $statusKey]) }}">
                    <i class="ki-outline ki-{{ $icon }} fs-4 me-3"></i>
                    <span class="fs-6 fw-bold me-3">{{ $label }}</span>
                    @if($statusKey !== '')
                        <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-2">
                            {{ $stats[$statusKey] ?? 0 }}
                        </span>
                    @endif
                </a>
            </li>
        @endforeach
    </x-slot>

    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.invoices.supplier.index')">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" class="form-control form-control-solid ps-12" placeholder="No. Invoice atau Supplier..." value="{{ request('search') }}">
                </div>
            </div>
        </x-filter-bar>
    </x-slot>

    <x-slot name="tableHeader">Daftar Tagihan Pemasok</x-slot>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>Nomor Invoice</th>
                <th>Invoice Distributor</th>
                <th>Supplier</th>
                <th>PO Number</th>
                <th class="text-end">Total</th>
                <th class="text-center">Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($supplierInvoices as $invoice)
                <tr>
                    <td>
                        <a href="{{ route('web.invoices.supplier.show', $invoice) }}" 
                           class="fw-bold text-gray-900 text-hover-primary">
                            {{ $invoice->invoice_number }}
                        </a>
                        <div class="text-muted fs-7 mt-1">{{ $invoice->created_at->format('d M Y') }}</div>
                    </td>
                    <td>
                        @if($invoice->distributor_invoice_number)
                            <span class="fw-bold text-primary">{{ $invoice->distributor_invoice_number }}</span>
                            @if($invoice->distributor_invoice_date)
                                <div class="text-muted fs-7 mt-1">{{ $invoice->distributor_invoice_date->format('d M Y') }}</div>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="fw-bold text-gray-800 fs-6">{{ $invoice->supplier?->name ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="text-gray-700 fs-7">{{ $invoice->purchaseOrder?->po_number ?? '—' }}</span>
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-gray-900 fs-6">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $invoice->status->getBadgeClass() }} fw-bold">{{ $invoice->status->getLabel() }}</span>
                    </td>
                    <td class="text-end">
                        <x-table-action>
                            <x-table-action.item :href="route('web.invoices.supplier.show', $invoice)" icon="eye" label="Lihat Detail" />
                            @if($invoice->isDraft())
                                @can('create_invoices')
                                    <x-table-action.item
                                        icon="shield-search"
                                        label="Verifikasi Invoice"
                                        color="success"
                                        :form="['method' => 'POST', 'action' => route('web.invoices.supplier.verify', $invoice)]"
                                        confirm="Verifikasi invoice ini? Sistem akan otomatis membuat draft tagihan ke RS/Klinik." />
                                @endcan
                            @endif
                            <x-table-action.divider />
                            <x-table-action.item :href="route('web.invoices.supplier.pdf', $invoice)" icon="file-down" label="Cetak PDF" color="info" target="_blank" />
                        </x-table-action>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-10">
                        <x-empty-state icon="document" title="Tidak Ada Data" message="Belum ada tagihan pemasok yang terdaftar." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($supplierInvoices->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $supplierInvoices->links() }}
        </div>
    @endif
</x-index-layout>
