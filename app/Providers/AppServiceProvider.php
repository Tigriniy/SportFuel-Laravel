<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-catalog', fn (User $user): bool => ! $user->is_blocked && in_array($user->role, ['admin', 'manager'], true)
        );

        Gate::define('moderate-reviews', fn (User $user): bool => $user->can('manage-catalog'));

        ResetPassword::createUrlUsing(function (User $user, string $token): string {
            return rtrim(config('app.url'), '/').route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ], false);
        });
    }
}
