@props([
    'action' => '#',
    'method' => 'GET',
])

<div class="card mb-5">
    <div class="card-body">
        <form action="{{ $action }}" method="{{ $method }}" class="d-flex flex-wrap gap-3">
            @if(strtoupper($method) !== 'GET')
                @csrf
                @method($method)
            @endif
            
            {{ $filters ?? $slot }}
            
            <button type="submit" class="btn btn-light-primary">
                <i class="ki-outline ki-chart fs-2"></i>
                Filter
            </button>
            
            @if(request()->hasAny(['search', 'status', 'type', 'organization', 'date_from', 'date_to']))
                <a href="{{ $action }}" class="btn btn-light">
                    <i class="ki-outline ki-arrow-zigzag fs-2"></i>
                    Reset
                </a>
            @endif
        </form>
    </div>
</div>
