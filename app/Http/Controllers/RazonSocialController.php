<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\RazonSocial;

class RazonSocialController extends Controller
{
    /*200 → Consulta o guardado exitoso.
    201 → Recurso creado correctamente (opcional para POST de creación).
    400 → Datos inválidos o faltan campos requeridos.
    401 → API Key inválida o no autenticado.
    404 → Registro no encontrado.
    409 → Registro duplicado.
    500 → Error inesperado del servidor.*/
    
    public function getRazonSocialList(Request $request){
        try {
            $result = RazonSocial::where('activo', true)->get();
            return response()->json([
                'statusCode' => 200,
                'message' => 'Consulta realizada correctamente',
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'statusCode' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function guardarRazonSocial(Request $request){
        try {
            $validator = Validator::make($request->all(),[
                'identificacion' => 'required',
                'tipo_identificacion' => 'required',
                'nombre' => 'required',
                'nombre_comercial' => 'required',
                'correo' => 'required',
                'telefono' => 'required',
                'clave_correo' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'statusCode' => 400,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);
            }

            if (self::_existeRazonSocial($request->identificacion)){
                return response()->json([
                    'statusCode' => 409,
                    'message' => 'La identificación ya existe'
                ], 409);
            }

            $razonSocial = new RazonSocial();
    
            $razonSocial->identificacion = $request->identificacion;
            $razonSocial->tipo_identificacion = $request->tipo_identificacion;
            $razonSocial->nombre = $request->nombre;
            $razonSocial->nombre_comercial = $request->nombre_comercial;
            $razonSocial->correo = $request->correo;
            $razonSocial->telefono = $request->telefono;
            $razonSocial->fecha_registro = now();       
            $razonSocial->clave_correo = $request->clave_correo;
            $razonSocial->activo = true;
            
            $razonSocial->save();
    
            if ($razonSocial->save()) {
                return response()->json([
                    'statusCode' => 200,
                    'message' => 'Razón social creada correctamente',
                    'data' => $razonSocial
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'statusCode' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function existeRazonSocial(Request $request){
        $validator = Validator::make($request->all(),[
            'identificacion' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 400);
        }
        try {

            $existe = self::_existeRazonSocial($request->identificacion);
           
            return response()->json([
                'statusCode' => 200,
                'message' => 'Ok',
                'existe' => $existe
            ], 200);
          
        } catch (\Exception $e) {
            return response()->json([
                'statusCode' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    private function _existeRazonSocial(string $identificacion): bool
    {
        return RazonSocial::where(
            'identificacion',
            $identificacion
        )->exists();
    }
}
