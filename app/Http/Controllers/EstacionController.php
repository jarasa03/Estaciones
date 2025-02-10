<?php

namespace App\Http\Controllers;

use App\Models\EstacionBd;
use Exception;
use App\Models\EstacionInv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class EstacionController extends Controller
{
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
    private function validarId($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            Log::error("ID inválido recibido: {$id}");
            abort(400, "ID inválido");
        }
        return (int) $id;
    }

    /**
     * Función externa para validar los datos de la estación.
     *
     * @param Request $request El objeto de la solicitud HTTP.
     * @return array Los datos validados de la estación.
     * @throws ValidationException Si la validación falla.
     */
    private function validarEstacion(Request $request)
    {
        Log::info("Iniciando validación de los datos.");
        return $request->validate([
            'nombre'    => 'required|string|max:255',
            'idema'     => 'required|string|max:50',
            'provincia' => 'required|string|max:255',
            'x'         => 'required|numeric',
            'y'         => 'required|numeric',
            'altitud'   => 'required|integer',
            'estado'    => 'required|integer'
        ]);
    }


    /**
     * Obtiene y devuelve la lista de todas las estaciones con su estado asociado.
     *
     * Este método recupera todas las estaciones desde la base de datos,
     * incluyendo su relación con la tabla de estados, y transforma los datos en un formato simplificado.
     * Cada estación incluye su ID, nombre, provincia, identificador, coordenadas (latitud y longitud), 
     * altitud, y el estado de la estación (activo/inactivo).
     *
     * @return \Illuminate\Http\JsonResponse Respuesta en formato JSON con la lista de estaciones o un error en caso de fallo.
     * 
     * @throws \Throwable Captura cualquier excepción durante el proceso y devuelve un error con código 500.
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
     * Almacena una nueva estación en la base de datos.
     *
     * Este método crea un nuevo registro en la tabla `estacion_inv` con los datos proporcionados
     * y luego crea un registro asociado en la tabla `estacion_bd` para gestionar su estado.
     *
     * La función valida los datos de entrada antes de procesarlos, y en caso de error de validación, 
     * devuelve una respuesta con detalles específicos del error.
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
     * @throws \Illuminate\Validation\ValidationException Si los datos no pasan la validación, se lanza una excepción con los errores específicos de validación.
     * @throws \Throwable Captura cualquier excepción no controlada y devuelve un error con código 500.
     */
    public function store(Request $request)
    {
        try {
            // Validación de datos
            Log::info("Validando datos de la estación.");
            $data = $this->validarEstacion($request);
            Log::info("Datos validados correctamente.", ['data' => $data]);
        } catch (ValidationException $e) {
            // Si la validación falla, captura la excepción y devuelve un error personalizado
            Log::error('Error al validar los datos: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json([
                'error' => 'Datos inválidos',
                'message' => 'Por favor revisa los campos proporcionados.',
                'details' => $e->errors() // Devuelve los errores específicos de la validación
            ], 422); // Código de estado 422 para errores de validación
        }
        try {
            // Crear una nueva estación en la tabla estacion_inv
            Log::info("Creando nueva estación en la tabla 'estacion_inv'.");
            $estacion = new EstacionInv();
            $estacion = new EstacionInv();
            $estacion->nombre = $data['nombre'];
            $estacion->idema = $data['idema'];
            $estacion->provincia = $data['provincia'];
            $estacion->latitud = $data['x']; // Guardamos la latitud
            $estacion->longitud = $data['y']; // Guardamos la longitud
            $estacion->altitud = $data['altitud'];
            $estacion->save();
            $estacion->refresh(); // Recargar el modelo para obtener el ID generado

            Log::info("Estación creada en 'estacion_inv' con ID: {$estacion->id}");

            // Crear una entrada en estacion_bd con el mismo ID
            Log::info("Creando nueva entrada en la tabla 'estacion_bd' con el ID: {$estacion->id}.");
            $estacionBd = new EstacionBd();
            $estacionBd->id = $estacion->id; // Asociamos el mismo ID de estacion_inv
            $estacionBd->estado = $data['estado']; // Guardamos el estado (activo/inactivo)
            $estacionBd->save();
            $estacionBd->refresh(); // Recargar el modelo

            Log::info("Entrada creada en 'estacion_bd' con estado: {$estacionBd->estado}");

            // Retornar la respuesta con los datos creados
            Log::info("Retornando datos de la estación creada.");
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
            Log::error('Error al insertar la estación: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => "Error al insertar la estación: " . $e->getMessage()], 500);
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
    public function show($id)
    {
        try {
            // Validamos el ID antes de continuar
            Log::info("Validando ID de la estación: {$id}");
            $id = $this->validarId($id);

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
    public function updateEstado(Request $request, $id)
    {
        try {
            // Validar que solo el campo 'estado' esté presente y sea booleano
            $request->validate([
                'estado' => 'required|boolean',
            ]);

            // Verificar si hay algún otro campo en el request
            if (count($request->all()) > 1) {
                Log::warning("Request contiene campos no permitidos. Solo se debe incluir 'estado'.");
                return response()->json(["message" => "Solo se permite editar el campo 'estado'"], 400);
            }

            // Validar y buscar la estación en la base de datos
            Log::info("Buscando estación con ID {$id} en la base de datos...");
            $estacionBd = EstacionBd::find($id);

            // Si no se encuentra la estación
            if (!$estacionBd) {
                Log::warning("No se encontró estación en estacion_bd con ID {$id}. El ID proporcionado no existe.");
                return response()->json(["message" => "La estación {$id} no existe en estacion_bd"], 404);
            }

            // Verificar si el estado actual ya es el mismo que el que se quiere establecer
            if ($estacionBd->estado === $request->estado) {
                Log::info("No se requiere actualización. El estado actual de la estación {$id} ya es {$request->estado}.");
                return response()->json(["message" => "El estado ya está configurado como se quiere para la estación {$id}"], 200);
            }

            // Actualizar el estado en estacion_bd
            Log::info("Actualizando el estado de la estación {$id} a {$request->estado}...");
            $estacionBd->estado = $request->estado;
            $estacionBd->save();

            Log::info("Estado actualizado correctamente en estacion_bd. Estación con ID {$id} ahora tiene el estado {$request->estado}.");

            return response()->json(["message" => "Estado actualizado correctamente para la estación {$id}"], 200);
        } catch (\Exception $e) {
            Log::error("Error al actualizar la estación con ID {$id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(["error" => "Error al actualizar estación con ID {$id}"], 500);
        }
    }

    /**
     * Elimina una estación de la base de datos si existe en alguna de las dos tablas (`estacion_bd` o `estacion_inv`).
     *
     * Este método intenta eliminar la estación con el ID proporcionado, buscando primero en la tabla `estacion_bd`
     * y luego en la tabla `estacion_inv`. Si no se encuentra la estación en ninguna de las tablas, se retorna un error 404.
     * Si se encuentra y elimina correctamente, se devuelve un mensaje de éxito.
     * En caso de cualquier error, se retorna un mensaje de error 500.
     *
     * @param int|string $id El ID de la estación a eliminar. Puede ser un número entero o una cadena que represente el ID.
     * 
     * @return \Illuminate\Http\JsonResponse Respuesta en formato JSON con el resultado de la operación.
     * 
     * @throws \Exception Si ocurre un error durante el proceso de eliminación, se captura y retorna un error con código 500.
     */
    public function destroy($id): JsonResponse
    {
        try {
            Log::info("Intentando eliminar estación con ID: {$id}");

            // Validamos el ID antes de continuar
            $id = $this->validarId($id);
            Log::info("ID validado correctamente: {$id}");

            $estacionBd = EstacionBd::find($id);
            $estacionInv = EstacionInv::find($id);

            // Si no se encuentra en ninguna tabla, retornamos un error 404
            if (!$estacionBd && !$estacionInv) {
                Log::warning("No se encontró ninguna estación con ID {$id}");
                return response()->json(["message" => "La estación {$id} no existe"], 404);
            }

            // Eliminamos si existen
            if ($estacionBd) {
                $estacionBd->delete();
                Log::info("Estación eliminada de la tabla estacion_bd con ID {$id}");
            }

            if ($estacionInv) {
                $estacionInv->delete();
                Log::info("Estación eliminada de la tabla estacion_inv con ID {$id}");
            }

            Log::info("Estación con ID {$id} eliminada correctamente");

            return response()->json(["message" => "Estación {$id} eliminada correctamente"], 200);
        } catch (Exception $e) {
            Log::error("Error al eliminar estación con ID {$id}: " . $e->getMessage());
            return response()->json(["error" => "Error al eliminar estación con id {$id}"], 500);
        }
    }
}
