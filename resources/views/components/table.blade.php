@props([
    'headers' => [],
])

<div class="card card-flush">
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                @if(!empty($headers))
                <thead>
                    <tr class="fw-bold text-muted">
                        @foreach($headers as $header)
                            @if(is_array($header))
                                <th class="{{ $header['class'] ?? '' }}">{{ $header['label'] }}</th>
                            @else
                                <th>{{ $header }}</th>
                            @endif
                        @endforeach
                    </tr>
                </thead>
                @endif
                <tbody>
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    </div>
</div>
