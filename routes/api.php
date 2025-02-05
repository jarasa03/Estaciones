<?php
use App\Http\Controllers\EstacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/estaciones', [EstacionController::class, 'index']);
Route::get('/estaciones/{id}', [EstacionController::class, 'show']); /* Get por id */
Route::post('/post', [EstacionController::class, 'store']); /* post */
Route::delete('/delete/{id}', [EstacionController::class, 'destroy']); /* post */