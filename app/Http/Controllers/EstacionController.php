<?php

namespace App\Http\Controllers;

use App\Models\Estacion;
use App\Models\EstacionInv;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Return_;

class EstacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        {
            try {
             
                $estaciones = EstacionInv::with('estado')->get();
                $datos = $estaciones->map(function ($estacion) {
                    return [
                        'id' => $estacion->id,
                        'nombre' => $estacion->nombre,
                        'provincia' => $estacion->provincia,
                        'idema' => $estacion->idema,
                        'x' => $estacion->latitud,
                        'y' => $estacion->longitud,
                        'altitud' => $estacion->altitud,
                        'estado' => optional($estacion->estado)->estado // Evita error si no tiene estado
                    ];
                });
        
                return response()->json($datos, 201); 
            } catch (\Exception $e) {
                return response()->json(['error' => 'Error al obtener estaciones'], 500);
            }
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
        try{

            $estacion= new EstacionInv();
            $estacion->nombre=$request->nombre;
            $estacion->idema=$request->idema;
            $estacion->provincia=$request->provincia;
            $estacion->latitud=$request->x;
            $estacion->longitud=$request->y;
            $estacion->altitud=$request->altitud;
            $estacion->save();
            return response()->json($request,201);
        }catch(\Exception $e){
            return response()->json(['error'=>"error al importar las estaciones"],500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Obtener  valores
            $estaciones = EstacionInv::with('estado')->find($id);
            $datos =[
                'id' => $estaciones->id,
                'nombre' => $estaciones->nombre,
                'provincia' => $estaciones->provincia,
                'idema' => $estaciones->idema,
                'x' => $estaciones->latitud,
                'y' => $estaciones->longitud,
                'altitud' => $estaciones->altitud,
                'estado' => optional($estaciones->estado)->estado, // Evita error si no tiene estado
            ];

            return response()->json($datos, 201); 
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener estaciones'], 500);
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
        }catch(\Exception $e){
            return response()->json(["error" => "Error al eliminar"],500);
        }
    }
}
