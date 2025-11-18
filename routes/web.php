<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ZadaraController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('api/zadara/lista', [ZadaraController::class, 'lista']);
Route::get('api/zadara/download/{path}', [ZadaraController::class, 'download'])
     ->where('path', '.*');
