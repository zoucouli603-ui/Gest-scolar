<?php

use App\Http\Controllers\SyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'school.tenant', 'school.access'])->post('/sync-notes', [SyncController::class, 'store'])
    ->name('sync.notes');
