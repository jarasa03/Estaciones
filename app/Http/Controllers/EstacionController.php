<?php

namespace App\Http\Controllers;

use App\Models\EstacionBd;
use App\Models\EstacionInv;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Throwable;

class EstacionController extends Controller
{

    /**
     * Muestra la ficha de una estación basada en el ID proporcionado.
     *
     * Este método obtiene la información de la estación desde una fuente externa (probablemente una API o servicio),
     * decodifica la respuesta en formato JSON y la pasa a la vista correspondiente. Si no se encuentra la estación,
     * se aborta la solicitud con un error 404.
     *
     * @param  int  $id  El ID de la estación cuya información se desea mostrar.
     * @return \Illuminate\View\View  La vista con los datos de la estación o un error 404 si no se encuentra.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException  Si la estación no es encontrada, se aborta con un error 404.
     */
    public function fichaEstacion($id)
    {
        // Llamar al método obtenerEstacion para obtener los datos en formato JSON
        $response = $this->obtenerEstacion($id);

        // Decodificar la respuesta JSON para convertirla en un array
        $estacion = json_decode($response->getContent(), true);

        // Verificar si la estación fue encontrada y la información fue procesada correctamente
        if (isset($estacion['error'])) {
            abort(404, 'Estación no encontrada');
        }

        // Pasar los datos como un array a la vista
        return view('estaciones.FichaEstacion', compact('estacion'));
    }


    /**
     * Muestra una lista paginada de estaciones.
     *
     * @return \Illuminate\View\View Vista con la lista de estaciones paginada.
     */
    public function index()
    {
        // Llamar a la función listarEstaciones y obtener los datos como array
        $estaciones = $this->listarEstaciones()->getData();

        // Retornar la vista pasando las estaciones como array
        return view('estaciones.ListaEstaciones', compact('estaciones'));
    }


    /**
     * Valida que el ID proporcionado sea un número entero válido.
     *
     * Esta función verifica si el ID proporcionado es un valor entero válido utilizando el filtro FILTER_VALIDATE_INT. 
     * Si el ID no es válido, se registra un error en los logs y se aborta la solicitud con un código de estado 400.
     *
     * @param mixed $id El ID a validar, que puede ser cualquier tipo.
     * @return int El ID validado como entero.
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Si el ID no es válido, se lanza un error con código 400.
     */
    private function validarIdEntero($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            Log::error("ID inválido recibido: {$id}");
            abort(400, "ID inválido");
        }
        return (int) $id;
    }

    /**
     * Obtiene una lista de estaciones que existen en la tabla estacion_bd 
     * y complementa su información con los datos de estacion_inv.
     *
     * @return \Illuminate\Http\JsonResponse JSON con la lista de estaciones filtradas.
     *
     * @throws \Throwable Captura cualquier excepción y devuelve un error 500 en caso de fallo.
     */
    public function listarEstaciones()
    {
        try {
            // Obtener solo los IDs que existen en estacion_bd
            $idsEstacionesBd = EstacionBd::pluck('id')->toArray();

            // Obtener las estaciones de estacion_inv que coincidan con los IDs de estacion_bd
            $estaciones = EstacionInv::whereIn('id', $idsEstacionesBd)->get()->map(function ($estacionInv) {
                // Obtener el estado desde estacion_bd (relacionado por el mismo ID)
                $estadoBd = EstacionBd::find($estacionInv->id);

                return [
                    'id' => $estacionInv->id,
                    'nombre' => $estacionInv->nombre,
                    'provincia' => $estacionInv->provincia,
                    'idema' => $estacionInv->idema,
                    'x' => $estacionInv->latitud,
                    'y' => $estacionInv->longitud,
                    'altitud' => $estacionInv->altitud,
                    'estado' => $estadoBd && $estadoBd->estado == 1 ? 'active' : 'inactive', // Tomamos el estado desde estacion_bd
                ];
            });

            return response()->json($estaciones, 201);
        } catch (Throwable $e) {
            Log::error('Error al obtener estaciones: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener estaciones'], 500);
        }
    }

    /**
     * Mueve una estación de la tabla `estacion_inv` a la tabla `estacion_bd` si no existe previamente.
     *
     * Este método valida el ID de la estación, verifica si ya está en `estacion_bd` y, si no es así,
     * intenta moverla desde `estacion_inv` a `estacion_bd`. Si la estación ya está en `estacion_bd`,
     * se retorna un mensaje indicando que no es necesario moverla. Si la estación no se encuentra en
     * `estacion_inv`, se retorna un error 404.
     *
     * @param  int  $id  El ID de la estación a mover.
     * @return \Illuminate\Http\JsonResponse  Respuesta JSON indicando el estado de la operación.
     *
     * @throws \Exception  Si ocurre un error al mover la estación o al interactuar con la base de datos.
     */
    public function moverEstacionAEstacionBd($id): JsonResponse
    {
        try {
            Log::info("Intentando mover estación con ID: {$id}");

            // Validamos el ID antes de continuar
            $id = $this->validarIdEntero($id);
            Log::info("ID validado correctamente: {$id}");

            // Buscamos la estación en estacion_bd
            $estacionBd = EstacionBd::find($id);

            // Si la estación ya existe en estacion_bd, retornamos un mensaje indicando que no es necesario moverla
            if ($estacionBd) {
                Log::info("La estación con ID {$id} ya existe en estacion_bd");
                return response()->json(["message" => "La estación ya existe en estacion_bd"], 400);
            }

            // Si no está en estacion_bd, buscamos en estacion_inv
            $estacionInv = EstacionInv::find($id);

            if (!$estacionInv) {
                // Si la estación no se encuentra en estacion_inv, retornamos un error 404
                Log::warning("No se encontró la estación con ID {$id} en estacion_inv");
                return response()->json(["message" => "La estación no existe en estacion_inv"], 404);
            }

            // Si la estación existe en estacion_inv, la insertamos en estacion_bd
            $nuevaEstacionBd = new EstacionBd();
            $nuevaEstacionBd->id = $estacionInv->id;
            $nuevaEstacionBd->estado = $estacionInv->estado; // Suponiendo que el estado es un campo en estacion_inv

            // Guardamos la nueva estación en estacion_bd
            $nuevaEstacionBd->save();

            Log::info("Estación con ID {$id} movida correctamente de estacion_inv a estacion_bd");

            return response()->json(["message" => "Estación movida correctamente a estacion_bd"], 200);
        } catch (Exception $e) {
            Log::error("Error al mover estación con ID {$id}: " . $e->getMessage());
            return response()->json(["error" => "Error al mover estación con id {$id}"], 500);
        }
    }

    /**
     * Obtiene los datos de una estación por su ID.
     *
     * Este método busca en la base de datos una estación con el ID proporcionado. 
     * Si el ID no es válido (no es un número entero), se retorna un error 500.
     * Si la estación no existe, se retorna un error 404. Si se encuentra la estación,
     * se devuelve una respuesta en formato JSON con los detalles de la estación, 
     * incluyendo su estado (activo/inactivo).
     *
     * @param mixed $id El identificador de la estación (se validará como entero).
     * 
     * @return \Illuminate\Http\JsonResponse Respuesta en formato JSON con los datos de la estación, 
     * o un mensaje de error si la estación no se encuentra o si hay un problema con el ID.
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Si la estación no se encuentra.
     */
    public function obtenerEstacion($id)
    {
        try {
            // Validamos el ID antes de continuar
            Log::info("Validando ID de la estación: {$id}");
            $id = $this->validarIdEntero($id);

            // Intentamos obtener la estación con el ID proporcionado
            $estacion = EstacionInv::with('estado')->findOrFail($id);

            // Log de éxito con detalles de la estación
            Log::info("Datos de estación obtenidos correctamente: ID: {$estacion->id}, Nombre: {$estacion->nombre}");

            // Retornamos la respuesta
            return response()->json([
                'id' => $estacion->id,
                'nombre' => $estacion->nombre,
                'provincia' => $estacion->provincia,
                'idema' => $estacion->idema,
                'x' => $estacion->latitud,
                'y' => $estacion->longitud,
                'altitud' => $estacion->altitud,
                'estado' => $estacion->estado ? ($estacion->estado->estado == 1 ? 'active' : 'inactive') : 'inactive'
            ], 201);
        } catch (ModelNotFoundException $e) {
            // Si la estación no se encuentra, logueamos el error y retornamos 404
            Log::error("Estación no encontrada para ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'Estación no encontrada'], 404);
        } catch (Exception $e) {
            // Log de errores generales en caso de que ocurra alguna otra excepción
            Log::error("Error al obtener datos de la estación con ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'Error al obtener datos de la estación'], 500);
        }
    }

    /**
     * Actualiza el estado de una estación en la base de datos.
     *
     * Esta función valida el campo 'estado' en el request, verifica si solo se ha enviado ese campo,
     * y luego actualiza el estado de la estación en la tabla 'estacion_bd'. Si el estado actual de la
     * estación ya es el mismo que el que se quiere establecer, se retorna un mensaje indicando que no
     * es necesario hacer la actualización. Si se encuentra algún error, se captura la excepción y se
     * retorna un mensaje de error adecuado.
     *
     * @param \Illuminate\Http\Request $request El objeto de la solicitud HTTP que contiene el campo 'estado'.
     * @param int $id El ID de la estación a actualizar.
     *
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con un mensaje de éxito o error.
     */
    public function actualizarEstadoEstacion(Request $request, $id)
    {
        try {
            // Validar que solo el campo 'estado' esté presente y sea booleano
            $request->validate([
                'estado' => 'required|boolean',
            ]);
    
            // Buscar la estación en la base de datos
            $estacionBd = EstacionBd::find($id);
    
            if (!$estacionBd) {
                Log::warning("No se encontró estación con ID {$id} en la base de datos.");
                return redirect()->back()->with('error', "La estación {$id} no existe en estacion_bd");
            }
    
            // Verificar si el estado actual ya es el mismo que el solicitado
            if ($estacionBd->estado === $request->estado) {
                return redirect()->back()->with('message', "El estado ya está configurado como se desea");
            }
    
            // Actualizar estado de la estación
            $estacionBd->estado = $request->estado;
            $estacionBd->save();
    
            return redirect()->back()->with('message', "Estado actualizado correctamente");
        } catch (Exception $e) {
            Log::error("Error al actualizar el estado de la estación con ID {$id}: " . $e->getMessage());
            return redirect()->back()->with('error', "Error al actualizar el estado de la estación");
        }
    }


    /**
     * Elimina una estación de la base de datos en la tabla `estacion_bd` usando su ID.
     *
     * Este método valida el ID de la estación, luego intenta encontrar la estación
     * en la tabla `estacion_bd`. Si la estación es encontrada, se elimina de la base de datos.
     * Si la estación no se encuentra o ocurre algún error durante el proceso, se devuelve
     * un mensaje adecuado en formato JSON.
     *
     * @param  int  $id  El ID de la estación a eliminar.
     * @return \Illuminate\Http\JsonResponse  Respuesta JSON con el resultado de la operación.
     * 
     * @throws \Exception Si ocurre un error durante el proceso de eliminación.
     */
    public function eliminarEstacion($id): JsonResponse
    {
        try {
            Log::info("Intentando eliminar estación con ID: {$id}");

            // Validamos el ID antes de continuar
            $id = $this->validarIdEntero($id);
            Log::info("ID validado correctamente: {$id}");

            // Buscamos la estación en la tabla estacion_bd
            $estacionBd = EstacionBd::find($id);

            // Si no se encuentra en estacion_bd, retornamos un error 404
            if (!$estacionBd) {
                Log::warning("No se encontró ninguna estación con ID {$id} en estacion_bd");
                return response()->json(["message" => "La estación {$id} no existe en estacion_bd"], 404);
            }

            Log::info("Estación obtenida de estacion_bd: " . $estacionBd);

            // Intentamos eliminar la estación de la tabla estacion_bd
            $deleted = $estacionBd->delete();

            if ($deleted) {
                Log::info("Estación eliminada de la tabla estacion_bd con ID {$id}");
            } else {
                Log::warning("No se pudo eliminar la estación con ID {$id} de estacion_bd");
            }

            Log::info("Estación con ID {$id} eliminada correctamente");

            return response()->json(["message" => "Estación {$id} eliminada correctamente"], 200);
        } catch (Exception $e) {
            Log::error("Error al eliminar estación con ID {$id}: " . $e->getMessage());
            return response()->json(["error" => "Error al eliminar estación con id {$id}"], 500);
        }
    }
}
