<?php

namespace App\Http\Controllers;

use App\Models\Estacion;
use Exception;
use App\Models\EstacionInv;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Return_;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        } catch (Exception $e) {
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $estacion = new EstacionInv();
            $estacion->nombre = $request->nombre;
            $estacion->idema = $request->idema;
            $estacion->provincia = $request->provincia;
            $estacion->latitud = $request->x;
            $estacion->longitud = $request->y;
            $estacion->altitud = $request->altitud;
            $estacion->save();
            return response()->json($request, 201);
        } catch (Exception $e) {
            return response()->json(['error' => "error al importar las estaciones"], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            // Intentamos obtener la estación por ID o lanzamos una excepción si no se encuentra
            $estacion = EstacionInv::with('estado')->findOrFail($id);

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
            return response()->json(['error' => 'Estación no encontrada'], 404);
        } catch (Exception $e) {
            // Capturamos el error general y lo registramos en el log
            Log::error('Error al obtener estación: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener estación'], 500);
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
