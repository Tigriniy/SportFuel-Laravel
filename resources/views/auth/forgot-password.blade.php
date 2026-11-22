@extends('auth.password-layout')

@section('title', 'Восстановление пароля')

@section('form')
  <p class="auth__subtitle">Укажите email, который использовали при регистрации.</p>
  <form class="auth__form" method="POST" action="{{ route('password.email') }}">
    @csrf
    <label for="email" class="auth__label">Email</label>
    <input id="email" name="email" type="email" value="{{ old('email') }}"
           maxlength="30" autocomplete="email" class="auth__input" required>
    <button type="submit" class="auth__button auth__button--login">Отправить ссылку</button>
  </form>
@endsection
