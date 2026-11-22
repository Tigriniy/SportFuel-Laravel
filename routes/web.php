<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'index');

Route::view('/reg-in.html', 'reg-in')->name('register');
Route::post('/register', [RegistrationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('register.store');

Route::view('/log-in.html', 'log-in')->name('login');
Route::post('/login', [LoginController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('login.store');

Route::view('/profile.html', 'profile')
    ->middleware('auth')
    ->name('profile');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

foreach (['index', 'katalog', 'carta-tovara', 'cart', 'favorite', 'order', 'payment_and_delivery'] as $page) {
    Route::view('/'.$page.'.html', $page);
}