<x-index-layout title="Tagihan ke RS/Klinik (AR)" description="Kelola tagihan yang diterbitkan ke RS/Klinik"
    :breadcrumbs="[['label' => 'Account Receivable']]">
    @can('create_invoices')
        <x-slot name="actions">
            <x-button :href="route('web.invoices.customer.create')" icon="plus" variant="primary">
                Buat Tagihan ke RS/Klinik
            </x-button>
        </x-slot>
    @endcan

    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.invoices.customer.index')">
            <div class="flex-grow-1" style="max-width: 300px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" class="form-control form-control-solid ps-12"
                        placeholder="No. Invoice / Nama RS..." value="{{ request('search') }}">
                </div>
            </div>

            <div style="min-width: 150px;">
                <select name="status" class="form-select form-select-solid">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="issued" @selected(request('status') === 'issued')>Issued</option>
                    <option value="partial_paid" @selected(request('status') === 'partial_paid')>Partial Paid</option>
                    <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                    <option value="void" @selected(request('status') === 'void')>Void</option>
                </select>
            </div>

            <div style="max-width: 150px;">
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="form-control form-control-solid">
            </div>
            <div style="max-width: 150px;">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="form-control form-control-solid">
            </div>
        </x-filter-bar>
    </x-slot>

    <x-slot name="tabs">
        @php
            $tabOptions = [
                'all' => ['label' => 'Semua', 'icon' => 'ki-home'],
                'draft' => ['label' => 'Draft', 'icon' => 'ki-document'],
                'issued' => ['label' => 'Menunggu', 'icon' => 'ki-notification-on'],
                'partial_paid' => ['label' => 'Partial', 'icon' => 'ki-information-5'],
                'paid' => ['label' => 'Lunas', 'icon' => 'ki-check-circle'],
            ];
            $currentTab = request('status', 'all');
        @endphp
        @foreach ($tabOptions as $val => $tabData)
            @php $isActive = $currentTab === $val; @endphp
            <li class="nav-item">
                <a href="{{ route('web.invoices.customer.index', array_merge(request()->except(['status', 'page']), ['status' => $val === 'all' ? null : $val])) }}"
                    class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                    <i class="ki-outline {{ $tabData['icon'] }} fs-4 me-3"></i>
                    <span class="fs-6 fw-bold me-3">{{ $tabData['label'] }}</span>
                </a>
            </li>
        @endforeach
    </x-slot>

    <x-slot name="tableHeader">Daftar Tagihan Pelanggan</x-slot>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>No. Invoice</th>
                <th>RS/Klinik</th>
                <th>Tgl. Invoice</th>
                <th>Jatuh Tempo</th>
                <th class="text-end">Grand Total</th>
                <th class="text-center">Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
                <tr>
                    <td>
                        <a href="{{ route('web.invoices.customer.show', $invoice) }}"
                            class="fw-bold text-gray-900 text-hover-primary">
                            {{ $invoice->invoice_number }}
                        </a>
                        @if ($invoice->supplierInvoice)
                            <div class="text-muted fs-8 mt-1">
                                <i class="ki-outline ki-arrow-right fs-8 me-1 text-primary"></i>
                                AP: {{ $invoice->supplierInvoice->invoice_number }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="fw-bold text-gray-800 fs-6">{{ $invoice->organization?->name ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="text-gray-700">
                            {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') : '—' }}
                        </span>
                    </td>
                    <td>
                        @if ($invoice->due_date)
                            @php $isOverdue = $invoice->due_date->isPast() && !in_array($invoice->status, ['paid', 'void']); @endphp
                            <span class="fw-bold {{ $isOverdue ? 'text-danger' : 'text-gray-700' }}">
                                {{ $invoice->due_date->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-gray-900 fs-6">
                            Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span
                            class="badge {{ $invoice->status->getBadgeClass() }} fw-bold">{{ $invoice->status->getLabel() }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('web.invoices.customer.show', $invoice) }}"
                            class="btn btn-icon btn-light-primary btn-sm" title="Lihat Detail">
                            <i class="ki-outline ki-eye fs-2"></i>
                        </a>
                        @if ($invoice->status->value === 'draft')
                            <form method="POST" action="{{ route('web.invoices.customer.issue', $invoice) }}"
                                class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-icon btn-light-success btn-sm"
                                    title="Terbitkan Invoice">
                                    <i class="ki-outline ki-check-circle fs-2"></i>
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('web.invoices.customer.pdf', $invoice) }}" target="_blank"
                            class="btn btn-icon btn-light-info btn-sm" title="Cetak PDF">
                            <i class="ki-outline ki-file-down fs-2"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-10">
                        <x-empty-state icon="document" title="Belum Ada Tagihan"
                            message="Tagihan AR dibuat otomatis saat Supplier Invoice diverifikasi." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($invoices->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $invoices->links() }}
        </div>
    @endif
</x-index-layout>
