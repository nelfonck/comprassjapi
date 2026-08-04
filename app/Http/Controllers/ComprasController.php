<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Compra;
use App\Models\DetalleCompra;

class ComprasController extends Controller
{
    public function guardarCompra(Request $request){
        try {
            $validator = Validator::make($request->all(),[
                'clave' => 'required',
                'numero_consecutivo' => 'required',
                'fecha_emision' => 'required',
                'proveedor_sistemas' => 'sometimes',
                'codigo_actividad_emisor' => 'sometimes',
                'codigo_actividad_receptor' => 'sometimes',
                'emisor_identificacion' => 'required',
                'emisor_nombre' => 'required',
                'emisor_nombre_comercial' => 'required',
                'receptor_identificacion' => 'required',
                'receptor_nombre' => 'required',
                'receptor_nombre_comercial' => 'required',
                'condicion_venta' => 'required',
                'condicion_venta_otros' => 'sometimes',
                'plazo_credito' => 'sometimes',
                'moneda' => 'required',
                'tipo_cambio' => 'required',
                'total_gravado' => 'required',
                'total_venta' => 'required',
                'total_venta_neta' => 'required',
                'total_impuesto' => 'required',
                'total_comprobante' => 'required',
                'fecha_registro' => 'required',
                'detalle' => 'required|array',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'statusCode' => 400,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);
            }

            if (self::_existeCompra($request->numero_consecutivo)){
                return response()->json([
                    'statusCode' => 409,
                    'message' => 'La compra ya existe'
                ], 409);
            }

            $compra = new Compra();
    
            $compra->clave = $request->clave ;
            $compra->numero_consecutivo = $request->numero_consecutivo ;
            $compra->fecha_emision = $request->fecha_emision ;
            $compra->proveedor_sistemas = $request->proveedor_sistemas ;
            $compra->codigo_actividad_emisor = $request->codigo_actividad_emisor ;
            $compra->codigo_actividad_receptor = $request->codigo_actividad_receptor ;
            $compra->emisor_identificacion=  $request->emisor_identificacion ;
            $compra->emisor_nombre =  $request->emisor_nombre ;
            $compra->emisor_nombre_comercial  =  $request->emisor_nombre_comercial ;
            $compra->receptor_identificacion   =  $request->receptor_identificacion  ;
            $compra->receptor_nombre  =  $request->receptor_nombre ;
            $compra->receptor_nombre_comercial  =  $request->receptor_nombre_comercial ;
            $compra->condicion_venta   =  $request->condicion_venta ;
            $compra->condicion_venta_otros   =  $request->condicion_venta_otros ;
            $compra->plazo_credito  =  $request->plazo_credito ;
            $compra->moneda  =  $request->moneda ;
            $compra->tipo_cambio =  $request->tipo_cambio ;
            $compra->total_gravado =  $request->total_gravado ;
            $compra->total_venta =  $request->total_venta ;
            $compra->total_venta_neta =  $request->total_venta_neta ;
            $compra->total_impuesto =  $request->total_impuesto ;
            $compra->total_comprobante =  $request->total_comprobante ;
            $compra->fecha_registro =  $request->fecha_registro ;
            $compra->save();
            
            
            $proveedor->save();
    
            if ($proveedor->save()) {
                //TODO: SAVE DETALLECOMPRA
            }

        } catch (\Exception $e) {
            return response()->json([
                'statusCode' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function _existeCompra(string $consecutivo): bool
    {
        return Compra::where(
            'consecutivo',
            $consecutivo
        )->exists();
    }
}
