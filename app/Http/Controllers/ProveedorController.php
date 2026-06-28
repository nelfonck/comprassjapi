<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Proveedor;

class ProveedorController extends Controller
{
    /*200 → Consulta o guardado exitoso.
    201 → Recurso creado correctamente (opcional para POST de creación).
    400 → Datos inválidos o faltan campos requeridos.
    401 → API Key inválida o no autenticado.
    404 → Registro no encontrado.
    409 → Registro duplicado.
    500 → Error inesperado del servidor.*/
    
    public function getProveedores(Request $request){
        try {
            $result = Proveedor::get();
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


    public function guardarProveedor(Request $request){
        try {
            $validator = Validator::make($request->all(),[
                'identificacion' => 'required',
                'tipo_identificacion' => 'required',
                'nombre' => 'required',
                'nombre_comercial' => 'required',
                'telefono' => 'required',
                'correo' => 'sometimes',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'statusCode' => 400,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);
            }

            if (self::_existeProveedor($request->identificacion)){
                return response()->json([
                    'statusCode' => 409,
                    'message' => 'La identificación ya existe'
                ], 409);
            }

            $proveedor = new Proveedor();
    
            $proveedor->identificacion = $request->identificacion;
            $proveedor->tipo_identificacion = $request->tipo_identificacion;
            $proveedor->nombre = $request->nombre;
            $proveedor->nombre_comercial = $request->nombre_comercial;
            $proveedor->telefono = $request->telefono;
            $proveedor->correo = $request->correo;
            $proveedor->fecha_registro = now();       
            
            $proveedor->save();
    
            if ($proveedor->save()) {
                return response()->json([
                    'statusCode' => 200,
                    'message' => 'Proveedor creado correctamente',
                    'data' => $proveedor
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'statusCode' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function existeProveedor(Request $request){
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

            $existe = self::_existeProveedor($request->identificacion);
           
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
    private function _existeProveedor(string $identificacion): bool
    {
        return Proveedor::where(
            'identificacion',
            $identificacion
        )->exists();
    }
}
