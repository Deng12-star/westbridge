@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-4 border-t border-paper-200 pt-5" aria-label="Pagination">
        <div class="text-small text-paper-600">
            Showing <span class="tabular">{{ $paginator->firstItem() }}</span>–<span class="tabular">{{ $paginator->lastItem() }}</span>
            of <span class="tabular">{{ $paginator->total() }}</span>
        </div>
        <div class="flex gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-10 items-center rounded-xs border border-paper-200 px-4 font-display text-small text-paper-400">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-10 items-center rounded-xs border border-paper-300 px-4 font-display text-small text-navy-700 transition-colors hover:bg-paper-100">Previous</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-10 items-center rounded-xs border border-paper-300 px-4 font-display text-small text-navy-700 transition-colors hover:bg-paper-100">Next</a>
            @else
                <span class="inline-flex h-10 items-center rounded-xs border border-paper-200 px-4 font-display text-small text-paper-400">Next</span>
            @endif
        </div>
    </nav>
@endif
