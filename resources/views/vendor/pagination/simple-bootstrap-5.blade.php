@if ($paginator->hasPages())
    <nav role="navigation" class="d-flex justify-content-end w-100 pt-1"
        aria-label="@lang('pagination.navigation')">
        <ul class="pagination pagination-primary pagination-sm pagination-rounded mb-0">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="icon-base ti tabler-chevron-left"></i>
                        <span>@lang('pagination.previous')</span>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link d-inline-flex align-items-center justify-content-center gap-1"
                        href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="icon-base ti tabler-chevron-left"></i>
                        <span>@lang('pagination.previous')</span>
                    </a>
                </li>
            @endif

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link d-inline-flex align-items-center justify-content-center gap-1"
                        href="{{ $paginator->nextPageUrl() }}" rel="next">
                        <span>@lang('pagination.next')</span>
                        <i class="icon-base ti tabler-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link d-inline-flex align-items-center justify-content-center gap-1">
                        <span>@lang('pagination.next')</span>
                        <i class="icon-base ti tabler-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
