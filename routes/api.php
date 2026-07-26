<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/**
 * API Routes 
 * Rutas privadas (utilizando middleware)
 */

//

/**
 * metodo nativo de laravel para utilizar los middleware
 */
Route::middleware(['system.jwt'])->group(function () {
    Route::post('/users/new', [UserController::class, 'create']);
    Route::get('/users/getUser', [UserController::class, 'getUser']);
});