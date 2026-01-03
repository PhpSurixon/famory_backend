<!DOCTYPE html>
<html>

<head>
    <title>Page Title</title>
</head>

<body>

</body>



@if ($paginator->hasPages())
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="Page navigation example">
                <ul class="pagination" style="justify-content: center;">
                    @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">Previous</span>
                    </li>
                    <li class="page-item active">
                        <span class="page-link">1</span>
                    </li>
                    @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                    </li>
                    @endif

                    @if ($paginator->lastPage() > 5)
                    @if ($paginator->currentPage() > 6)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($paginator->currentPage() - 4) }}">
                            {{ $paginator->currentPage() - 4 }}
                        </a>
                    </li>
                    @endif
                    @for ($i = max($paginator->currentPage() - 3, 2); $i <= min($paginator->currentPage() + 4, $paginator->lastPage() - 1); $i++)
                        <li class="page-item @if ($i === $paginator->currentPage()) active @endif">
                            <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                        @endfor

                        @if ($paginator->currentPage() < $paginator->lastPage() - 3)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                            @endif
                            <li class="page-item @if ($paginator->currentPage() === $paginator->lastPage()) active @endif">
                                <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
                            </li>
                            @else
                            @for ($i = 2; $i <= $paginator->lastPage(); $i++)
                                <li class="page-item @if ($i === $paginator->currentPage()) active @endif">
                                    <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
                                </li>
                                @endfor
                                @endif

                                @if ($paginator->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
                                </li>
                                @else
                                <li class="page-item disabled">
                                    <span class="page-link">Next</span>
                                </li>
                                @endif
                </ul>
            </nav>
        </div>
    </div>
</div>
@endif


</html>