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
 * Endpoint públic de captació de leads via formulari web.
 * Rate-limited: 5 sol·licituds per minut per IP. Honeypot al camp `_hp`.
 */
Route::post('/public/leads', \App\Modules\Crm\Http\Controllers\PublicLeadController::class)
    ->middleware('throttle:5,1');

/*
 * Rutes autenticades (Sanctum SPA cookies).
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', MeController::class);
    Route::get('/leads', [\App\Modules\Crm\Http\Controllers\LeadController::class, 'index']);
    Route::post('/leads', [\App\Modules\Crm\Http\Controllers\LeadController::class, 'store']);
    Route::get('/leads/{lead}', [\App\Modules\Crm\Http\Controllers\LeadController::class, 'show']);
    Route::patch('/leads/{lead}', [\App\Modules\Crm\Http\Controllers\LeadController::class, 'update']);
    Route::delete('/leads/{lead}', [\App\Modules\Crm\Http\Controllers\LeadController::class, 'destroy']);
});
