<?php

use App\Modules\Identity\Http\Controllers\MeController;
use Illuminate\Support\Facades\Route;

/*
 * Endpoint de salut. Retorna l'estat del servei i un timestamp ISO 8601.
 * No requereix autenticació.
 */
Route::get('/health', function () {
    return [
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ];
});

/*
 * Rutes autenticades (Sanctum SPA cookies).
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', MeController::class);
});
