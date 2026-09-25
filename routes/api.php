<?php

use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

// API-first sloj (PRD 12.1) - isti kontroleri, JSON odgovori, za buducu Node.js migraciju (PRD 12.3).
// MVP koristi sesijsku autentikaciju (isti sajt); token auth je Faza 2.
Route::middleware('auth')->group(function () {
    Route::get('/projekti', [ProjectController::class, 'index']);
    Route::post('/projekti', [ProjectController::class, 'store']);
    Route::get('/projekti/{project}', [ProjectController::class, 'show']);
    Route::patch('/projekti/{project}', [ProjectController::class, 'update']);

    Route::get('/stanovi/{unit}', [UnitController::class, 'show']);
    Route::patch('/stanovi/{unit}', [UnitController::class, 'update']);

    Route::post('/dokumenti', [DocumentController::class, 'store']);
    Route::get('/dokumenti/{document}/preuzmi', [DocumentController::class, 'download']);

    Route::get('/reklamacije', [ClaimController::class, 'index']);
    Route::post('/reklamacije', [ClaimController::class, 'store']);
    Route::patch('/reklamacije/{claim}', [ClaimController::class, 'update']);
});

// Temelj.rs → Temelj Investitor (bez prijave; svaki zahtev je potpisan zajedničkim ključem)
Route::middleware('throttle:120,1')->prefix('temelj')->group(function () {
    Route::post('/upit', [\App\Http\Controllers\Api\TemeljController::class, 'upit']);
    Route::post('/veza', [\App\Http\Controllers\Api\TemeljController::class, 'veza']);
    Route::post('/kupac', [\App\Http\Controllers\Api\TemeljController::class, 'kupac']);
    Route::post('/kupac/dokumenti', [\App\Http\Controllers\Api\TemeljController::class, 'kupacDokumenti']);
    Route::post('/kupac/dokument-link', [\App\Http\Controllers\Api\TemeljController::class, 'kupacDokumentLink']);
});
