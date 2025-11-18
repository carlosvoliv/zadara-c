Route::prefix('zadara')->group(function () {
    Route::get('lista', [ZadaraController::class, 'lista']);
    Route::get('download/{path}', [ZadaraController::class, 'download'])
         ->where('path', '.*'); // permite barras na URL
});
