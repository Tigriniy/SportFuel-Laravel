@props(['mobile' => false])

<form class="{{ $mobile ? 'header__mobile-search' : 'header__search' }}" method="GET"
      action="{{ route('catalog') }}" role="search">
    <input type="search" name="q" maxlength="100" aria-label="Название товара" placeholder="Поиск"
           class="{{ $mobile ? '' : 'header__search-input' }}"
           value="{{ request()->routeIs('catalog') ? request()->query('q') : '' }}">
    <button class="header__search-submit" type="submit" aria-label="Найти товар">
        <img src="{{ asset('assets/images/systemImg/search.svg') }}" alt="">
    </button>
</form>
