<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$uloge): Response
    {
        $user = $request->user();
        if (!$user || !in_array($user->uloga, $uloge)) {
            abort(403, 'Nemate dozvolu za ovu akciju.');
        }
        return $next($request);
    }
}
