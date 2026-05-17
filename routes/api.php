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
    Route::patch('/leads/{lead}/status', \App\Modules\Crm\Http\Controllers\LeadStatusController::class);
    Route::post('/leads/{lead}/notes', [\App\Modules\Crm\Http\Controllers\LeadNoteController::class, 'store']);
    Route::delete('/notes/{note}', [\App\Modules\Crm\Http\Controllers\LeadNoteController::class, 'destroy']);

    Route::apiResource('people', \App\Modules\Contacts\Http\Controllers\PersonController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::apiResource('companies', \App\Modules\Contacts\Http\Controllers\CompanyController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::apiResource('projects', \App\Modules\Projects\Http\Controllers\ProjectController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::patch('/projects/{project}/status', \App\Modules\Projects\Http\Controllers\ProjectStatusController::class);
    Route::post('/projects/{project}/packs', [\App\Modules\Projects\Http\Controllers\ProjectHoursPackController::class, 'store']);
    Route::patch('/projects/{project}/packs/{pack}', [\App\Modules\Projects\Http\Controllers\ProjectHoursPackController::class, 'update']);
    Route::delete('/projects/{project}/packs/{pack}', [\App\Modules\Projects\Http\Controllers\ProjectHoursPackController::class, 'destroy']);
    Route::post('/projects/{project}/tasks', [\App\Modules\Projects\Http\Controllers\ProjectTaskController::class, 'store']);
    Route::patch('/projects/{project}/tasks/{task}', [\App\Modules\Projects\Http\Controllers\ProjectTaskController::class, 'update']);
    Route::delete('/projects/{project}/tasks/{task}', [\App\Modules\Projects\Http\Controllers\ProjectTaskController::class, 'destroy']);
    Route::post('/projects/{project}/time-entries', [\App\Modules\Projects\Http\Controllers\ProjectTimeEntryController::class, 'store']);
    Route::patch('/projects/{project}/time-entries/{timeEntry}', [\App\Modules\Projects\Http\Controllers\ProjectTimeEntryController::class, 'update']);
    Route::delete('/projects/{project}/time-entries/{timeEntry}', [\App\Modules\Projects\Http\Controllers\ProjectTimeEntryController::class, 'destroy']);
    Route::post('/leads/{lead}/project', [\App\Modules\Projects\Http\Controllers\LeadProjectController::class, 'store']);
});
