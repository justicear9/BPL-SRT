@if ($paginator->hasPages())
    <nav class="d-flex flex-wrap align-items-center justify-content-between gap-3 w-100 pt-1"
        aria-label="@lang('pagination.navigation')">
        {{-- Mobile --}}
        <div class="w-100 d-sm-none">
            <ul class="pagination pagination-primary pagination-sm pagination-rounded mb-0 w-100 justify-content-between flex-nowrap gap-2">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled flex-fill text-center" aria-disabled="true">
                        <span class="page-link d-inline-flex align-items-center justify-content-center gap-1">
                            <i class="icon-base ti tabler-chevron-left"></i>
                            <span>@lang('pagination.previous')</span>
                        </span>
                    </li>
                @else
                    <li class="page-item flex-fill text-center">
                        <a class="page-link d-inline-flex align-items-center justify-content-center gap-1"
                            href="{{ $paginator->previousPageUrl() }}" rel="prev">
                            <i class="icon-base ti tabler-chevron-left"></i>
                            <span>@lang('pagination.previous')</span>
                        </a>
                    </li>
                @endif

                @if ($paginator->hasMorePages())
                    <li class="page-item flex-fill text-center">
                        <a class="page-link d-inline-flex align-items-center justify-content-center gap-1"
                            href="{{ $paginator->nextPageUrl() }}" rel="next">
                            <span>@lang('pagination.next')</span>
                            <i class="icon-base ti tabler-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled flex-fill text-center" aria-disabled="true">
                        <span class="page-link d-inline-flex align-items-center justify-content-center gap-1">
                            <span>@lang('pagination.next')</span>
                            <i class="icon-base ti tabler-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </div>

        {{-- Tablet and up --}}
        <div class="d-none d-sm-flex w-100 align-items-center justify-content-between gap-3 flex-wrap">
            <p class="small text-body-secondary mb-0">
                @lang('pagination.showing')
                <span class="fw-semibold text-heading">{{ $paginator->firstItem() }}</span>
                @lang('pagination.to')
                <span class="fw-semibold text-heading">{{ $paginator->lastItem() }}</span>
                @lang('pagination.of')
                <span class="fw-semibold text-heading">{{ $paginator->total() }}</span>
                @lang('pagination.results')
            </p>

            <ul class="pagination pagination-primary pagination-sm pagination-rounded mb-0">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <span class="page-link d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                            <i class="icon-base ti tabler-chevron-left"></i>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link d-inline-flex align-items-center justify-content-center"
                            href="{{ $paginator->previousPageUrl() }}" rel="prev"
                            aria-label="@lang('pagination.previous')">
                            <i class="icon-base ti tabler-chevron-left"></i>
                        </a>
                    </li>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link d-inline-flex align-items-center justify-content-center"
                            href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                            <i class="icon-base ti tabler-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                        <span class="page-link d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                            <i class="icon-base ti tabler-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
