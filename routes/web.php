<?php

use App\Modules\Identity\Http\Controllers\LoginController;
use App\Modules\Identity\Http\Controllers\LogoutController;
use Illuminate\Support\Facades\Route;

/*
 * Endpoints d'autenticació SPA via Sanctum cookies.
 * Cal middleware 'web' (default a routes/web.php) per a session persistence.
 */
Route::post('/login', LoginController::class);
Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');
