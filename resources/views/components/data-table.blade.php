@props([
    'headers' => [],
    'data' => null,
    'pagination' => false,
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
                    @if($data && $data->count() > 0)
                        @foreach($data as $item)
                            {{ $row }}
                        @endforeach
                    @else
                        @if(isset($empty))
                            {{ $empty }}
                        @else
                            <tr>
                                <td colspan="{{ count($headers) }}">
                                    <x-empty-state 
                                        icon="file-deleted"
                                        title="Tidak Ada Data"
                                        message="Tidak ada data yang ditemukan"
                                    />
                                </td>
                            </tr>
                        @endif
                    @endif
                </tbody>
            </table>
        </div>
        
        @if($pagination && $data && $data->hasPages())
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
