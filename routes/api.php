<?php
use App\Http\Controllers\EstacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas relacionadas con "Estaciones"
Route::get('/estacion', [EstacionController::class, 'index']); // Obtener todas las estaciones
Route::get('/estacion/{id}', [EstacionController::class, 'show']); // Obtener una estación específica por su ID
Route::post('/estacion', [EstacionController::class, 'store']); // Crear una nueva estación
Route::put('/estacion/{id}', [EstacionController::class, 'updateEstado']); // Actualizar una estación existente
Route::delete('/estacion/{id}', [EstacionController::class, 'destroy']); // Eliminar una estación por su ID