<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Resources\UserResource;
use Illuminate\Http\Request;

/*
 * Endpoint GET /api/me. Retorna les dades de l'usuari autenticat.
 * Usat per la SPA per saber qui està loguejat (i quin rol té).
 */
class MeController extends Controller
{
    public function __invoke(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
