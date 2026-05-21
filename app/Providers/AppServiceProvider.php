<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\Repositories\UserRepository::class,
            \App\Repositories\EloquentUserRepository::class
        );

        $this->app->bind(
            \App\Contracts\Repositories\OtpRepository::class,
            \App\Repositories\EloquentOtpRepository::class
        );

        $this->app->bind(
            \App\Contracts\Services\Auth\AuthServiceContract::class,
            \App\Services\Auth\AuthService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
