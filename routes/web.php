<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ZadaraController;

Route::get('/', function () {
    return view('welcome');
});

Route::view('upload-zip', 'upload-zip');
Route::view('/zadara/upload', 'zadara-upload');

// Rotas Zadara (sem CSRF)
Route::post('api/zadara/upload-zip', [ZadaraController::class, 'uploadZip']);
Route::get('api/zadara/lista', [ZadaraController::class, 'lista']);
Route::get('api/zadara/download/{path}', [ZadaraController::class, 'download'])
     ->where('path', '.*');
