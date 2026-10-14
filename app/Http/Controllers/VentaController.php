<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Compania;
use App\Models\Factura;
use App\Models\HistorialFactura;
use App\Models\DetalleFactura;
use App\Models\HistorialDetalleFactura;
use App\Models\CXC;
use App\Models\RazonSocial;
use App\Helpers\Constantes;
use App\Models\ViewFacturas3;
use App\Models\DetalleFacturasNotaCredito;
use App\Models\NotaCredito;
use App\Models\NotaDebito;


class VentaController extends Controller
{
    public function getVentas(Request $request)
    {
        try {
    
            $validator = Validator::make($request->all(), [
                'fecha_inicio' => 'required',
                'fecha_fin' => 'sometimes',
                'rs' => 'required'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'statusCode' => 400,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 400);
            }
                
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');
            //Si local viene con el id -1 definimos nula la variable para no contemplarla en el query
            $rs = $request->rs;

            if (is_string($rs)) {
                $rs = json_decode($rs,true);
            }

            if ($rs['id']==-1){
                $rs = null;
            } 

           // $local = $request->input('local');
            $conexiones = Constantes::$conexiones;

            $registros = [];

            foreach ($conexiones as $key => $conexion) {
                # code...
                /*
                 * FACTURAS DE LAS DOS TABLAS
                 */
                $compania = Compania::on($conexion)->select(
                    'identificacion',
                    'razon_social',
                    'razon_comercial'
                )->first();
                
                //si tienda no viene null lo limitamos a solo su respectiva conexion
                if ($rs!=null){
                    if ($rs['identificacion'] != $compania['identificacion']){
                        continue;
                    }
                }
                /*
                |--------------------------------------------------------------------------
                | 1. Notas de crédito aplicadas a las facturas
                |--------------------------------------------------------------------------
                */

                $notasAplicadas = DetalleFacturasNotaCredito::query()
                    ->from('detalle_facturas_nota_credito as d')
                    ->join('nota_credito as n', function ($join) {
                        $join->on(
                            'n.codigo_nota_credito',
                            '=',
                            'd.nota_credito'
                        );
                    })
                    ->leftJoin('nota_debito as nd', function ($join) {
                        $join->on(
                            'nd.nota_credito_reversada',
                            '=',
                            'n.codigo_nota_credito'
                        );
                    })
                    ->where('n.estado', '1')
                    ->select('d.factura')
                    ->selectRaw('
                        SUM(
                            d.monto_aplica - COALESCE(nd.subtotal_con_imp, 0)
                        ) AS monto_nc
                    ')
                    ->groupBy('d.factura');


                /*
                |--------------------------------------------------------------------------
                | 2. Facturas con el cálculo de aplicación de NC
                |--------------------------------------------------------------------------
                */

                $facturasBase = ViewFacturas3::query()
                    ->from('view_facturas3 as vf')
                    ->leftJoinSub($notasAplicadas, 'nc', function ($join) {
                        $join->on('nc.factura', '=', 'vf.cod_factura');
                    })
                    ->whereDate('vf.fecha_factura', '>=', $fechaInicio)
                    ->whereDate('vf.fecha_factura', '<=', $fechaFin)
                    ->select('vf.*')
                    ->selectRaw('
                        COALESCE(nc.monto_nc, 0) AS monto_nc
                    ');

                /*
                * La consulta exterior calcula aplicacion_nc.
                * 2 = totalmente aplicada
                * 1 = parcialmente aplicada
                * 0 = sin aplicación
                */

                $facturas = DB::query()
                    ->fromSub($facturasBase, 'f')
                    ->select('f.*')
                    ->selectRaw('
                        CASE
                            WHEN ROUND(
                                COALESCE(f.monto_neto_col, 0)::numeric, 2
                            ) = ROUND(
                                COALESCE(f.monto_nc, 0)::numeric, 2
                            )
                            AND COALESCE(f.monto_nc, 0) > 0
                                THEN 2

                            WHEN COALESCE(f.monto_nc, 0) > 0
                            AND ROUND(
                                COALESCE(f.monto_neto_col, 0)::numeric
                                - COALESCE(f.monto_nc, 0)::numeric, 2
                            ) > 0
                                THEN 1

                            ELSE 0
                        END AS aplicacion_nc
                    ');


                /*
                |--------------------------------------------------------------------------
                | 3. Selección de facturas
                |--------------------------------------------------------------------------
                */

                $documentosFacturas = DB::query()
                    ->fromSub($facturas, 'f')
                    ->whereIn('f.aplicacion_nc', [1, 0])
                    ->selectRaw("
                        f.cod_factura::varchar AS cod_factura,
                        f.cod_control_caja,
                        f.fecha_factura,
                        f.cod_cliente,
                        f.consecutivo_hacienda,
                        f.clave_hacienda,
                        f.cod_factura::varchar AS numero_documento,
                        f.monto_neto_col,
                        f.monto_iv_col,
                        f.monto_bruto_col,
                        f.monto_descuento_col,
                        f.monto_exento::numeric AS monto_exento,
                        f.nombre_cliente,
                        f.recurso,
                        f.bodega,
                        f.tipo_pago,
                        f.tipo_cambio,
                        'FA' AS tipo_documento,
                        'Factura' AS tipo_documento_desc,
                        f.aplicacion_nc
                    ");


                /*
                |--------------------------------------------------------------------------
                | 4. Selección de notas de crédito
                |--------------------------------------------------------------------------
                */

                $documentosNotas = NotaCredito::query()
                    ->from('nota_credito as nc')
                    ->leftJoin(
                        'tipo_nota_credito as tn',
                        'tn.tipo_nota_credito',
                        '=',
                        'nc.tipo_nota'
                    )
                    ->where('nc.estado', '1')
                    ->where('tn.devolucion', 'S')
                    ->whereDate('nc.fecha', '>=', $fechaInicio)
                    ->whereDate('nc.fecha', '<=', $fechaFin)
                    ->where('nc.tipo_nota', '!=', 'AN')
                    ->selectRaw("
                        nc.codigo_nota_credito::varchar AS cod_factura,
                        nc.cod_control_caja_aplico_nota AS cod_control_caja,
                        nc.fecha AS fecha_factura,
                        nc.cod_cliente,
                        nc.consecutivo_hacienda,
                        nc.clave_hacienda,
                        nc.numero_nota_cliente::varchar AS numero_documento,
                        nc.total_col::numeric * -1 AS monto_neto_col,
                        nc.total_imp_col::numeric * -1 AS monto_iv_col,
                        (nc.total_col - nc.total_imp_col)::numeric * -1
                            AS monto_bruto_col,
                        nc.total_descuento::numeric * -1 AS monto_descuento_col,
                        nc.total_exento::numeric * -1 AS monto_exento,
                        nc.nombre_cliente,
                        nc.recurso,
                        nc.bodega,
                        nc.tipo_pago_factura AS tipo_pago,
                        nc.tipo_cambio,
                        'NC' AS tipo_documento,
                        'Nota crédito' AS tipo_documento_desc,
                        NULL::integer AS aplicacion_nc
                    ");


                /*
                |--------------------------------------------------------------------------
                | 5. Unión final: facturas + notas de crédito
                |--------------------------------------------------------------------------
                */

                $documentos =  $documentosFacturas
                    ->unionAll($documentosNotas)
                    ->get();
        
                /*
                 * TODOS LOS TOTALES EN UNA SOLA CONSULTA
                 */
                $totales = DB::connection($conexion)->query()
                    ->fromSub($documentos, 'f')
                    ->selectRaw('
        
                        COALESCE(SUM(monto_neto_col), 0)
                            AS facturado,
        
                        COALESCE(SUM(monto_iv_col), 0)
                            AS iva,
        
                        COALESCE(SUM(monto_descuento_col), 0)
                            AS descuento,
        
                        COALESCE(SUM(
                            CASE
                                WHEN tipo_pago = \'C\'
                                THEN monto_neto_col
                                ELSE 0
                            END
                        ), 0) AS colones,
        
                        COALESCE(SUM(
                            CASE
                                WHEN tipo_pago = \'D\'
                                THEN monto_neto_col / NULLIF(tipo_cambio, 0)
                                ELSE 0
                            END
                        ), 0) AS dolares,
        
                        COALESCE(SUM(
                            CASE
                                WHEN tipo_pago = \'CR\'
                                THEN monto_neto_col
                                ELSE 0
                            END
                        ), 0) AS credito,
        
                        COALESCE(SUM(
                            CASE
                                WHEN tipo_pago = \'T\'
                                THEN monto_neto_col
                                ELSE 0
                            END
                        ), 0) AS tarjeta,
        
                        COALESCE(SUM(
                            CASE
                                WHEN tipo_pago = \'TR\'
                                THEN monto_neto_col
                                ELSE 0
                            END
                        ), 0) AS sinpe,
        
                        COALESCE(SUM(
                            CASE
                                WHEN tipo_pago = \'MX\'
                                THEN monto_neto_col
                                ELSE 0
                            END
                        ), 0) AS mixto,

                        COALESCE(SUM(
                            CASE
                                WHEN tipo_documento = \'NC\'
                                THEN monto_neto_col
                                ELSE 0
                            END
                        ), 0) AS monto_notas
        
                    ')
                    ->first();

                    //SUMAR TOTAL NOTA CREDITO
                    $totalNotaCredito = NotaCredito::on($conexion)->whereDate('fecha_aplicado', '>=', $fechaInicio)->sum('total_col');

                    //SUMAR TOTAL ABONOS
                    $totalAbonos = CXC::on($conexion)->whereDate('fecha', '>=', $fechaInicio)->sum('monto_movimiento');
        
                /*
                 * RESULTADO
                 */
                $registros[] = [
        
                    'compania' => $compania,
        
                    'facturado' => (float) $totales->facturado,
        
                    'iva' => (float) $totales->iva,
        
                    'colones' => (float) $totales->colones,
        
                    'dolares' => (float) $totales->dolares,
        
                    'descuento' => (float) $totales->descuento,
        
                    'credito' => (float) $totales->credito,
        
                    'tarjeta' => (float) $totales->tarjeta,
        
                    'sinpe' => (float) $totales->sinpe,
        
                    'mixto' => (float) $totales->mixto,

                    'notas_credito' => (float) $totales->monto_notas,

                    'abonos' => (float) $totalAbonos
                ];
            }
    
            return response()->json([
                'statusCode' => 200,
                'message' => 'informe de ventas',
                'data' => $registros
            ], 200);
    
        } catch (\Throwable $e) {
    
            return response()->json([
                'statusCode' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getRotacionGlobal(Request $request){
        $fechaInicio = now()->startOfWeek()->format('Y-m-d');

        $companias = Constantes::$conexiones;

        $codigos = [
           '074323047165','074323079715','07432354','123490','74000654','74000661','74000685','74000708','74001187','74001200',
           '7401006712349','7441029500042','7441029500059','7441029500110','7441029500141','7441029500202','7441029500240',
           '7441029500318','7441029500325','7441029500356','7441029500431','7441029500530','7441029500547','7441029501537',
           '7441029502107','7441029502251','7441029503548','7441029504613','7441029504842','7441029504859','7441029504866',
           '7441029504996','7441029506181','7441029507041','7441029507249','7441029507348','7441029507355','7441029507751',
           '7441029508116','7441029508147','7441029508307','7441029508437','7441029508444','7441029508918','7441029510294',
           '7441029510539','7441029510546','7441029511017','7441029514179','7441029514902','7441029515268','7441029515374',
           '7441029516104','7441029516579','7441029516586','744102951695','7441029516951','7441029517002','7441029517149',
           '7441029517392','7441029517736','7441029517842','7441029518276','7441029518528','7441029518535','7441029518603',
           '7441029518627','7441029519198','7441029519211','74410295192853','7441029519327','7441029519648','7441029519655',
           '7441029519846','7441029520057','7441029520064','7441029520071','7441029520132','7441029520286','7441029520484',
           '7441029520705','7441029520712','7441029520767','7441029520781','7441029521368','7441029521375','7441029521788',
           '7441029522204','7441029522211','7441029522228','7441029522303','7441029522396','7441029522402','7441029522419',
           '7441029522471','7441029522686','7441029522693','7441029522754','7441029522945','7441029522952','7441029522969',
           '7441029523089','7441029523096','7441029523164','7441029523232','7441029523263','7441029523577','7441029523683',
           '7441029523706','7441029524154','7441029524482','7441029524499','7441029524505','7441029524512','7441029524819',
           '7441029525489','7441029525496','7441029525519','7441029525557','7441029525601','7441029526158','7441029526301',
           '7441029526424','7441029526431','7441029526448','7441029526684','7441029526714','7441029526936','7441029527148',
           '7441029527179','7441029527209','7441029528091','7441029555462','7441029555653','7441029555660','7441029556025',
           '7441029556537','7441029556575','7441029556759','7500810004739','7500810005903','7500810005927','7500810028063',
           '7500810028087','7500810030325','7500810030332','7500810033999','7500810034002','7500810034019','7500810034286',
           '7500810049136','7500810049143','7500810049235','7500810049242','7500810049259','7500810049273','7500810049297',
           '7500810049310','7501000137237','7501000137763','7501000175574','7501000278404','7501000352777','7501030424369',
           '7501030452508','757520040475','757528013455','757528013479','757528013486','757528013509','757528022563',
           '757528022570','757528022587','757528024581','757528024895','757528028541','757528028947','757528029050',
           '757528029074','757528038892','757528038908','757528040406','757528040413','757528040444','757528040451',
           '757528040468','757528040475','757528040482','757528040499','757528040505','757528041298','757528044794',
           '757528045845','757528046491','757528046507','757528046637','757528046644','757528047467','757528047474',
           '757528047481','757528048686','757528049430','757528049836','757528049850','7750727738825','7861009944534',
           '7861009944541','797936200115','889','963883','963935'
        ];
        $productosGlobales = [];

        foreach ($companias as $conexion) {

            $detalle = DetalleFactura::on($conexion)->select(
                'cod_articulo',
                'cantidad'
            )
            ->whereIn('cod_factura', function ($query) use ($fechaInicio) {
                $query->select('cod_factura')
                    ->from('factura')
                    ->whereDate('fecha_creacion', '>=', $fechaInicio);
            })
            ->unionAll(
                
                HistorialDetalleFactura::on($conexion)->select(
                        'cod_articulo',
                        'cantidad'
                    )
                    ->whereIn('cod_factura', function ($query) use ($fechaInicio) {
                        $query->select('cod_factura')
                            ->from('historial_factura')
                            ->whereDate('fecha_creacion', '>=', $fechaInicio);
                    })
            );
    
            $resultado = DB::connection($conexion)->query()
            ->fromSub($detalle, 'd')
            ->join(
                'articulo as a',
                'a.cod_articulo',
                '=',
                'd.cod_articulo'
            )
            ->whereIn('a.cod_articulo', $codigos)
            ->select(
                'd.cod_articulo',
                'a.descripcion',
                DB::connection($conexion)->raw('SUM(d.cantidad) AS cantidad_total')
            )
            ->groupBy(
                'd.cod_articulo',
                'a.descripcion'
            )
            ->orderBy('d.cod_articulo')
            ->get();
    
            foreach ($resultado as $producto) {
                $codigo = $producto->cod_articulo;

                if (isset($productosGlobales[$codigo])) {

                    // Ya existe → sumamos
                    $productosGlobales[$codigo]['cantidad'] += $producto->cantidad_total;

                } else {

                    // No existe → lo agregamos
                    $productosGlobales[$codigo] = [
                        'codigo' => $producto->cod_articulo,
                        'descripcion' => $producto->descripcion,
                        'cantidad' => $producto->cantidad_total
                    ];
                }
            }
        }
        return Response()->json(['statusCode'=>200, 'message'=>'Rotacion de bimbo desde la fehca -> '.$fechaInicio, 'data'=>$productosGlobales],200);
    }   
}
