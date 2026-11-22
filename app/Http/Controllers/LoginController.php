<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:30', 'regex:/\A[A-Za-z]+\z/'],
            'password' => ['required', 'string', 'max:72', 'regex:/\A[\x21-\x7E]+\z/'],
        ], [
            'login.required' => 'Введите логин.',
            'login.max' => 'Логин должен быть не длиннее 30 символов.',
            'login.regex' => 'Логин должен содержать только латинские буквы.',
            'password.required' => 'Введите пароль.',
            'password.max' => 'Пароль должен быть не длиннее 72 символов.',
            'password.regex' => 'Проверьте символы в пароле.',
        ]);

        $credentials['is_blocked'] = false;

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['login' => 'Не удалось войти. Проверьте логин и пароль.'])
                ->withInput($request->only('login'));
        }

        $request->session()->regenerate();

        return redirect()->intended(route('profile'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}