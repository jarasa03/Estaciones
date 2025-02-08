<?php
use App\Http\Controllers\EstacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas relacionadas con "Estaciones"
Route::get('/estaciones', [EstacionController::class, 'index']); // Obtener todas las estaciones
Route::get('/estaciones/{id}', [EstacionController::class, 'show']); // Obtener una estación específica por su ID
Route::post('/estaciones', [EstacionController::class, 'store']); // Crear una nueva estación
Route::put('/estaciones/{id}', [EstacionController::class, 'update']); // Actualizar una estación existente
Route::delete('/estaciones/{id}', [EstacionController::class, 'destroy']); // Eliminar una estación por su ID