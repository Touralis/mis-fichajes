<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FichajeController;

Route::middleware(['web', 'auth'])
  ->group(function () {
    Route::get('/fichajes', [FichajeController::class, 'index'])->name('fichajes.dashboard');
    Route::post('/fichajes/click', [FichajeController::class, 'clickButton'])->name('fichajes.click');
  });
