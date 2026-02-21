<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use App\Auth\MUserProvider;
use Illuminate\Support\Facades\URL;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {

    if (app()->environment('local')) {
        URL::forceScheme('https'); 
    }

        Paginator::useBootstrapFive();

        /**
         * ✅ Register custom auth provider (Laravel 12 project lu gak punya AuthServiceProvider)
         */
        Auth::provider('muser', function ($app, array $config) {
        return new MUserProvider();
        });

        /**
         * ✅ Gates (sebelumnya pakai email, sekarang M_User gak punya email)
         */
        Gate::define('manage-users', function ($user) {
            // admin web OR legacy Su (bit)
            $role = strtolower((string)($user->Role ?? $user->role ?? ''));
            $su   = (bool)($user->Su ?? false);

            return $su || $role === 'admin';
        });

        Gate::define('view-process', function ($user) {
            $role = strtolower((string)($user->Role ?? $user->role ?? ''));
            $su   = (bool)($user->Su ?? false);

            return $su || in_array($role, ['admin', 'internal'], true);
        });
    }
}
