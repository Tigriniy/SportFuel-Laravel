<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Корзина — SportFuel</title>
    <link rel="stylesheet" href="/assets/css/index.css">
  <link rel="stylesheet" href="/assets/css/header-footer.css">
  <link rel="stylesheet" href="/assets/css/cart.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>
<body>

<!-- ===== HEADER ==== -->
<header class="header">
  <div class="header__inner">
    <a href="index.html" class="header__logo">
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

<main class="cart">
  <div class="cart__container">
    <h1 class="cart__title">Корзина</h1>

    @if (session('cart_status'))
      <p class="cart__notice" role="status">{{ session('cart_status') }}</p>
    @endif
    @if ($errors->cart->any())
      <ul class="cart__notice cart__notice--error" role="alert">
        @foreach ($errors->cart->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    @endif

    @if ($items->isEmpty())
      <div class="cart__empty">
        <img src="{{ asset('assets/images/systemImg/cart.svg') }}" alt="" class="cart__empty-icon">
        <h2 class="cart__empty-title">Корзина пуста</h2>
        @guest
          <p class="cart__empty-text">Войди, чтобы добавлять товары в корзину.</p>
          <a href="{{ route('login') }}" class="cart__empty-button">Войти</a>
        @else
          <p class="cart__empty-text">Добавь товары из каталога, чтобы сохранить их в корзине.</p>
          <a href="{{ route('catalog') }}" class="cart__empty-button">Перейти в каталог</a>
        @endguest
      </div>
    @else
      <div class="cart__lines">
        @foreach ($items as $item)
          @php($limit = $item->availableQuantity())
          <article class="cart__line">
            @if ($item->product->cover)
              <img class="cart__photo" src="{{ asset($item->product->cover->path) }}" alt="">
            @endif
            <div class="cart__line-info">
              <h2>
                @if ($item->product->is_active)
                  <a href="{{ route('products.show', $item->product->slug) }}">{{ $item->product->name }}</a>
                @else
                  {{ $item->product->name }}
                @endif
              </h2>
              @if ($item->variant)
                <p>{{ $item->variant->title }}</p>
              @endif
              <p>{{ number_format($item->unitPrice(), 2, ',', ' ') }} ₽ за шт.</p>
              @if ($limit < 1)
                <p class="cart__availability">Позиция сейчас недоступна.</p>
              @elseif ($limit < $item->quantity)
                <p class="cart__availability">Доступно {{ $limit }} шт. Уменьши количество, чтобы учесть позицию в итоге.</p>
              @endif
            </div>
            <div class="cart__line-actions">
              @if ($limit > 0)
                <form method="POST" action="{{ route('cart.update', $item) }}">
                  @csrf
                  @method('PATCH')
                  <label for="quantity-{{ $item->id }}">Количество</label>
                  <input id="quantity-{{ $item->id }}" name="quantity" type="number" min="1" max="{{ $limit }}"
                         value="{{ min($item->quantity, $limit) }}" required>
                  <button type="submit">Обновить</button>
                </form>
              @endif
              <form method="POST" action="{{ route('cart.destroy', $item) }}">
                @csrf
                @method('DELETE')
                <button type="submit">Удалить</button>
              </form>
            </div>
            @if ($limit >= $item->quantity)
              <strong>{{ number_format($item->subtotalInKopecks() / 100, 2, ',', ' ') }} ₽</strong>
            @endif
          </article>
        @endforeach
      </div>
      <div class="cart__total">
        <a href="{{ route('catalog') }}">Вернуться в каталог</a>
        <strong>Итого: {{ number_format($total / 100, 2, ',', ' ') }} ₽</strong>
      </div>
    @endif
  </div>
</main>

<!-- === FOOTER ==== -->
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