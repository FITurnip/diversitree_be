<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\StatusWorkspaceController;
use App\Http\Controllers\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
});

Route::prefix('workspace')->controller(WorkspaceController::class)->group(function () {
    Route::get('/list', 'list');
    Route::get('/detail/{id}', 'getDetail');
    Route::post('/save-informasi', 'saveInformasiWorkspace');
    Route::post('/save-koordinat', 'saveTitikKoordinatWorkspace');
    Route::post('/save-pohon', 'savePohon');
    Route::post('/save-final-result', 'saveFinalResult');
});

Route::get('/status-workspace/list', [StatusWorkspaceController::class, 'list']);
