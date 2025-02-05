<?php
use App\Http\Controllers\EstacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/estaciones', [EstacionController::class, 'index']);
Route::get('/estaciones/{id}', [EstacionController::class, 'show'])->where('id', '^[1-9]$|^[1-9][0-9]$|^100$'); /* Get por id solo del 1 al 100 y positivos*/
Route::post('/post', [EstacionController::class, 'store']); /* post */
Route::delete('/delete/{id}', [EstacionController::class, 'destroy']); /* post */