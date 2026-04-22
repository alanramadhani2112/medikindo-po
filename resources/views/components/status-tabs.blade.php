{{--
    Reusable Status Tabs Component

    Usage:
    <x-status-tabs :tabs="$tabOptions" :current="$tab" route="web.po.index" :counts="$counts" />

    Props:
    - tabs    : array ['key' => ['label' => '...', 'icon' => 'icon-name']]
                icon = ki-outline icon name WITHOUT 'ki-' prefix (e.g. 'home', 'document')
    - current : current active tab key ('' or 'all' = show all)
    - route   : named route string
    - counts  : array ['key' => count] for badge numbers
    - param   : query param name (default: 'tab')
--}}

@props([
    'tabs'    => [],
    'current' => '',
    'route'   => '',
    'counts'  => [],
    'param'   => 'tab',
])

@foreach($tabs as $key => $tabData)
    @php
        $isAll    = ($key === '' || $key === 'all');
        $isActive = ($current === $key)
                 || ($isAll && ($current === '' || $current === 'all' || $current === null));
        $count    = $counts[$key] ?? null;

        // Build href — preserve existing query params except tab/page
        $paramValue = $isAll ? null : $key;
        $href = route($route, array_merge(
            request()->except([$param, 'page']),
            [$param => $paramValue]
        ));
    @endphp

    <li class="nav-item">
        <a class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}"
           href="{{ $href }}">
            <i class="ki-outline ki-{{ $tabData['icon'] }} fs-4 me-2"></i>
            <span class="fs-6 fw-bold me-2">{{ $tabData['label'] }}</span>
            @if(! $isAll && $count !== null)
                <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-1">
                    {{ $count }}
                </span>
            @endif
        </a>
    </li>
@endforeach
