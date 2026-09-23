<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production' && str_starts_with(config('app.url'), 'https')) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            $view->with('currentUser', auth()->user());
            $view->with('currentTenant', auth()->user()?->tenant);
        });
    }
}
