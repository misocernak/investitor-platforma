<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// Aplikaciji firme pristupa samo korisnik čija je firma odobrena i aktivna.
// Admin platforme ide na svoj panel, a firma na čekanju / odbijena / suspendovana na ekran statusa.
class AktivnaFirma
{
    public function handle(Request $request, Closure $next)
    {
        $korisnik = $request->user();
        if ($korisnik?->jePlatforma()) {
            return redirect()->route('platforma.index');
        }
        if (! $korisnik?->tenant?->aktivan()) {
            return redirect()->route('registracija.status');
        }
        return $next($request);
    }
}
