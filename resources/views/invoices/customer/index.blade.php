<x-index-layout title="Tagihan ke RS/Klinik (AR)" description="Kelola tagihan yang diterbitkan ke RS/Klinik"
    :breadcrumbs="[['label' => 'Account Receivable']]">

    @can('create_invoices')
        <x-slot name="actions">
            <x-button :href="route('web.invoices.customer.create')" icon="plus" variant="primary">
                Buat Tagihan ke RS/Klinik
            </x-button>
        </x-slot>
    @endcan

    {{-- Summary Stats --}}
    <x-slot name="top">
        <div class="row g-4 mb-7">
            @php
                $statCards = [
                    ['label' => 'Total Piutang', 'value' => $stats['total_outstanding'] ?? 0, 'color' => 'primary', 'icon' => 'bill'],
                    ['label' => 'Menunggu Bayar', 'value' => $stats['issued_amount'] ?? 0, 'color' => 'warning', 'icon' => 'time'],
                    ['label' => 'Dibayar Sebagian', 'value' => $stats['partial_amount'] ?? 0, 'color' => 'info', 'icon' => 'information-5'],
                    ['label' => 'Overdue', 'value' => $stats['overdue_amount'] ?? 0, 'color' => 'danger', 'icon' => 'cross-circle'],
                ];
            @endphp
            @foreach($statCards as $card)
                <div class="col-md-3">
                    <div class="card bg-{{ $card['color'] }} h-100">
                        <div class="card-body py-5">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="ki-outline ki-{{ $card['icon'] }} fs-2 text-white opacity-75"></i>
                                <span class="text-white fs-8 fw-bold text-uppercase">{{ $card['label'] }}</span>
                            </div>
                            <div class="text-white fs-2x fw-bold">Rp {{ number_format($card['value'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-slot>

    {{-- Toolbar: Search + Filters --}}
    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.invoices.customer.index')">
            <div class="flex-grow-1" style="max-width: 320px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" class="form-control form-control-solid ps-12"
                        placeholder="No. Invoice / Nama RS..." value="{{ request('search') }}">
                </div>
            </div>
            <div style="min-width: 160px;">
                <select name="status" class="form-select form-select-solid">
                    <option value="">Semua Status</option>
                    <option value="issued"       @selected(request('status') === 'issued')>Menunggu Pembayaran</option>
                    <option value="partial_paid" @selected(request('status') === 'partial_paid')>Dibayar Sebagian</option>
                    <option value="paid"         @selected(request('status') === 'paid')>Lunas</option>
                    <option value="overdue"      @selected(request('status') === 'overdue')>Overdue</option>
                    <option value="draft"        @selected(request('status') === 'draft')>Draft</option>
                    <option value="void"         @selected(request('status') === 'void')>Void</option>
                </select>
            </div>
            <div style="min-width: 140px;">
                <select name="aging" class="form-select form-select-solid">
                    <option value="">Semua Aging</option>
                    <option value="current" @selected(request('aging') === 'current')>Belum Jatuh Tempo</option>
                    <option value="1-30"    @selected(request('aging') === '1-30')>1–30 Hari</option>
                    <option value="31-60"   @selected(request('aging') === '31-60')>31–60 Hari</option>
                    <option value="61-90"   @selected(request('aging') === '61-90')>61–90 Hari</option>
                    <option value="90+"     @selected(request('aging') === '90+')>90+ Hari</option>
                </select>
            </div>
            <div style="max-width: 140px;">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-solid">
            </div>
            <div style="max-width: 140px;">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-solid">
            </div>
        </x-filter-bar>
    </x-slot>

    {{-- Tabs --}}
    <x-slot name="tabs">
        @php
            $tabOptions = [
                'all'          => ['label' => 'Semua',          'icon' => 'home'],
                'draft'        => ['label' => 'Draft',          'icon' => 'document'],
                'issued'       => ['label' => 'Menunggu Bayar', 'icon' => 'notification-on'],
                'partial_paid' => ['label' => 'Bayar Sebagian', 'icon' => 'information-5'],
                'paid'         => ['label' => 'Lunas',          'icon' => 'check-circle'],
                'overdue'      => ['label' => 'Jatuh Tempo',    'icon' => 'warning-2'],
            ];
            $currentTab = request('status', 'all');
        @endphp
        <x-status-tabs :tabs="$tabOptions" :current="$currentTab" route="web.invoices.customer.index" :counts="$tabCounts" param="status" />
    </x-slot>

    <x-slot name="tableHeader">Daftar Tagihan Pelanggan</x-slot>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>No. Invoice</th>
                <th>RS/Klinik</th>
                <th class="text-center">Jatuh Tempo</th>
                <th class="text-center">Aging</th>
                <th class="text-end">Total</th>
                <th class="text-end">Outstanding</th>
                <th class="text-center">Status</th>
                <th class="text-end pe-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
                @php
                    $agingBucket = $invoice->aging_bucket;
                    $agingColor  = match($agingBucket) {
                        'current' => 'success',
                        '1-30'    => 'warning',
                        '31-60'   => 'danger',
                        '61-90'   => 'danger',
                        '90+'     => 'dark',
                        default   => 'secondary',
                    };
                    $isOverdue = $invoice->isOverdueByDate();
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('web.invoices.customer.show', $invoice) }}"
                            class="fw-bold text-gray-900 text-hover-primary">
                            {{ $invoice->invoice_number }}
                        </a>
                        @if($invoice->supplierInvoice)
                            <div class="text-muted fs-8 mt-1">
                                <i class="ki-outline ki-arrow-right fs-8 me-1 text-primary"></i>
                                AP: {{ $invoice->supplierInvoice->invoice_number }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="fw-bold text-gray-800">{{ $invoice->organization?->name ?? '—' }}</span>
                    </td>
                    <td class="text-center">
                        @if($invoice->due_date)
                            <span class="fw-semibold {{ $isOverdue ? 'text-danger' : 'text-gray-700' }}">
                                {{ $invoice->due_date->format('d M Y') }}
                            </span>
                            @if($isOverdue)
                                <div class="text-danger fs-9 mt-1 fw-bold">
                                    <i class="ki-outline ki-time fs-9 me-1"></i>
                                    +{{ $invoice->days_overdue }} hari
                                </div>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if(!in_array($invoice->status->value, ['paid', 'void', 'draft']))
                            <span class="badge badge-light-{{ $agingColor }} fw-bold">
                                {{ $agingBucket === 'current' ? 'On Time' : $agingBucket . ' hr' }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-gray-900">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                    </td>
                    <td class="text-end">
                        @if($invoice->outstanding_amount > 0)
                            <span class="fw-bold text-danger">Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}</span>
                            @php $pct = $invoice->total_amount > 0 ? ($invoice->paid_amount / $invoice->total_amount) * 100 : 0; @endphp
                            <div class="progress h-4px mt-1" style="min-width:80px">
                                <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                            </div>
                        @else
                            <span class="badge badge-light-success fw-bold">Lunas</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $invoice->status->getBadgeClass() }} fw-bold">
                            {{ $invoice->status->getLabel() }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <x-table-action>
                            <x-table-action.item :href="route('web.invoices.customer.show', $invoice)" icon="eye" label="Lihat Detail" />
                            @if($invoice->status->canAcceptPayment())
                                @can('process_payments')
                                    <x-table-action.item :href="route('web.payments.create.incoming', ['invoice_id' => $invoice->id])" icon="dollar" label="Tambah Pembayaran" color="success" />
                                @else
                                    @can('submit_payment_proof')
                                        <x-table-action.item :href="route('web.payment-proofs.create', ['invoice_id' => $invoice->id])" icon="shield-tick" label="Upload Bukti Bayar" color="success" />
                                    @endcan
                                @endcan
                            @endif
                            @if($invoice->status->value === 'draft')
                                <x-table-action.divider />
                                <x-table-action.item icon="check-circle" label="Terbitkan Invoice" color="primary"
                                    :form="['method' => 'POST', 'action' => route('web.invoices.customer.issue', $invoice)]"
                                    confirm="Terbitkan invoice ini ke RS/Klinik?" />
                            @endif
                            <x-table-action.divider />
                            <x-table-action.item :href="route('web.invoices.customer.pdf', $invoice)" icon="file-down" label="Cetak PDF" color="info" target="_blank" />
                        </x-table-action>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-10">
                        <x-empty-state icon="document" title="Belum Ada Tagihan"
                            message="Tagihan AR dibuat otomatis saat Supplier Invoice diverifikasi." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($invoices->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $invoices->links() }}
        </div>
    @endif
</x-index-layout>
