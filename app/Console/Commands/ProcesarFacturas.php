<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FacturaVista;
use App\Models\Compania;
use App\Models\FacturaHub;

class ProcesarFacturas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturas:procesar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa las facturas pendientes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
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
    
            return Command::SUCCESS;
            
        } catch (\Throwable $th) {

            Log::error('Error procesando facturas', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea'   => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
