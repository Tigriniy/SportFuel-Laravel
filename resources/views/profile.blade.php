<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Личный кабинет — SportFuel</title>
	<link rel="stylesheet" href="/assets/css/index.css">
	<link rel="stylesheet" href="/assets/css/header-footer.css">
	<link rel="stylesheet" href="/assets/css/profile.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Epilogue:ital,wght@0,100..900;1,100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>
<body>

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

            <div class="header__search">
                <input type="text" placeholder="Поиск" class="header__search-input">
                <img src="/assets/images/systemImg/search.svg" alt="Поиск">
            </div>

            <a href="reg-in.html" class="header__icon-link">
                <img src="/assets/images/systemImg/profile.svg" alt="Профиль">
            </a>
            <a href="favorite.html" class="header__icon-link">
                <img src="/assets/images/systemImg/favorite.svg" alt="Избранное">
            </a>
            <a href="cart.html" class="header__icon-link">
                <img src="/assets/images/systemImg/cart.svg" alt="Корзина">
            </a>
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
                    <div class="header__mobile-search">
                        <input type="text" placeholder="Поиск...">
                        <img src="/assets/images/systemImg/search.svg" alt="Поиск">
                    </div>
                    <div class="header__mobile-icons">
                        <a href="reg-in.html" class="header__mobile-icon">
                            <img src="/assets/images/systemImg/profile.svg" alt="Профиль">
                        </a>
                        <a href="favorite.html" class="header__mobile-icon">
                            <img src="/assets/images/systemImg/favorite.svg" alt="Избранное">
                        </a>
                        <a href="cart.html" class="header__mobile-icon">
                            <img src="/assets/images/systemImg/cart.svg" alt="Корзина">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<main class="profile">
    <div class="profile__container">
        <div class="profile__header">
            <h1 class="profile__title">Личный кабинет</h1>
            <p class="profile__subtitle">Управляйте личными данными и паролем</p>
            @if (session('status'))
                <p class="profile__subtitle" role="status">{{ session('status') }}</p>
            @endif
        </div>
        <div class="profile__grid">
            <div class="profile__info">
                <div class="profile__card">
                    <div class="profile__avatar">
                        <img src="/assets/images/systemImg/profile.svg" alt="Аватар пользователя">
                    </div>
                    <h2 class="profile__name">{{ $fullName }}</h2>
                    <p class="profile__email">{{ $user->email }}</p>
                </div>
                <div class="profile__card">
                    <h3 class="profile__card-title">Контактные данные</h3>
                    <ul class="profile__contacts">
                        <li class="profile__contact">
                            <span class="profile__contact-label">Логин</span>
                            <span class="profile__contact-value">{{ $user->login }}</span>
                        </li>
                        <li class="profile__contact">
                            <span class="profile__contact-label">Телефон</span>
                            <span class="profile__contact-value">{{ $user->phone }}</span>
                        </li>
                        <li class="profile__contact">
                            <span class="profile__contact-label">Адрес доставки</span>
                            <span class="profile__contact-value">{{ $user->delivery_address ?? 'Не указан' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="profile__edit">
                <div class="profile__card">
                    <h3 class="profile__card-title">Редактировать профиль</h3>
                    @php($profileErrors = array_diff($errors->profile->all(), $errors->profile->get('current_password')))
                    @if ($profileErrors)
                        <ul role="alert">
                            @foreach ($profileErrors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <form class="profile__form" method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="profile__form-group">
                            <label for="last_name" class="profile__label">Фамилия</label>
                            <input type="text" id="last_name" name="last_name" class="profile__input"
                                   value="{{ old('last_name', $user->last_name) }}" maxlength="30" autocomplete="family-name" required>
                        </div>
                        <div class="profile__form-group">
                            <label for="first_name" class="profile__label">Имя</label>
                            <input type="text" id="first_name" name="first_name" class="profile__input"
                                   value="{{ old('first_name', $user->first_name) }}" maxlength="30" autocomplete="given-name" required>
                        </div>
                        <div class="profile__form-group">
                            <label for="patronymic" class="profile__label">Отчество</label>
                            <input type="text" id="patronymic" name="patronymic" class="profile__input"
                                   value="{{ old('patronymic', $user->patronymic) }}" maxlength="30" required>
                        </div>
                        <div class="profile__form-group">
                            <label for="email" class="profile__label">Email</label>
                            <input type="email" id="email" name="email" class="profile__input"
                                   value="{{ $user->email }}" maxlength="30" autocomplete="email"
                                   aria-describedby="profile_email_help" required>
                            <p class="profile__contact-value" id="profile_email_help">
                                Для смены email введи текущий пароль перед сохранением.
                            </p>
                        </div>
                        <div class="profile__form-group">
                            <label for="phone" class="profile__label">Телефон (до 11 цифр)</label>
                            <input type="tel" id="phone" name="phone" class="profile__input" inputmode="numeric"
                                   value="{{ old('phone', $user->phone) }}" maxlength="11" pattern="[0-9]{1,11}" autocomplete="tel" required>
                        </div>
                        <div class="profile__form-group">
                            <label for="delivery_address" class="profile__label">Адрес доставки</label>
                            <textarea id="delivery_address" name="delivery_address" class="profile__textarea"
                                      maxlength="255" autocomplete="street-address">{{ old('delivery_address', $user->delivery_address) }}</textarea>
                        </div>
                        <div class="profile__form-group">
                            <label for="email_current_password" class="profile__label">Текущий пароль (при смене email)</label>
                            <input type="password" id="email_current_password" name="current_password"
                                   class="profile__input" autocomplete="current-password" maxlength="72"
                                   @if ($errors->profile->has('current_password')) aria-invalid="true" aria-describedby="profile_email_password_error" @endif>
                            @error('current_password', 'profile')
                                <p class="profile__contact-value" id="profile_email_password_error" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="profile__btn">Сохранить изменения</button>
                    </form>
                </div>

                <div class="profile__card profile__card--danger">
                    <h3 class="profile__card-title">Безопасность</h3>
                    <details @if ($errors->password->any()) open @endif>
                        <summary class="profile__btn profile__btn--outline">Изменить пароль</summary>
                        @if ($errors->password->any())
                            <ul role="alert">
                                @foreach ($errors->password->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <form class="profile__form" method="POST" action="{{ route('profile.password.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="profile__form-group">
                                <label for="current_password" class="profile__label">Текущий пароль</label>
                                <input type="password" id="current_password" name="current_password"
                                       class="profile__input" autocomplete="current-password" maxlength="72" required>
                            </div>
                            <div class="profile__form-group">
                                <label for="password" class="profile__label">Новый пароль</label>
                                <p class="profile__contact-value" id="password_help">От 10 символов: латинские буквы, цифры и спецсимволы.</p>
                                <input type="password" id="password" name="password" class="profile__input"
                                       autocomplete="new-password" aria-describedby="password_help" minlength="10" maxlength="72" required>
                            </div>
                            <div class="profile__form-group">
                                <label for="password_confirmation" class="profile__label">Повторите новый пароль</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="profile__input" autocomplete="new-password" minlength="10" maxlength="72" required>
                            </div>
                            <button type="submit" class="profile__btn">Сохранить пароль</button>
                        </form>
                    </details>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="profile__btn profile__btn--danger">Выйти из аккаунта</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

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