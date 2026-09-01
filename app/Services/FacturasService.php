<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\FacturaVista;
use App\Models\Compania;
use App\Models\FacturaHub;
use App\Models\DocumentoRecepcion;

class FacturasService
{
    public function lookAndUpdateStateDocument(){
        /*1 = Aceptado
        2 = Rechazado
        3 = Procesando
        4 = Recibido
        5 = Espera descarga*/

        $companias = [
            'playa',
            'parque',
            'barrio',
            'lc',
            //'panera',
            'desarrollos',
            'lico32',
            'pasion',
            //'rancho',
            'costasur',
            'ps'
        ];

        try {

            FacturaHub::where('estado_recepcion', '!=', 1)
                ->orderBy('id')
                ->chunk(500, function ($facturas) use ($companias) {
            
                    // Consecutivos que estamos buscando
                    $consecutivos = $facturas
                        ->pluck('numero_consecutivo')
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();
            
                    foreach ($companias as $conexion) {
            
                        $modelo = new DocumentoRecepcion();
                        $modelo->setConnection($conexion);
            
                        $resultados = $modelo
                            ->whereIn('consecutivo_documento', $consecutivos)
                            ->get([
                                'consecutivo_documento',
                                'estado_recepcion',
                            ]);
            
                        foreach ($resultados as $resultado) {
            
                            FacturaHub::where(
                                'numero_consecutivo',
                                $resultado->consecutivo_documento
                            )->update([
                                'estado_recepcion' => $resultado->estado_recepcion,
                            ]);
                        }
                    }
                });

        } catch (\Throwable $e) {
            Log::error('Error actualizando estados de facturas', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea'   => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
        
    }
    
    public function guardarFacturas(){

        $compania = Compania::first();

        try {
            $facturas = FacturaVista::where('fecha_creacion', '>=', '2026-08-28')->orderBy('fecha_creacion')->chunk(500, function($facturas) use ($compania){
                $registros = [];
                foreach ($facturas as $key => $factura) {
                    $registros[] = [
                        'clave' =>              $factura->clave_hacienda,
                        'numero_consecutivo'   => $factura->consecutivo_hacienda,
                        'fecha_emision'         => $factura->fecha_factura,
                        'emisor_identificacion' => $compania->identificacion,
                        'emisor_nombre'         => $compania->razon_social,
                        'emisor_nombre_comercial' => $compania->razon_comercial,
                        'receptor_identificacion' => $factura->identificacion_fe,
                        'receptor_nombre'       => $factura->nombre_cliente,
                        'receptor_nombre_comercial' => $factura->nombre_cliente,
                        'condicion_venta'       => 01,
                        'plazo_credito'         => 0,
                        'moneda'                => 'CRC',
                        'tipo_cambio'           => $factura->tipo_cambio,
                        'total_gravado'         => $factura->monto_gravado,
                        'total_venta_neta'      => $factura->monto_neto_col,
                        'total_impuesto'        => $factura->monto_iv_col,
                        'total_comprobante'     => $factura->monto_neto_col,
                        'fecha_registro'        => $factura->fecha_aplicacion,
                    ];
                }
                if (!empty($registros)) {
                    FacturaHub::insertOrIgnore($registros);
                }
            });
    
            
        } catch (\Throwable $e) {

            Log::error('Error procesando facturas', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea'   => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            throw Command::FAILURE;
        }
    }
}