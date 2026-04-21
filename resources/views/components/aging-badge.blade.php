@props(['invoice'])

@php
    $bucket = $invoice->aging_bucket;
    $days   = $invoice->days_overdue;

    $config = [
        'current' => ['label' => 'On Time',       'class' => 'badge-light-success'],
        '1-30'    => ['label' => '1–30 hr',        'class' => 'badge-light-warning'],
        '31-60'   => ['label' => '31–60 hr',       'class' => 'badge-light-danger'],
        '61-90'   => ['label' => '61–90 hr',       'class' => 'badge-danger'],
        '90+'     => ['label' => '>90 hr',         'class' => 'badge-light-danger fw-bolder'],
    ];

    $cfg = $config[$bucket] ?? $config['current'];
@endphp

@if(!in_array($invoice->status->value, ['paid', 'void', 'draft']))
    <span class="badge {{ $cfg['class'] }} fw-bold">{{ $cfg['label'] }}</span>
    @if($days > 0)
        <div class="text-danger fs-9 mt-1">+{{ $days }} hari</div>
    @endif
@else
    <span class="text-muted">—</span>
@endif
