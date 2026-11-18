<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;
Route::view('/', 'index');
Route::view('/reg-in.html', 'reg-in')->name('register');
Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:10,1')->name('register.store');
foreach (['index','katalog','carta-tovara','cart','favorite','log-in','order','profile','payment_and_delivery'] as $page) {
    Route::view('/'.$page.'.html', $page);
}
