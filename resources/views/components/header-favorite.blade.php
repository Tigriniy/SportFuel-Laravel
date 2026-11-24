@props(['mobile' => false])

@php
    if (! request()->attributes->has('favorite_count')) {
        request()->attributes->set('favorite_count', (int) (auth()->user()?->visibleFavorites()->count() ?? 0));
    }
    $favoriteCount = request()->attributes->get('favorite_count');
@endphp

<a href="{{ route('favorites.index') }}" class="{{ $mobile ? 'header__mobile-icon' : 'header__icon-link' }} header__favorite"
   aria-label="Избранное{{ $favoriteCount ? ', количество товаров: '.$favoriteCount : '' }}">
    <img src="{{ asset('assets/images/systemImg/favorite.svg') }}" alt="">
    @if ($favoriteCount)
        <span class="header__favorite-count" aria-hidden="true">{{ $favoriteCount > 99 ? '99+' : $favoriteCount }}</span>
    @endif
</a>
