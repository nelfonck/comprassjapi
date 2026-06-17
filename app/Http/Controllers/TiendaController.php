<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Tienda;

class TiendaController extends Controller
{
    
    public function getTiendas(Request $request){
        try {
            $result = Tienda::where('activo', true)->get();
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

    public function guardarTienda(Request $request){
        try {
            $validator = Validator::make($request->all(),[
                'nombre' => 'required',
                'id_razon_social' => 'required',
                'telefono' => 'required',
                'correo' => 'required',
                'direccion' => 'required',
                'clave_correo' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'statusCode' => 400,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);
            }

            if (self::_existeTienda($request->nombre)){
                return response()->json([
                    'statusCode' => 409,
                    'message' => 'La tienda ya existe'
                ], 409);
            }

            $tienda = new Tienda();
    
            $tienda->nombre = $request->nombre;
            $tienda->id_razon_social = $request->id_razon_social;
            $tienda->telefono = $request->telefono;
            $tienda->correo = $request->correo;
            $tienda->direccion = $request->direccion;
            $tienda->clave_correo = $request->clave_correo;    
            
            $tienda->save();
    
            if ($tienda->save()) {
                return response()->json([
                    'statusCode' => 200,
                    'message' => 'tienda creada correctamente',
                    'data' => $tienda
                ]);
            }
            
            return response()->json([
                'statusCode' => 500,
                'message' => 'No fue posible guardar la tienda'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'statusCode' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function existeTienda(Request $request){
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 400);
        }
        try {

            $existe = self::_existeTienda($request->nombre);
           
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
    private function _existeTienda(string $nombre): bool
    {
        return Tienda::where(
            'nombre',
            $nombre
        )->exists();
    }
}
