@props([
    'action' => '#',
    'method' => 'GET',
])

<div class="card card-flush mb-7">
    <div class="card-body">
        <form action="{{ $action }}" method="{{ $method }}" class="row g-4">
            @if(strtoupper($method) !== 'GET')
                @csrf
                @method($method)
            @endif
            
            {{ $filters ?? $slot }}
            
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-chart
 fs-3"></i>
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'filter']))
                    <a href="{{ $action }}" class="btn btn-light">
                        <i class="ki-outline ki-arrow-zigzag fs-3"></i>
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
