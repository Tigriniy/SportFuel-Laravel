<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог — SportFuel</title>
    <link rel="stylesheet" href="/assets/css/header-footer.css">
    <link rel="stylesheet" href="/assets/css/index.css">
    <link rel="stylesheet" href="/assets/css/katalog.css">
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,100..900;1,100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>
<body>

<!-- === HEADER === -->
<header class="header">
    <div class="header__inner">
        <a href="index.html" class="header__logo">
            <img src="/assets/images/systemImg/logo.svg" alt="SportFuel">
        </a>

        <input type="checkbox" id="burger" class="header__burger-checkbox" aria-label="Открыть меню">
        <label for="burger" class="header__overlay"></label>
        <label for="burger" class="header__burger-button">
            <span class="header__burger-line"></span>
            <span class="header__burger-line"></span>
            <span class="header__burger-line"></span>
        </label>

        <nav class="header__nav">
            <a href="index.html" class="header__nav-link">Главная</a>
            <a href="katalog.html" class="header__nav-link">Каталог</a>
            <a href="payment_and_delivery.html" class="header__nav-link">Доставка</a>
            <a href="order.html" class="header__nav-link">Контакты</a>

            <x-header-search />

            <a href="profile.html" class="header__icon-link">
                <img src="/assets/images/systemImg/profile.svg" alt="Профиль">
            </a>
            <x-header-favorite />
            <x-header-cart />
        </nav>

        <div class="header__mobile-menu">
            <div class="header__mobile-inner">
                <a href="index.html" class="header__mobile-link">
                    <img src="/assets/images/systemImg/home.svg" alt="Главная">
                    <span>Главная</span>
                </a>
                <a href="katalog.html" class="header__mobile-link">
                    <img src="/assets/images/systemImg/list.svg" alt="Каталог">
                    <span>Каталог</span>
                </a>
                <a href="payment_and_delivery.html" class="header__mobile-link">
                    <img src="/assets/images/systemImg/truck.svg" alt="Доставка">
                    <span>Доставка</span>
                </a>
                <a href="order.html" class="header__mobile-link">
                    <img src="/assets/images/systemImg/phone.svg" alt="Контакты">
                    <span>Контакты</span>
                </a>

                <div class="header__mobile-controls">
                    <x-header-search mobile />
                    <div class="header__mobile-icons">
                        <a href="reg-in.html" class="header__mobile-icon">
                            <img src="/assets/images/systemImg/profile.svg" alt="Профиль">
                        </a>
                        <x-header-favorite mobile />
                        <x-header-cart mobile />
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<main class="main">
    <div class="main__container">
        <section class="catalog">
            <h1 class="catalog__title">Каталог товара</h1>

            <div class="catalog__layout">

                <aside class="catalog-filter" aria-label="Фильтры каталога">
                    <form method="GET" action="{{ route('catalog') }}">
                        @if ($errors->any())
                            <ul class="catalog-filter__errors" role="alert">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="catalog-filter__section">
                            <label class="catalog-filter__section-title" for="catalog-q">Название товара</label>
                            <input class="catalog-filter__input" id="catalog-q" type="search" name="q"
                                   placeholder="Например, креатин" maxlength="100" value="{{ $filters['q'] ?? '' }}">
                        </div>

                        <fieldset class="catalog-filter__section">
                            <legend class="catalog-filter__section-title">Цена, ₽</legend>
                            <div class="catalog-filter__prices">
                                <label for="catalog-min-price">От
                                    <input class="catalog-filter__input" id="catalog-min-price" type="number" name="min_price"
                                           min="0" max="99999999.99" step="0.01" value="{{ $filters['min_price'] ?? '' }}">
                                </label>
                                <label for="catalog-max-price">До
                                    <input class="catalog-filter__input" id="catalog-max-price" type="number" name="max_price"
                                           min="0" max="99999999.99" step="0.01" value="{{ $filters['max_price'] ?? '' }}">
                                </label>
                            </div>
                        </fieldset>

                        @foreach (['category' => ['Категории', $categories], 'brand' => ['Бренды', $brands]] as $field => [$title, $options])
                            @if ($options->isNotEmpty())
                                <fieldset class="catalog-filter__section">
                                    <legend class="catalog-filter__section-title">{{ $title }}</legend>
                                    <ul class="catalog-filter__list">
                                        @foreach ($options as $option)
                                            <li class="catalog-filter__item">
                                                <label>
                                                    <input type="checkbox" name="{{ $field }}[]" value="{{ $option->id }}"
                                                           @checked(in_array($option->id, $filters[$field] ?? []))>
                                                    {{ $option->name }}
                                                </label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </fieldset>
                            @endif
                        @endforeach

                        <div class="catalog-filter__section">
                            <label class="catalog-filter__section-title" for="catalog-sort">Сортировка</label>
                            <select class="catalog-filter__input" id="catalog-sort" name="sort">
                                @foreach ($sortOptions as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['sort'] ?? 'newest') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button class="catalog-filter__apply" type="submit">Применить</button>
                        <a href="{{ route('catalog') }}" class="catalog-filter__reset">Сбросить фильтры</a>
                    </form>
                </aside>

                <div class="catalog__results">
                    <p class="catalog-filter__results">Найдено товаров: {{ $products->total() }}</p>
                    <x-cart-errors />
                    <div class="catalog__grid">
                        @forelse ($products as $product)
                            <x-product-tile :product="$product" />
                        @empty
                            @if (array_intersect_key($filters, array_flip(['q', 'category', 'brand', 'min_price', 'max_price'])))
                                <p class="catalog__empty">
                                    По этим параметрам товары не найдены.
                                    <a href="{{ route('catalog') }}">Очистить фильтры</a>
                                </p>
                            @else
                                <p class="catalog__empty">В каталоге пока нет товаров.</p>
                            @endif
                        @endforelse
                    </div>
                    <x-catalog-pagination :items="$products" />
                </div>
            </div>
        </section>
    </div>
</main>

<!-- === FOOTER === -->
<footer class="footer">
    <div class="footer__inner">
        <div class="footer__column">
            <img src="/assets/images/systemImg/logo.svg" alt="SportFuel" class="footer__logo">
        </div>
        <div class="footer__column">
            <h3 class="footer__title">Разделы сайта</h3>
            <a href="index.html" class="footer__link">Главная</a>
            <a href="katalog.html" class="footer__link">Каталог</a>
            <a href="payment_and_delivery.html" class="footer__link">Доставка</a>
            <a href="order.html" class="footer__link">Контакты</a>
        </div>
        <div class="footer__column">
            <h3 class="footer__title">Контактная информация</h3>
            <a href="tel:+79402389143" class="footer__link">Телефон: +7 (940) 238-91-43</a>
            <a href="mailto:info@sportfuel.ru" class="footer__link">Email: info@sportfuel.ru</a>
            <a href="#" class="footer__link">Мы в интернете</a>
            <div class="footer__socials">
                <img src="/assets/images/systemImg/Сферум_Logo.svg" alt="Сферум">
                <img src="/assets/images/systemImg/Max_logo.svg" alt="Max">
                <img src="/assets/images/systemImg/Rutube.svg" alt="Rutube">
                <img src="/assets/images/systemImg/VK_Logo 1.svg" alt="VK">
            </div>
        </div>
        <div class="footer__column">
            <h3 class="footer__title">Подпишись на нашу рассылку</h3>
            <div class="footer__subscribe">
                <input type="email" placeholder="Электронная почта">
                <button>Подписаться</button>
            </div>
            <p class="footer__subscribe-text">Ты всегда будешь в курсе новинок и выгодных предложений!</p>
        </div>
    </div>
    <div class="footer__copyright">©2025 SportFuel. Все права защищены.</div>
</footer>

</body>
</html>