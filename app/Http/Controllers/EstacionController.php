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
     * Obtiene y devuelve la lista de todas las estaciones con su estado asociado.
     *
     * Este método recupera todas las estaciones desde la base de datos,
     * incluyendo su relación con la tabla de estados, y transforma los datos en un formato simplificado.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta en formato JSON con la lista de estaciones o un error en caso de fallo.
     *
     * @throws \Throwable Captura cualquier excepción y devuelve un error con código 500.
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

    /**
     * Almacena una nueva estación en la base de datos.
     *
     * Este método crea un nuevo registro en la tabla `estacion_inv` con los datos proporcionados
     * y luego crea un registro asociado en la tabla `estacion_bd` para gestionar su estado.
     *
     * @param Request $request Contiene los datos de la estación a crear:
     *  - string $nombre     Nombre de la estación.
     *  - string $idema      Identificador de la estación.
     *  - string $provincia  Provincia donde se ubica la estación.
     *  - float  $x          Latitud de la estación.
     *  - float  $y          Longitud de la estación.
     *  - int    $altitud    Altitud de la estación.
     *  - int    $estado     Estado de la estación (activo/inactivo).
     *
     * @return \Illuminate\Http\JsonResponse Respuesta en formato JSON con los datos de la estación creada o un error en caso de fallo.
     *
     * @throws \Throwable Captura cualquier excepción y devuelve un error con código 500.
     */
    public function store(Request $request)
    {
        try {
            // Crear una nueva estación en la tabla estacion_inv
            $estacion = new EstacionInv();
            $estacion->nombre = $request->nombre;
            $estacion->idema = $request->idema;
            $estacion->provincia = $request->provincia;
            $estacion->latitud = $request->x; // Guardamos la latitud
            $estacion->longitud = $request->y; // Guardamos la longitud
            $estacion->altitud = $request->altitud;
            $estacion->save();
            $estacion->refresh(); // Recargar el modelo para obtener el ID generado

            // Crear una entrada en estacion_bd con el mismo ID
            $estacionBd = new EstacionBd();
            $estacionBd->id = $estacion->id; // Asociamos el mismo ID de estacion_inv
            $estacionBd->estado = $request->estado; // Guardamos el estado (activo/inactivo)
            $estacionBd->save();
            $estacionBd->refresh(); // Recargar el modelo

            // Retornar la respuesta con los datos creados
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
            // Registrar el error y retornar una respuesta con código 500
            Log::error('Error al insertar la estación: ' . $e->getMessage());
            return response()->json(['error' => "Error al insertar la estación: " . $e->getMessage()], 500);
        }
    }


    /**
     * Obtiene los datos de una estación por su ID.
     *
     * Este método busca en la base de datos una estación con el ID proporcionado.
     * Si el ID no es un número válido, retorna un error 500.
     * Si la estación no existe, retorna un error 404.
     * En caso contrario, devuelve los datos de la estación en formato JSON con estado 201.
     *
     * @param mixed $id El identificador de la estación (se validará como entero).
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con los datos de la estación o un mensaje de error.
     */
    public function show($id)
    {
        try {
            // Validamos que el ID sea un número entero
            if (!ctype_digit(strval($id))) {
                Log::error('Error al obtener la estación: ID inválido.');
                return response()->json(['error' => 'Error al obtener estación'], 500);
            }

            // Convertimos el ID a entero después de validar
            $id = (int) $id;

            // Buscamos la estación en la base de datos o lanzamos una excepción si no se encuentra
            $estacion = EstacionInv::findOrFail($id);

            // Buscamos el estado en la tabla asociada
            $estado = EstacionBd::where('id', $id)->first();

            // Registramos los datos obtenidos en el log
            Log::info("Datos de estación: " . json_encode($estacion));
            Log::info("Datos de estado: " . json_encode($estado));

            // Estructuramos la respuesta con los datos de la estación
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

            // Retornamos la información de la estación en formato JSON con código 201
            return response()->json($datos, 201);
        } catch (ModelNotFoundException $e) {
            // Si la estación no se encuentra, retornamos un error 404
            return response()->json(['error' => 'Estación no encontrada'], 404);
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
