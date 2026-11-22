<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('profile', [
            'user' => $user,
            'fullName' => implode(' ', [$user->last_name, $user->first_name, $user->patronymic]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $emailIsChanging = $request->input('email') !== $user->email;

        $data = $request->validateWithBag('profile', [
            'last_name' => ['required', 'string', 'max:30', 'regex:/\A[А-Яа-яЁё]+\z/u'],
            'first_name' => ['required', 'string', 'max:30', 'regex:/\A[А-Яа-яЁё]+\z/u'],
            'patronymic' => ['required', 'string', 'max:30', 'regex:/\A[А-Яа-яЁё]+\z/u'],
            'email' => ['required', 'email', 'max:30', 'regex:/\A[\x21-\x7e]+\z/', Rule::unique('users')->ignore($user)],
            'phone' => ['required', 'string', 'max:11', 'regex:/\A[0-9]+\z/'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'current_password' => [Rule::requiredIf($emailIsChanging), 'nullable', 'bail', 'string', 'max:72', 'current_password'],
        ], [
            'required' => 'Заполните поле :attribute.',
            'max' => 'Поле :attribute превышает допустимую длину.',
            'regex' => 'Поле :attribute имеет неверный формат.',
            'email.email' => 'Введите корректный email.',
            'email.unique' => 'Этот email уже используется.',
            'current_password.required' => 'Для смены email введите текущий пароль.',
            'current_password.current_password' => 'Текущий пароль неверен.',
        ], [
            'last_name' => 'фамилия',
            'first_name' => 'имя',
            'patronymic' => 'отчество',
            'phone' => 'телефон',
            'delivery_address' => 'адрес доставки',
            'current_password' => 'текущий пароль',
        ]);

        unset($data['current_password']);

        DB::transaction(function () use ($user, $data, $emailIsChanging): void {
            if ($emailIsChanging) {
                Password::deleteToken($user);
                $user->email_verified_at = null;
            }

            $user->fill($data)->save();

            if ($emailIsChanging) {
                Password::deleteToken($user);
            }
        });

        return redirect()->route('profile')->with('status', 'Данные профиля сохранены.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('password', [
            'current_password' => ['bail', 'required', 'string', 'max:72', 'current_password'],
            'password' => [
                'required', 'string', 'min:10', 'max:72', 'confirmed', 'different:current_password',
                'regex:/\A[\x21-\x7e]+\z/', 'regex:/[A-Za-z]/',
                'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'current_password.required' => 'Введите текущий пароль.',
            'current_password.current_password' => 'Текущий пароль неверен.',
            'current_password.max' => 'Текущий пароль слишком длинный.',
            'password.required' => 'Введите новый пароль.',
            'password.min' => 'Пароль должен содержать не менее 10 символов.',
            'password.max' => 'Пароль должен содержать не более 72 символов.',
            'password.confirmed' => 'Новый пароль и его подтверждение не совпадают.',
            'password.different' => 'Новый пароль должен отличаться от текущего.',
            'password.regex' => 'Используйте латинские буквы, цифры и специальные символы без пробелов.',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $data): void {
            $user->password = $data['password'];
            $user->setRememberToken(Str::random(60));
            $user->save();

            Password::deleteToken($user);
        });

        Auth::logoutCurrentDevice();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Пароль изменён. Войдите с новым паролем.');
    }
}
