<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z]+$/', 'unique:users,login'],
            'last_name' => ['required', 'string', 'max:30', 'regex:/^[А-Яа-яЁё]+$/u'],
            'first_name' => ['required', 'string', 'max:30', 'regex:/^[А-Яа-яЁё]+$/u'],
            'patronymic' => ['required', 'string', 'max:30', 'regex:/^[А-Яа-яЁё]+$/u'],
            'email' => ['required', 'email', 'max:30', 'regex:/^[\x21-\x7e]+$/', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:11', 'regex:/^[0-9]+$/'],
            'password' => ['required', 'string', 'min:10', 'max:72', 'confirmed', 'regex:/^[\x21-\x7e]+$/', 'regex:/[A-Za-z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
        ], [
            'required' => 'Заполните поле :attribute.',
            'max' => 'Поле :attribute превышает допустимую длину.',
            'regex' => 'Поле :attribute имеет неверный формат.',
            'unique' => 'Это значение :attribute уже используется.',
            'confirmed' => 'Пароли не совпадают.',
            'min' => 'Пароль должен содержать не менее 10 символов.',
            'email' => 'Введите корректный email.',
        ], [
            'login'=>'логин', 'last_name'=>'фамилия', 'first_name'=>'имя',
            'patronymic'=>'отчество', 'email'=>'email', 'phone'=>'телефон', 'password'=>'пароль',
        ]);
        User::create($data);
        return redirect()->route('register')->with('success', 'Учётная запись создана. Вход будет подключён следующим шагом.');
    }
}
