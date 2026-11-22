<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
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

Route::middleware('guest')->group(function () {
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:10,1')
        ->name('password.update');
});

Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::get('/profile.html', [ProfileController::class, 'show'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
        ->middleware('throttle:10,1')
        ->name('profile.password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

foreach (['index', 'katalog', 'carta-tovara', 'cart', 'favorite', 'order', 'payment_and_delivery'] as $page) {
    Route::view('/'.$page.'.html', $page);
}
