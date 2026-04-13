<x-layout title="Audit Explorer" pageTitle="Eksplorasi Audit Log" breadcrumb="Dashboard — Audit">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="ui-page-title">Jejak Audit Sistem</h1>
            <p class="ui-text">Log riwayat transaksi dan aktivitas seluruh pengguna sistem Medikindo.</p>
        </div>
    </div>

    <x-card title="Aktivitas Terbaru" icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" class="px-0 py-0 overflow-hidden">
        <x-slot name="actions">
            <span class="ui-section-label bg-gray-50 px-3 py-1 rounded-full">Displaying Last 30</span>
        </x-slot>

        <x-table :headers="['Waktu', 'Pengguna', 'Aksi', 'Entitas', 'Detail (Metadata)', ['label' => 'IP / Device', 'class' => 'text-right']]">
            @forelse($logs as $log)
            <tr class="hover:bg-slate-50/50 transition-colors group">
                <td class="px-6 py-4">
                    <div class="flex flex-col leading-tight">
                        <span class="ui-card-title text-gray-900">{{ $log->occurred_at->format('d/m/Y H:i:s') }}</span>
                        <span class="ui-section-label text-gray-400">{{ $log->occurred_at->diffForHumans() }}</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex flex-col leading-tight">
                        <span class="ui-text font-bold text-gray-700">{{ $log->user->name ?? 'System' }}</span>
                        <span class="ui-section-label text-gray-400">{{ $log->user->email ?? '' }}</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    @php
                        $variant = match(true) {
                            str_contains($log->action, 'create') || str_contains($log->action, 'submit') => 'success',
                            str_contains($log->action, 'delete') || str_contains($log->action, 'reject') => 'danger',
                            str_contains($log->action, 'update') || str_contains($log->action, 'edit')   => 'info',
                            default => 'primary',
                        };
                    @endphp
                    <x-badge :variant="$variant">{{ strtoupper($log->action) }}</x-badge>
                </td>
                <td class="px-6 py-4">
                    <div class="flex flex-col leading-tight">
                        <span class="ui-card-title text-primary">{{ class_basename($log->entity_type) }}</span>
                        <span class="ui-section-label text-gray-400">ID: {{ $log->entity_id }}</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    @if($log->metadata && count($log->metadata) > 0)
                        <div class="flex flex-wrap gap-1.5 focus:scale-110">
                            @foreach($log->metadata as $key => $value)
                                <div class="bg-gray-50 rounded-lg px-2 py-1 flex items-center gap-1 border border-gray-100 transition-transform active:scale-95">
                                    <span class="ui-badge !text-gray-400">{{ $key }}:</span>
                                    <span class="ui-badge !text-gray-600">
                                        @if($key === 'amount' || $key === 'total')
                                            Rp {{ is_numeric($value) ? number_format($value, 0, ',', '.') : $value }}
                                        @else
                                            {{ is_array($value) ? json_encode($value) : $value }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="ui-muted">No extra details</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex flex-col items-end leading-tight">
                        <span class="ui-section-label text-gray-400">{{ $log->ip_address }}</span>
                        <span class="ui-muted truncate" style="max-width: 140px;" title="{{ $log->user_agent }}">{{ $log->user_agent }}</span>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-400 ui-text">Belum ada log aktivitas.</td>
            </tr>
            @endforelse
        </x-table>

        @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/10">
                {{ $logs->links('components.pagination-links') }}
            </div>
        @endif
    </x-card>

</x-layout>


