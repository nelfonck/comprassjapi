<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\RazonSocial;

class RazonSocialController extends Controller
{
    public function getRazonSocialList(Request $request){
        $result = RazonSocial::where('activo', true)->get();
        return response()->json([
            'statusCode' => 200,
            'message' => 'Consulta realizada correctamente',
            'data' => $result,
        ], 200);
    }

    public function saveRazonSocial(Request $request){
        $validator = Validator::make($request->all(),[
            'identificacion' => 'required',
            'tipo_identificacion' => 'required',
            'nombre' => 'required',
            'nombre_comercial' => 'required',
            'correo' => 'required',
            'telefono' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 400);
        }
        $razonSocial = new RazonSocial();

        $razonSocial->identificacion = $request->identificacion;
        $razonSocial->tipo_identificacion = $request->tipo_identificacion;
        $razonSocial->nombre = $request->nombre;
        $razonSocial->nombre_comercial = $request->nombre_comercial;
        $razonSocial->correo = $request->correo;
        $razonSocial->telefono = $request->telefono;
        $razonSocial->fecha_registro = now();       
        $razonSocial->activo = true;
        
        $razonSocial->save();

        if ($razonSocial->save()) {
            return response()->json([
                'statusCode' => 200,
                'message' => 'Razón social creada correctamente',
                'data' => $razonSocial
            ]);
        }
        
        return response()->json([
            'statusCode' => 500,
            'message' => 'No fue posible guardar la razón social'
        ], 500);
    }
}
