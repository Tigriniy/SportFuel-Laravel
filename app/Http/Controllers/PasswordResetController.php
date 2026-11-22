<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendLink(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:30'],
        ], [
            'email.required' => 'Введите email.',
            'email.email' => 'Введите корректный email.',
            'email.max' => 'Email должен быть не длиннее 30 символов.',
        ]);

        Password::sendResetLink($credentials + ['is_blocked' => false]);

        return back()->with('status', 'Если для этого адреса доступно восстановление, мы отправим ссылку.');
    }

    public function showResetForm(Request $request, string $token): View
    {
        $email = $request->query('email');

        return view('auth.reset-password', [
            'token' => $token,
            'email' => is_string($email) ? $email : '',
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:30'],
            'password' => [
                'required', 'string', 'min:10', 'max:72', 'confirmed',
                'regex:/\A[\x21-\x7e]+\z/', 'regex:/[A-Za-z]/',
                'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'token.required' => 'Откройте ссылку из письма.',
            'token.max' => 'Запросите новую ссылку для восстановления.',
            'email.required' => 'Введите email.',
            'email.email' => 'Введите корректный email.',
            'email.max' => 'Email должен быть не длиннее 30 символов.',
            'password.required' => 'Введите новый пароль.',
            'password.min' => 'Пароль должен содержать не менее 10 символов.',
            'password.max' => 'Пароль должен быть не длиннее 72 символов.',
            'password.confirmed' => 'Пароли не совпадают.',
            'password.regex' => 'Используйте латинские буквы, цифры и специальные символы, без пробелов.',
        ]);

        $status = Password::reset($credentials + ['is_blocked' => false], function (User $user, string $password): void {
            $user->password = $password;
            $user->setRememberToken(Str::random(60));
            $user->save();

            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'Ссылка недействительна. Запросите новую ссылку.'])
                ->withInput($request->only('email'));
        }

        return redirect()->route('login')->with('status', 'Пароль изменён. Войдите с новым паролем.');
    }
}
