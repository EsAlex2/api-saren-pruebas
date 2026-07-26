<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/**
 * API Routes 
 */

//Rutas publicas
Route::post('/users/new', [UserController::class, 'create']);
Route::get('/users/getUser', [UserController::class, 'getUser']);