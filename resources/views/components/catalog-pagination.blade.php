@props(['items'])

@if ($items->hasPages())
    <nav aria-label="Страницы списка" class="catalog-pagination">
        @if ($items->previousPageUrl())
            <a href="{{ $items->previousPageUrl() }}">Назад</a>
        @endif
        <span>{{ $items->currentPage() }} из {{ $items->lastPage() }}</span>
        @if ($items->nextPageUrl())
            <a href="{{ $items->nextPageUrl() }}">Далее</a>
        @endif
    </nav>
@endif
