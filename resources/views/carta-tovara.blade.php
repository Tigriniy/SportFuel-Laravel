<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $product->name }} | SportFuel</title>
  <link rel="stylesheet" href="/assets/css/conf.css">
  <link rel="stylesheet" href="/assets/css/header-footer.css">
  <link rel="stylesheet" href="/assets/css/carta-tovara.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>
<body>

<!-- === HEADER ===== -->
<header class="header">
  <div class="header__inner">
    <a href="{{ url('index.html') }}" class="header__logo">
      <img src="/assets/images/systemImg/logo.svg" alt="SportFuel">
    </a>

    <input type="checkbox" id="burger" class="header__burger-checkbox">
    <label for="burger" class="header__overlay"></label>
    <label for="burger" class="header__burger-button">
      <span class="header__burger-line"></span>
      <span class="header__burger-line"></span>
      <span class="header__burger-line"></span>
    </label>

    <nav class="header__nav">
      <a href="{{ url('index.html') }}" class="header__nav-link">Главная</a>
      <a href="{{ url('katalog.html') }}" class="header__nav-link">Каталог</a>
      <a href="{{ url('payment_and_delivery.html') }}" class="header__nav-link">Доставка</a>
      <a href="{{ url('order.html') }}" class="header__nav-link">Контакты</a>

      <x-header-search />

      <a href="{{ url('profile.html') }}" class="header__icon-link">
        <img src="/assets/images/systemImg/profile.svg" alt="Профиль">
      </a>
      <a href="{{ url('favorite.html') }}" class="header__icon-link">
        <img src="/assets/images/systemImg/favorite.svg" alt="Избранное">
      </a>
      <x-header-cart />
    </nav>

    <div class="header__mobile-menu">
      <div class="header__mobile-inner">
        <a href="{{ url('index.html') }}" class="header__mobile-link">
          <img src="/assets/images/systemImg/home.svg" alt="Главная">
          <span>Главная</span>
        </a>
        <a href="{{ url('katalog.html') }}" class="header__mobile-link">
          <img src="/assets/images/systemImg/list.svg" alt="Каталог">
          <span>Каталог</span>
        </a>
        <a href="{{ url('payment_and_delivery.html') }}" class="header__mobile-link">
          <img src="/assets/images/systemImg/truck.svg" alt="Доставка">
          <span>Доставка</span>
        </a>
        <a href="{{ url('order.html') }}" class="header__mobile-link">
          <img src="/assets/images/systemImg/phone.svg" alt="Контакты">
          <span>Контакты</span>
        </a>

        <div class="header__mobile-controls">
          <x-header-search mobile />
          <div class="header__mobile-icons">
            <a href="{{ route('profile') }}" class="header__mobile-icon">
              <img src="/assets/images/systemImg/profile.svg" alt="Профиль">
            </a>
            <a href="{{ url('favorite.html') }}" class="header__mobile-icon">
              <img src="/assets/images/systemImg/favorite.svg" alt="Избранное">
            </a>
            <x-header-cart mobile />
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- ========== MAIN ========== -->
<main class="product">
  <div class="product__container">
    <nav class="breadcrumbs" aria-label="Путь к товару">
      <ol class="breadcrumbs__list">
        <li class="breadcrumbs__item"><a href="{{ route('home') }}" class="breadcrumbs__link">Главная</a></li>
        <li class="breadcrumbs__item"><a href="{{ route('catalog') }}" class="breadcrumbs__link">Каталог</a></li>
        <li class="breadcrumbs__item"><span class="breadcrumbs__current" aria-current="page">{{ $product->name }}</span></li>
      </ol>
    </nav>

    <section class="product__main">
      <div class="product__image" data-gallery>
        @if ($product->images->isNotEmpty())
          @php($cover = $product->images->first())
          <img class="product__main-photo" data-main-photo src="{{ asset($cover->path) }}" alt="{{ $cover->alt ?? $product->name }}">
          @if ($product->images->count() > 1)
            <div class="product__thumbnails" aria-label="Фото товара">
              @foreach ($product->images as $image)
                <button class="product__thumbnail" type="button" data-photo="{{ asset($image->path) }}"
                        data-alt="{{ $image->alt ?? $product->name }}" aria-label="Показать фото {{ $loop->iteration }}"
                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                  <img src="{{ asset($image->path) }}" alt="" loading="lazy">
                </button>
              @endforeach
            </div>
          @endif
        @else
          <p class="product__placeholder">Фото пока нет</p>
        @endif
      </div>

      <div class="product__info">
        <h1 class="product__info-title">{{ $product->name }}</h1>
        @php($stock = $product->variants->isEmpty() ? $product->stock : $product->variants->max('stock'))
        @php($price = $product->variants->isEmpty() ? $product->price : $product->variants->min('price'))
        <p class="product__price">{{ $product->variants->count() > 1 ? 'от ' : '' }}{{ number_format($price, 2, ',', ' ') }} ₽</p>
        <p>{{ $stock > 0 ? 'В наличии' : 'Нет в наличии' }}</p>
        <a class="product__info-rating-count" href="#reviews">
          @if ($rating->total)
            ★ {{ number_format($rating->average, 1, ',', ' ') }} из 5 · Оценок: {{ $rating->total }}
          @else
            Оценок пока нет
          @endif
        </a>

        <dl class="product__specs">
          <div class="product__specs-row">
            <dt class="product__specs-label">Категория</dt>
            <dd class="product__specs-value">{{ $product->category->name }}</dd>
          </div>
          <div class="product__specs-row">
            <dt class="product__specs-label">Бренд</dt>
            <dd class="product__specs-value">{{ $product->brand->name }}</dd>
          </div>
          @foreach ($product->specificationRows() as $spec)
            <div class="product__specs-row">
              <dt class="product__specs-label">{{ $spec['name'] }}</dt>
              <dd class="product__specs-value">{{ $spec['value'] }}</dd>
            </div>
          @endforeach
        </dl>

        @include('products.cart-control')
      </div>
    </section>

    @if ($product->description || $product->composition)
      <section class="product__details">
        @if ($product->description)
          <div class="product__details-section">
            <h2 class="product__details-section-title">Описание</h2>
            <div class="product__details-content"><p class="product__description">{{ $product->description }}</p></div>
          </div>
        @endif
        @if ($product->composition)
          <div class="product__details-section">
            <h2 class="product__details-section-title">Состав продукта</h2>
            <div class="product__details-content"><p class="product__description">{{ $product->composition }}</p></div>
          </div>
        @endif
      </section>
    @endif

    @include('products.reviews')

    @if ($product->relatedProducts->isNotEmpty())
      <section class="product__related">
        <h2 class="product__details-section-title">Связанные товары</h2>
        <ul class="product__related-list">
          @foreach ($product->relatedProducts as $related)
            <li>
              <a href="{{ route('products.show', $related->slug) }}" class="product__related-link">
                @if ($related->cover)
                  <img src="{{ asset($related->cover->path) }}" alt="" loading="lazy">
                @endif
                <span>{{ $related->name }}</span>
              </a>
            </li>
          @endforeach
        </ul>
      </section>
    @endif
  </div>
</main>
<script src="{{ asset('assets/js/product-gallery.js') }}" defer></script>

<!-- ===== FOOTER ==== -->
<footer class="footer">
  <div class="footer__inner">
    <div class="footer__column">
      <img src="/assets/images/systemImg/logo.svg" alt="SportFuel" class="footer__logo">
    </div>
    <div class="footer__column">
      <h3 class="footer__title">Разделы сайта</h3>
      <a href="{{ url('index.html') }}" class="footer__link">Главная</a>
      <a href="{{ url('katalog.html') }}" class="footer__link">Каталог</a>
      <a href="{{ url('payment_and_delivery.html') }}" class="footer__link">Доставка</a>
      <a href="{{ url('order.html') }}" class="footer__link">Контакты</a>
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
        <input type="email" placeholder="Электронная почта" class="footer__subscribe-input">
        <button class="footer__subscribe-btn">Подписаться</button>
      </div>
      <p class="footer__subscribe-text">Ты всегда будешь в курсе новинок и выгодных предложений!</p>
    </div>
  </div>
  <div class="footer__copyright">©2025 SportFuel. Все права защищены.</div>
</footer>

</body>
</html>