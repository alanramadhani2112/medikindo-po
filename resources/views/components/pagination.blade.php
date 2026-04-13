@if ($paginator->hasPages())
<ul class="pagination">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <li class="page-item previous disabled">
            <span class="page-link"><i class="previous"></i></span>
        </li>
    @else
        <li class="page-item previous">
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}"><i class="previous"></i></a>
        </li>
    @endif

    {{-- Pages --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <li class="page-item next">
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}"><i class="next"></i></a>
        </li>
    @else
        <li class="page-item next disabled">
            <span class="page-link"><i class="next"></i></span>
        </li>
    @endif
</ul>
@endif
