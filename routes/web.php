<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\UserController;


Route::get('/users', [UserController::class, 'index']);
Route::get('/facilities', [FacilityController::class, 'index']);
