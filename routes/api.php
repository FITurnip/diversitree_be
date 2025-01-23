<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkspaceController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('workspace')->group(function () {
    Route::get('/list', [WorkspaceController::class, 'list']);
    Route::get('/detail/{id}', [WorkspaceController::class, 'getDetail']);
    Route::post('/save-informasi', [WorkspaceController::class, 'saveInformasiWorkspace']);
    Route::post('/save-koordinat', [WorkspaceController::class, 'saveTitikKoordinatWorkspace']);
});
