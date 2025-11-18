<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ZadaraController;

Route::prefix('zadara')->group(function () {
    Route::get('lista', [ZadaraController::class, 'lista']);
    Route::get('download/{path}', [ZadaraController::class, 'download'])
         ->where('path', '.*'); // permite barras na URL
});
