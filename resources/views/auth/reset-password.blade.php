@extends('auth.password-layout')

@section('title', 'Новый пароль')

@section('form')
  <p class="auth__subtitle">От 10 символов. Используйте латинские буквы, цифры и специальные символы.</p>
  <form class="auth__form" method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <label for="email" class="auth__label">Email</label>
    <input id="email" name="email" type="email" value="{{ old('email', $email) }}"
           maxlength="30" autocomplete="email" class="auth__input" required>

    <label for="password" class="auth__label">Новый пароль</label>
    <input id="password" name="password" type="password" minlength="10" maxlength="72"
           autocomplete="new-password" class="auth__input" required>

    <label for="password_confirmation" class="auth__label">Повторите пароль</label>
    <input id="password_confirmation" name="password_confirmation" type="password"
           minlength="10" maxlength="72" autocomplete="new-password" class="auth__input" required>

    <button type="submit" class="auth__button auth__button--login">Сохранить пароль</button>
    <a href="{{ route('password.request') }}" class="auth__forgot">Запросить новую ссылку</a>
  </form>
@endsection
