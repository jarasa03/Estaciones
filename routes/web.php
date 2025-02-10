<?php

use App\Http\Controllers\EstacionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/estaciones', [EstacionController::class, 'index']);