{{-- Pagination compacte des listes d'administration. Paramètre : $pages (LengthAwarePaginator). --}}
@if($pages->hasPages())
    <div class="adm-pagination">
        <span>
            <strong>{{ $pages->firstItem() }}–{{ $pages->lastItem() }}</strong>
            sur <strong>{{ $pages->total() }}</strong>
        </span>
        <div class="adm-pagination__nav">
            @if($pages->onFirstPage())
                <span class="adm-icon-btn adm-icon-btn--off"><x-icon name="chevron-left" size="sm" /></span>
            @else
                <a href="{{ $pages->previousPageUrl() }}" class="adm-icon-btn" title="Page précédente"><x-icon name="chevron-left" size="sm" /></a>
            @endif
            <span>Page {{ $pages->currentPage() }} / {{ $pages->lastPage() }}</span>
            @if($pages->hasMorePages())
                <a href="{{ $pages->nextPageUrl() }}" class="adm-icon-btn" title="Page suivante"><x-icon name="chevron-right" size="sm" /></a>
            @else
                <span class="adm-icon-btn adm-icon-btn--off"><x-icon name="chevron-right" size="sm" /></span>
            @endif
        </div>
    </div>
@endif
