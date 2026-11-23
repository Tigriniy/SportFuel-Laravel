<?php

use App\Http\Controllers\Admin\CatalogReferenceController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::get('/index.html', [CatalogController::class, 'home']);
Route::get('/cart.html', [CartController::class, 'index'])->name('cart.index');
Route::get('/katalog.html', [CatalogController::class, 'index'])->name('catalog');
Route::get('/products/{product:slug}', [CatalogController::class, 'show'])->name('products.show');
Route::post('/products/{product:slug}/reviews', [ReviewController::class, 'store'])
    ->middleware(['auth', 'auth.session', 'throttle:10,1'])->name('reviews.store');
Route::redirect('/carta-tovara.html', '/katalog.html');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'auth.session', 'can:manage-catalog'])->group(function () {
    Route::redirect('/', '/admin/products')->name('home');
    Route::resource('products', ProductController::class)->except('show');
    Route::middleware('can:moderate-reviews')->group(function () {
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/reviews/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
    });

    Route::where(['kind' => 'categories|brands', 'record' => '[0-9]+'])->group(function () {
        Route::get('/{kind}', [CatalogReferenceController::class, 'index'])->name('references.index');
        Route::post('/{kind}', [CatalogReferenceController::class, 'store'])->name('references.store');
        Route::get('/{kind}/{record}/edit', [CatalogReferenceController::class, 'edit'])->name('references.edit');
        Route::patch('/{kind}/{record}', [CatalogReferenceController::class, 'update'])->name('references.update');
        Route::delete('/{kind}/{record}', [CatalogReferenceController::class, 'destroy'])->name('references.destroy');
    });
});

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
    Route::post('/products/{product:slug}/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/items/{item}', [CartController::class, 'update'])->whereNumber('item')->name('cart.update');
    Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->whereNumber('item')->name('cart.destroy');
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

foreach (['favorite', 'order', 'payment_and_delivery'] as $page) {
    Route::view('/'.$page.'.html', $page);
}
