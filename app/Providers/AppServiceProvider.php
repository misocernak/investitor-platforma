<?php

namespace App\Providers;

use Illuminate\Database\Schema\Builder;
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
        // Starije MySQL verzije na shared hostingu: 191 znak × 4 bajta (utf8mb4) staje u ograničenje indeksa
        Builder::defaultStringLength(191);

        if (config('app.env') === 'production' && str_starts_with(config('app.url'), 'https')) {
            URL::forceScheme('https');
        }

        // Prijavljeni korisnik i firma se dele sa svim prikazima jednom po zahtevu
        // (ranije se ovo izvršavalo za svaki prikaz i svaku komponentu na stranici)
        View::share('currentUser', null);
        View::share('currentTenant', null);
    }
}
