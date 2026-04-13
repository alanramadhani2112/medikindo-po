<x-layout title="Finance Console" pageTitle="Rekonsiliasi Finansial" breadcrumb="Dashboard — Finance">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="ui-page-title">Konsol Finansial</h1>
            <p class="ui-text">Rekonsiliasi total piutang organisasi dan hutang supplier.</p>
        </div>
        <div class="flex items-center gap-3">
            <x-badge variant="info" class="px-4 py-1.5 rounded-full ui-badge">
                AR: {{ $arInvoices->total() }} Faktur
            </x-badge>
            <x-badge variant="danger" class="px-4 py-1.5 rounded-full ui-badge">
                AP: {{ $apInvoices->total() }} Faktur
            </x-badge>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <x-dashboard-card 
            title="Total Piutang (AR)" 
            value="Rp {{ number_format($stats['ar_total'], 0, ',', '.') }}"
            description="Terbuka / Piutang Berjalan"
            color="primary"
            icon="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
        />
        <x-dashboard-card 
            title="Piutang Overdue" 
            :value="$stats['ar_overdue']"
            description="Pelanggan Menunggak"
            color="warning"
            icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
        />
        <x-dashboard-card 
            title="Total Hutang (AP)" 
            value="Rp {{ number_format($stats['ap_total'], 0, ',', '.') }}"
            description="Kewajiban Pembayaran"
            color="danger"
            icon="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
        />
        <x-dashboard-card 
            title="Hutang Overdue" 
            :value="$stats['ap_overdue']"
            description="Action Required"
            color="danger"
            icon="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
        />
    </div>

    <div class="space-y-12">
        
        {{-- Section: Piutang Organisasi (AR) --}}
        <x-card title="Piutang Organisasi (Accounts Receivable)" icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" class="px-0 py-0 overflow-hidden">
            <x-slot name="actions">
                <span class="ui-section-label bg-primary-clarity px-3 py-1 rounded-full text-primary">Total {{ $arInvoices->total() }} Faktur</span>
            </x-slot>

            <x-table :headers="['No. Faktur', 'Organisasi', 'Tenggat', ['label' => 'Total Tagihan', 'class' => 'text-right'], ['label' => 'Terbayar', 'class' => 'text-right'], 'Status', ['label' => 'Aksi', 'class' => 'text-right']]">
                @forelse($arInvoices as $inv)
                <tr class="hover:bg-gray-50 transition-colors group">
                    <td class="px-6 py-4">
                        <a href="{{ route('web.invoices.customer.show', $inv) }}" class="ui-card-title text-gray-900 group-hover:text-primary transition-colors">{{ $inv->invoice_number }}</a>
                    </td>
                    <td class="px-6 py-4"><span class="ui-text font-bold text-gray-700">{{ $inv->organization->name }}</span></td>
                    <td class="px-6 py-4"><span class="ui-muted">{{ $inv->due_date?->format('d M Y') }}</span></td>
                    <td class="px-6 py-4 text-right ui-card-title text-gray-900">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-right ui-value-sm text-primary">Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        @php
                            $stColor = match($inv->status) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                'overdue' => 'danger',
                                default => 'primary'
                            };
                        @endphp
                        <x-badge variant="{{ $stColor }}">{{ strtoupper($inv->status) }}</x-badge>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <x-button variant="secondary" size="sm" outline href="{{ route('web.payments.create.incoming', ['invoice_id' => $inv->id]) }}">
                            Catat Bayar
                        </x-button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-400 ui-text">Tidak ada piutang ditemukan.</td>
                </tr>
                @endforelse
            </x-table>

            @if($arInvoices->hasPages())
                <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/10">
                    {{ $arInvoices->links('components.pagination-links') }}
                </div>
            @endif
        </x-card>

        {{-- Section: Hutang Supplier (AP) --}}
        <x-card title="Hutang ke Supplier (Accounts Payable)" icon="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" class="px-0 py-0 overflow-hidden">
            <x-slot name="actions">
                <span class="ui-section-label bg-danger-clarity px-3 py-1 rounded-full text-danger">Total {{ $apInvoices->total() }} Faktur</span>
            </x-slot>

            <x-table :headers="['No. Faktur', 'Supplier', 'Tenggat', ['label' => 'Total Tagihan', 'class' => 'text-right'], ['label' => 'Terbayar', 'class' => 'text-right'], 'Status', ['label' => 'Aksi', 'class' => 'text-right']]">
                @forelse($apInvoices as $inv)
                <tr class="hover:bg-gray-50 transition-colors group">
                    <td class="px-6 py-4">
                        <a href="{{ route('web.invoices.supplier.show', $inv) }}" class="ui-card-title text-gray-900 group-hover:text-danger transition-colors">{{ $inv->invoice_number }}</a>
                    </td>
                    <td class="px-6 py-4"><span class="ui-text font-bold text-gray-700">{{ $inv->supplier->name }}</span></td>
                    <td class="px-6 py-4"><span class="ui-muted">{{ $inv->due_date?->format('d M Y') }}</span></td>
                    <td class="px-6 py-4 text-right ui-card-title text-gray-900">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 text-right ui-value-sm text-primary">Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        @php
                            $stColor = match($inv->status) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                'overdue' => 'danger',
                                default => 'primary'
                            };
                        @endphp
                        <x-badge variant="{{ $stColor }}">{{ strtoupper($inv->status) }}</x-badge>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <x-button variant="danger" size="sm" outline href="{{ route('web.payments.create.outgoing', ['invoice_id' => $inv->id]) }}">
                            Bayar Supplier
                        </x-button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-400 ui-text">Tidak ada hutang ditemukan.</td>
                </tr>
                @endforelse
            </x-table>

            @if($apInvoices->hasPages())
                <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/10">
                    {{ $apInvoices->links('components.pagination-links') }}
                </div>
            @endif
        </x-card>

    </div>

</x-layout>


