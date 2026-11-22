<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Вход — SportFuel</title>
  <link rel="stylesheet" href="/assets/css/conf.css">
  <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>

<div class="auth">
  <div class="auth__container">
    <img src="/assets/images/systemImg/logo.svg" alt="SportFuel" class="auth__logo">

    <div class="auth__card">
      <h2 class="auth__title">Добро пожаловать</h2>
      <p class="auth__subtitle">Войдите или создайте новый аккаунт</p>

      <div class="auth__tabs">
        <div class="auth__tab auth__tab--active">Вход</div>
        <a href="reg-in.html" class="auth__tab">Регистрация</a>
      </div>

      <form class="auth__form" method="POST" action="{{ route('login.store') }}">
        @csrf

        @if (session('status'))
          <p role="status">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
          <ul role="alert">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        @endif

        <label for="login" class="auth__label">Логин</label>
        <input id="login" name="login" type="text" value="{{ old('login') }}"
              maxlength="30" autocomplete="username" class="auth__input" required>

        <label for="password" class="auth__label">Пароль</label>
        <input id="password" name="password" type="password"
              maxlength="72" autocomplete="current-password" class="auth__input" required>

        <button type="submit" class="auth__button auth__button--login">Войти</button>
        <a href="{{ route('password.request') }}" class="auth__forgot">Забыли пароль?</a>
      </form>

      <p class="auth__or">ИЛИ ВОЙТИ ЧЕРЕЗ</p>

      <div class="auth__socials">
        <button class="auth__social-btn">Google</button>
        <button class="auth__social-btn">VK</button>
      </div>
    </div>

    <p class="auth__footer-text">
      <a href="index.html">Вернуться на главную</a>
    </p>
  </div>
</div>

</body>
</html>