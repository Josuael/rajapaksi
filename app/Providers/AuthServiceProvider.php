<?php

namespace App\Providers;

use App\Auth\MUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Auth::provider('MUser', function ($app, array $config) {
            return new MUserProvider();
        });
    }
}
