<?php

namespace App\Modules\Identity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Middleware que verifica que l'usuari autenticat té un rol específic.
 * Ús: ->middleware('role:admin') o ->middleware('role:admin,staff') (qualsevol dels dos).
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'No autenticat.');
        }

        if (! $user->hasAnyRole($roles)) {
            abort(403, 'No tens permís per accedir a aquest recurs.');
        }

        return $next($request);
    }
}
