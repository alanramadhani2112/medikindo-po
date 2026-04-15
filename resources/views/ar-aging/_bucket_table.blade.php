<div class="table-responsive">
    <table class="table table-row-bordered table-row-gray-200 align-middle gs-0 gy-3 mb-0">
        <thead class="bg-light">
            <tr class="fw-bold text-muted fs-7 text-uppercase">
                <th class="ps-5 min-w-150px">No. Invoice</th>
                <th class="min-w-180px">RS/Klinik</th>
                <th class="min-w-110px">Jatuh Tempo</th>
                <th class="text-end min-w-150px">Outstanding</th>
                <th class="text-center min-w-120px pe-5">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
                @php
                    $outstanding = (float) $invoice->total_amount - (float) $invoice->paid_amount;
                    $daysOverdue = now()->startOfDay()->diffInDays($invoice->due_date, false);
                    $daysOverdue = -$daysOverdue; // positive = overdue
                @endphp
                <tr>
                    <td class="ps-5">
                        <a href="{{ route('web.invoices.customer.show', $invoice) }}"
                           class="fw-bold text-gray-900 text-hover-primary">
                            {{ $invoice->invoice_number }}
                        </a>
                    </td>
                    <td>
                        <span class="text-gray-700 fw-semibold">{{ $invoice->organization?->name ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="fw-semibold {{ $daysOverdue > 0 ? 'text-danger' : 'text-gray-700' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                        </span>
                        @if($daysOverdue > 0)
                            <div class="text-danger fs-8">{{ $daysOverdue }} hari lewat</div>
                        @elseif($daysOverdue < 0)
                            <div class="text-success fs-8">{{ abs($daysOverdue) }} hari lagi</div>
                        @endif
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-gray-900">Rp {{ number_format($outstanding, 0, ',', '.') }}</span>
                    </td>
                    <td class="text-center pe-5">
                        @php
                            $statusBadge = match($invoice->status) {
                                'draft'        => 'badge-secondary',
                                'issued'       => 'badge-warning',
                                'partial_paid' => 'badge-info',
                                default        => 'badge-secondary',
                            };
                        @endphp
                        <span class="badge {{ $statusBadge }}">{{ strtoupper(str_replace('_', ' ', $invoice->status)) }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
