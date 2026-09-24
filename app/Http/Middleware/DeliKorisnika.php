<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

// Deli prijavljenog korisnika i njegovu firmu sa svim prikazima (jednom po zahtevu)
class DeliKorisnika
{
    public function handle(Request $request, Closure $next)
    {
        if ($korisnik = $request->user()) {
            $korisnik->loadMissing('tenant');
            View::share('currentUser', $korisnik);
            View::share('currentTenant', $korisnik->tenant);
        }

        return $next($request);
    }
}
