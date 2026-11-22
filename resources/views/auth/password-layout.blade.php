<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="referrer" content="no-referrer">
  <title>@yield('title') — SportFuel</title>
  <link rel="stylesheet" href="{{ asset('assets/css/conf.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
</head>
<body>
  <div class="auth">
    <div class="auth__container">
      <img src="{{ asset('assets/images/systemImg/logo.svg') }}" alt="SportFuel" class="auth__logo">
      <div class="auth__card">
        <h1 class="auth__title">@yield('title')</h1>
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
        @yield('form')
      </div>
      <p class="auth__footer-text"><a href="{{ route('login') }}">Вернуться ко входу</a></p>
    </div>
  </div>
</body>
</html>
