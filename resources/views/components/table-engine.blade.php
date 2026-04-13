@props([
    'columns' => [],
    'filters' => [],
    'actions' => [],
    'bulkActions' => [],
    'data' => null,
    'emptyState' => [],
    'styling' => [],
    'perPageOptions' => [10, 25, 50, 100],
    'searchable' => false,
    'exportable' => false,
    'ajax' => false,
    'request' => null,
])

{{-- Filter Bar --}}
@if(!empty($filters))
<div class="card card-flush mb-7">
    <div class="card-body">
        <form action="{{ $request->url() }}" method="GET" class="row g-4">
            @foreach($filters as $filter)
                @php
                    $type = $filter['type'] ?? 'text';
                    $name = $filter['name'] ?? '';
                    $label = $filter['label'] ?? '';
                    $placeholder = $filter['placeholder'] ?? '';
                    $value = $request->get($name, '');
                @endphp

                @if($type === 'search')
                    <div class="col-md-4">
                        <input 
                            type="text" 
                            name="{{ $name }}" 
                            value="{{ $value }}" 
                            placeholder="{{ $placeholder }}" 
                            class="form-control form-control-solid"
                        />
                    </div>
                @elseif($type === 'select')
                    <div class="col-md-3">
                        <select name="{{ $name }}" class="form-select form-select-solid">
                            @foreach($filter['options'] ?? [] as $optValue => $optLabel)
                                <option value="{{ $optValue }}" {{ $value == $optValue ? 'selected' : '' }}>
                                    {{ $optLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @elseif($type === 'date')
                    <div class="col-md-3">
                        @if($label)
                        <label class="form-label">{{ $label }}</label>
                        @endif
                        <input 
                            type="date" 
                            name="{{ $name }}" 
                            value="{{ $value }}" 
                            class="form-control form-control-solid"
                        />
                    </div>
                @endif
            @endforeach

            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-magnifier fs-3"></i>
                    Cari
                </button>
                @if($request->hasAny(array_column($filters, 'name')))
                    <a href="{{ $request->url() }}" class="btn btn-light">
                        <i class="ki-outline ki-cross fs-3"></i>
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
@endif

{{-- Table Card --}}
<div class="card card-flush">
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="{{ $styling['table_class'] ?? 'table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4' }}">
                {{-- Table Header --}}
                <thead>
                    <tr class="{{ $styling['header_class'] ?? 'fw-bold text-muted' }}">
                        @if(!empty($bulkActions))
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#table_engine tbody .form-check-input" />
                                </div>
                            </th>
                        @endif

                        @foreach($columns as $column)
                            @php
                                $key = $column['key'] ?? '';
                                $label = $column['label'] ?? '';
                                $sortable = $column['sortable'] ?? false;
                                $width = $column['width'] ?? '';
                                $align = $column['align'] ?? 'left';
                                $currentSort = $request->get('sort');
                                $currentDirection = $request->get('direction', 'asc');
                                $isSorted = $currentSort === $key;
                                $nextDirection = $isSorted && $currentDirection === 'asc' ? 'desc' : 'asc';
                            @endphp

                            <th class="{{ $width }} text-{{ $align }}">
                                @if($sortable)
                                    <a href="{{ $request->fullUrlWithQuery(['sort' => $key, 'direction' => $nextDirection]) }}" 
                                       class="text-muted text-hover-primary">
                                        {{ $label }}
                                        @if($isSorted)
                                            <i class="ki-outline ki-{{ $currentDirection === 'asc' ? 'up' : 'down' }} fs-7 ms-1"></i>
                                        @endif
                                    </a>
                                @else
                                    {{ $label }}
                                @endif
                            </th>
                        @endforeach

                        @if(!empty($actions))
                            <th class="min-w-100px text-end">Aksi</th>
                        @endif
                    </tr>
                </thead>

                {{-- Table Body --}}
                <tbody>
                    @forelse($data as $row)
                        <tr>
                            @if(!empty($bulkActions))
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="{{ $row->id }}" />
                                    </div>
                                </td>
                            @endif

                            @foreach($columns as $column)
                                @php
                                    $key = $column['key'] ?? '';
                                    $type = $column['type'] ?? 'text';
                                    $align = $column['align'] ?? 'left';
                                    $render = $column['render'] ?? null;
                                    
                                    // Get value (support nested keys like 'user.name')
                                    $value = data_get($row, $key);
                                @endphp

                                <td class="text-{{ $align }}">
                                    @if($render && is_callable($render))
                                        {!! $render($row) !!}
                                    @elseif($type === 'badge')
                                        @php
                                            $variants = $column['variants'] ?? [];
                                            $variant = $variants[$value] ?? 'primary';
                                        @endphp
                                        <x-badge variant="{{ $variant }}">{{ strtoupper($value) }}</x-badge>
                                    @elseif($type === 'date')
                                        @php
                                            $format = $column['format'] ?? 'd M Y';
                                        @endphp
                                        <span class="text-gray-800 fw-semibold fs-7">
                                            {{ $value ? $value->format($format) : '-' }}
                                        </span>
                                    @elseif($type === 'currency')
                                        @php
                                            $currency = $column['currency'] ?? 'IDR';
                                        @endphp
                                        <span class="text-gray-900 fw-bold d-block fs-6">
                                            {{ $currency === 'IDR' ? 'Rp ' . number_format($value, 0, ',', '.') : $value }}
                                        </span>
                                    @elseif($type === 'boolean')
                                        @php
                                            $trueLabel = $column['true_label'] ?? 'Yes';
                                            $falseLabel = $column['false_label'] ?? 'No';
                                        @endphp
                                        <x-badge variant="{{ $value ? 'success' : 'secondary' }}">
                                            {{ $value ? $trueLabel : $falseLabel }}
                                        </x-badge>
                                    @elseif($type === 'image')
                                        @php
                                            $width = $column['width'] ?? 40;
                                            $height = $column['height'] ?? 40;
                                            $rounded = $column['rounded'] ?? false;
                                        @endphp
                                        <img src="{{ $value }}" 
                                             alt="" 
                                             width="{{ $width }}" 
                                             height="{{ $height }}"
                                             class="{{ $rounded ? 'rounded-circle' : '' }}" />
                                    @else
                                        <span class="text-gray-900 fw-bold d-block fs-6">{{ $value ?? '-' }}</span>
                                    @endif
                                </td>
                            @endforeach

                            @if(!empty($actions))
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @foreach($actions as $action)
                                            @php
                                                $label = $action['label'] ?? '';
                                                $route = $action['route'] ?? '';
                                                $icon = $action['icon'] ?? '';
                                                $variant = $action['variant'] ?? 'light-primary';
                                                $size = $action['size'] ?? 'sm';
                                                $method = $action['method'] ?? 'GET';
                                                $confirm = $action['confirm'] ?? false;
                                                $confirmMessage = $action['confirm_message'] ?? 'Are you sure?';
                                                $can = $action['can'] ?? null;
                                                $visible = $action['visible'] ?? null;
                                                
                                                // Check visibility
                                                if ($visible && is_callable($visible) && !$visible($row)) {
                                                    continue;
                                                }
                                                
                                                // Check permission
                                                if ($can && !auth()->user()->can($can)) {
                                                    continue;
                                                }
                                                
                                                $url = route($route, $row);
                                            @endphp

                                            @if($method === 'GET')
                                                <x-button 
                                                    variant="{{ $variant }}" 
                                                    size="{{ $size }}" 
                                                    icon="{{ $icon }}"
                                                    href="{{ $url }}"
                                                >
                                                    {{ $label }}
                                                </x-button>
                                            @else
                                                <form method="POST" action="{{ $url }}" 
                                                      @if($confirm) onsubmit="return confirm('{{ $confirmMessage }}')" @endif>
                                                    @csrf
                                                    @method($method)
                                                    <x-button 
                                                        type="submit"
                                                        variant="{{ $variant }}" 
                                                        size="{{ $size }}" 
                                                        icon="{{ $icon }}"
                                                    >
                                                        {{ $label }}
                                                    </x-button>
                                                </form>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) + (!empty($actions) ? 1 : 0) + (!empty($bulkActions) ? 1 : 0) }}">
                                <x-empty-state 
                                    icon="{{ $emptyState['icon'] ?? 'file-deleted' }}"
                                    title="{{ $emptyState['title'] ?? 'Tidak Ada Data' }}"
                                    message="{{ $emptyState['message'] ?? 'Tidak ada data yang ditemukan.' }}"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($data && $data->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-5">
            <div class="text-gray-600 fs-7">
                Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }} data
            </div>
            <div>
                {{ $data->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
