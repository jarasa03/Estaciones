<?php
use App\Http\Controllers\EstacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas relacionadas con "Estaciones"
Route::get('/estacion', [EstacionController::class, 'listarEstaciones']); // Obtener todas las estaciones
Route::get('/estacion/{id}', [EstacionController::class, 'obtenerEstacion']); // Obtener una estación específica por su ID
Route::post('/estacion/{id}', [EstacionController::class, 'moverEstacionAEstacionBd']); // Anñadir una nueva estación desde estacion_inv a estacion_bd
Route::put('/estacion/{id}', [EstacionController::class, 'actualizarEstadoEstacion'])->name('estaciones.actualizarEstado'); // Cambiarle el estado a una estación
Route::delete('/estacion/{id}', [EstacionController::class, 'eliminarEstacion']); // Eliminar una estación por su ID de estacion_bd