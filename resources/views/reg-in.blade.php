<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Регистрация — SportFuel</title>
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

      <!-- таб -->
      <div class="auth__tabs">
        <a href="log-in.html" class="auth__tab">Вход</a>
        <div class="auth__tab auth__tab--active">Регистрация</div>
      </div>

      @if(session('success'))<p role="status">{{ session('success') }}</p>@endif
      @if($errors->any())<ul role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
      <form class="auth__form" method="POST" action="{{ route('register.store') }}">
      @csrf
<label for="login" class="auth__label">Логин (только латинские буквы)</label><input id="login" name="login" type="text" maxlength="30" required class="auth__input" value="{{ old('login') }}">
<label for="last_name" class="auth__label">Фамилия</label><input id="last_name" name="last_name" type="text" maxlength="30" required class="auth__input" value="{{ old('last_name') }}">
<label for="first_name" class="auth__label">Имя</label><input id="first_name" name="first_name" type="text" maxlength="30" required class="auth__input" value="{{ old('first_name') }}">
<label for="patronymic" class="auth__label">Отчество</label><input id="patronymic" name="patronymic" type="text" maxlength="30" required class="auth__input" value="{{ old('patronymic') }}">
<label for="email" class="auth__label">Email</label><input id="email" name="email" type="email" maxlength="30" required class="auth__input" value="{{ old('email') }}">
<label for="phone" class="auth__label">Телефон (до 11 цифр)</label><input id="phone" name="phone" type="tel" maxlength="11" required class="auth__input" value="{{ old('phone') }}">
<label for="password" class="auth__label">Пароль (от 10 символов: латиница, цифры и спецсимволы)</label><input id="password" name="password" type="password" maxlength="72" required class="auth__input">
<label for="password_confirmation" class="auth__label">Подтверждение пароля</label><input id="password_confirmation" name="password_confirmation" type="password" maxlength="72" required class="auth__input">
<button class="auth__button auth__button--register" type="submit">Зарегистрироваться</button></form>
    </div>

    <p class="auth__footer-text">
      <a href="index.html">Вернуться на главную</a>
    </p>
  </div>
</div>

</body>
</html>