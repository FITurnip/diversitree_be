<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\StatusWorkspaceController;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->middleware('auth:sanctum');
    Route::post('/edit-profile', 'editProfile')->middleware('auth:sanctum');
});

Route::prefix('workspace')->controller(WorkspaceController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/list', 'list');
    Route::get('/detail/{id}', 'getDetail');
    Route::post('/save-informasi', 'saveInformasiWorkspace');
    Route::post('/save-koordinat', 'saveTitikKoordinatWorkspace');
    Route::post('/save-pohon', 'savePohon');
    Route::post('/save-final-result', 'saveFinalResult');

    // mode tim
    Route::prefix('/tim')->group(function () {
        Route::get('/qr-code/{workspace_id}', 'bagiAkses');
        Route::post('/list', 'listAnggotaTim');
        Route::post('/tambah-anggota', 'tambahAnggota');
    });
});

Route::get('/status-workspace/list', [StatusWorkspaceController::class, 'list']);
