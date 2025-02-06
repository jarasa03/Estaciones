<?php

namespace App\Http\Controllers;

use App\Models\EstacionBd;
use Exception;
use App\Models\EstacionInv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable; // Importar Throwable

class EstacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Se obtienen todas las estaciones junto con su estado relacionado
            // "with('estado')" hace que se cargue la relación 'estado' (esto evita consultas extra)
            $estaciones = EstacionInv::with('estado')->get()->transform(function ($estacion) {
                // Aquí estamos creando una nueva estructura para cada estación.
                return [
                    // Estos son los atributos de la estación que queremos devolver
                    'id' => $estacion->id,
                    'nombre' => $estacion->nombre,
                    'provincia' => $estacion->provincia,
                    'idema' => $estacion->idema,
                    'x' => $estacion->latitud,
                    'y' => $estacion->longitud,
                    'altitud' => $estacion->altitud,
                    // Aquí manejamos el estado de la estación.
                    // Si la estación tiene un estado (relación cargada con 'estado'),
                    // se comprueba si ese estado es 1. Si es 1, asignamos 'active', si no, 'inactive'.
                    // Si no tiene estado asociado, también se devuelve 'inactive'.
                    'estado' => $estacion->estado ? ($estacion->estado->estado == 1 ? 'active' : 'inactive') : 'inactive'
                ];
            });
            // Finalmente, se devuelve la colección de estaciones en formato JSON con un código de respuesta 201
            return response()->json($estaciones, 201);
        } catch (Throwable $e) {
            // Si ocurre algún error durante el proceso, lo capturamos aquí
            // Se guarda un log con el mensaje de error y se devuelve un error genérico
            Log::error('Error al obtener estaciones: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener estaciones'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try {
            // Crear la nueva estación en estacion_inv
            $estacion = new EstacionInv();
            $estacion->nombre = $request->nombre;
            $estacion->idema = $request->idema;
            $estacion->provincia = $request->provincia;
            $estacion->latitud = $request->x;
            $estacion->longitud = $request->y;
            $estacion->altitud = $request->altitud;
            $estacion->save();
            $estacion->refresh();

            // Insertar en estacion_bd con el mismo id y el estado proporcionado
            $estacionBd = new EstacionBd();
            $estacionBd->id = $estacion->id; // Usamos el mismo ID generado en estacion_inv
            $estacionBd->estado = $request->estado; // Activado o desactivado
            $estacionBd->save();
            $estacionBd->refresh();

            return response()->json([
                'id' => $estacion->id,
                'nombre' => $estacion->nombre,
                'idema' => $estacion->idema,
                'provincia' => $estacion->provincia,
                'latitud' => $estacion->latitud,
                'longitud' => $estacion->longitud,
                'altitud' => $estacion->altitud,
                'estado' => $estacionBd->estado
            ], 201);
        } catch (Throwable $e) {
            Log::error('Error al insertar la estación: ' . $e->getMessage());
            return response()->json(['error' => "Error al insertar la estación: " . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            // Validamos que el ID sea un número entero
            if (!ctype_digit(strval($id))) {
                return response()->json(['error' => 'Error al obtener estacion'], 500);
                Log::error('Error al obtener la estación: ' . $e->getMessage());
            }

            $id = (int) $id; // Convertimos a entero después de validar

            // Intentamos obtener la estación por ID o lanzamos una excepción si no se encuentra
            $estacion = EstacionInv::findOrFail($id);
            $estado = EstacionBd::where('id', $id)->first();

            Log::info("Datos de estación: " . json_encode($estacion));
            Log::info("Datos de estado: " . json_encode($estado));

            // Mapear los datos de la estación a una estructura más simple para la respuesta
            $datos = [
                'id' => $estacion->id,
                'nombre' => $estacion->nombre,
                'provincia' => $estacion->provincia,
                'idema' => $estacion->idema,
                'x' => $estacion->latitud,
                'y' => $estacion->longitud,
                'altitud' => $estacion->altitud,
                'estado' => $estacion->estado ? ($estacion->estado->estado == 1 ? 'active' : 'inactive') : 'inactive'
            ];

            // Retornar los datos de la estación como JSON con un código de estado 201
            return response()->json($datos, 201);
        } catch (ModelNotFoundException $e) {
            // Si la estación no es encontrada, retornar error 404
            return response()->json(['error' => 'Estacion no encontrada'], 404);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Obtener  valores
            $estaciones = EstacionInv::find($id);
            $estaciones->delete();
        } catch (Exception $e) {
            return response()->json(["error" => "Error al eliminar"], 500);
        }
    }
}
