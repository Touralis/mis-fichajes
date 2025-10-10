<?php

use App\Http\Controllers\FichajeController;

Route::middleware(['web', 'auth'])
  ->group(function () {
    Route::get('/fichajes', [FichajeController::class, 'index'])->name('fichajes.dashboard');
    Route::post('/fichajes/click', [FichajeController::class, 'clickButton'])->name('fichajes.click');
    Route::get('/fichajes/admin', [FichajeController::class, 'indexAdmin'])->name('fichajes.dashboard.admin');
    Route::post('/fichajes/admin/storeEmpleado', [FichajeController::class, 'storeEmpleado'])->name('fichajes.admin.storeEmpleado');
    Route::put('/fichajes/admin/updateEmpleado/{id}', [FichajeController::class, 'updateEmpleado'])->name('fichajes.admin.updateEmpleado');
    Route::delete('/fichajes/admin/destroyEmpleado/{id}', [FichajeController::class, 'destroyEmpleado'])->name('fichajes.admin.destroyEmpleado');
  });
