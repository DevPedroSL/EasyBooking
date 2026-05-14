<?php

namespace App\Providers;

use App\Models\Barbershop;
use App\Models\Service;
use App\Models\User;
use App\Policies\BarbershopPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
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
        if (is_string(config('view.compiled'))) {
            File::ensureDirectoryExists(config('view.compiled'), 0775);
        }

        Gate::policy(Barbershop::class, BarbershopPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Gate::define('access-admin', fn (User $user): bool => $user->role === 'admin');

        $refreshPublicBarbershopCache = function ($model): void {
            Cache::forever('public_barbershops_version', (string) microtime(true));
        };

        Barbershop::saved($refreshPublicBarbershopCache);
        Barbershop::deleted($refreshPublicBarbershopCache);
        Service::saved($refreshPublicBarbershopCache);
        Service::deleted($refreshPublicBarbershopCache);
    }
}
