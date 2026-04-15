@props([
    'title'             => null,
    'tabs'              => [],
    'activeTab'         => null,
    'tabParam'          => 'tab',
    'searchPlaceholder' => 'Filter data...',
    'columns'           => [],
    'data'              => null,
])
<div class="card">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <!--begin::Card title-->
        <div class="card-title flex-column gap-0">
            @if($title)
                <h2 class="fw-bold mb-0">{{ $title }}</h2>
            @endif
            <!--begin::Tabs-->
            @if(count($tabs) > 0)
            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-7 fw-bold mt-2">
                @foreach($tabs as $val => $tabData)
                    @php
                        $valStr = (string)$val;
                        $currentStr = (string)request($tabParam, $activeTab);
                        $isActive = $currentStr === $valStr;
                    @endphp
                    <li class="nav-item mt-2">
                        <a href="{{ request()->fullUrlWithQuery([$tabParam => $val === '' ? null : $val, 'page' => null]) }}"
                           class="nav-link text-active-primary ms-0 me-6 py-1 {{ $isActive ? 'active' : '' }}">
                            @if(isset($tabData['icon']))<i class="{{ str_replace('ki-', 'ki-outline ki-', $tabData['icon']) }} fs-4 me-1"></i>@endif
                            {{ $tabData['label'] }}
                            @if(isset($tabData['count']) && $tabData['count'] > 0)
                                <span class="badge badge-circle badge-light-primary ms-2 fs-8">{{ $tabData['count'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
            @endif
            <!--end::Tabs-->
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                <!--begin::Search form-->
                <form method="GET" action="{{ request()->url() }}" class="d-flex align-items-center gap-2">
                    @foreach(request()->except(['search','page',$tabParam]) as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $v)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    @if(count($tabs) > 0)
                        <input type="hidden" name="{{ $tabParam }}" value="{{ request($tabParam, $activeTab) }}">
                    @endif

                    <div class="d-flex align-items-center position-relative">
                        <i class="ki-outline ki-chart
 fs-3 position-absolute ms-4"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control form-control-solid ps-12 w-250px"
                               placeholder="{{ $searchPlaceholder }}" />
                    </div>

                    @isset($filters){{ $filters }}@endisset

                    @php
                        $hasSearch = request()->filled('search');
                        $otherFilters = collect(request()->all())->except(['search','page',$tabParam])->filter()->isNotEmpty();
                        $isFiltered = $hasSearch || $otherFilters;
                    @endphp
                    @if($isFiltered)
                        <a href="{{ request()->url() . (request()->has($tabParam) ? '?'.$tabParam.'='.request($tabParam) : '') }}"
                           class="btn btn-light-danger btn-sm">
                            <i class="ki-outline ki-arrow-zigzag fs-4 me-1"></i>Reset
                        </a>
                    @endif

                    <button type="submit" class="d-none">Search</button>
                </form>
                <!--end::Search form-->

                @isset($actions)
                <div class="d-flex align-items-center gap-2">{{ $actions }}</div>
                @endisset
            </div>
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body py-4">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        @foreach($columns as $col)
                            @php
                                $label = is_array($col) ? ($col['label'] ?? '') : $col;
                                $class = is_array($col) ? ($col['class'] ?? '') : '';
                            @endphp
                            <th class="{{ $class }}">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    </div>
    <!--end::Card body-->
    <!--begin::Card footer - Pagination-->
    @if($data && method_exists($data, 'links'))
    <div class="card-footer d-flex align-items-center justify-content-between flex-wrap">
        <div class="text-muted fw-semibold fs-7">
            Menampilkan {{ $data->firstItem() ?? 0 }} - {{ $data->lastItem() ?? 0 }}
            dari {{ $data->total() }} data
        </div>
        <div>
            {{ $data->withQueryString()->links('components.pagination') }}
        </div>
    </div>
    @endif
    <!--end::Card footer-->
</div>
