<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RazonSocialController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\ProveedorController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('apikey')->group(function () {


    Route::get(
        '/existe-razon-social',
        [RazonSocialController::class, 'existeRazonSocial']
    );
    Route::get(
        '/razones-sociales',
        [RazonSocialController::class, 'getRazonSocialList']
    );
    Route::post(
        '/guardar-razon-social',
        [RazonSocialController::class, 'guardarRazonSocial']
    );
    Route::post(
        '/guardar-tienda',
        [TiendaController::class, 'guardarTienda']
    );
    Route::get(
        '/tiendas',
        [TiendaController::class, 'getTiendas']
    );
    Route::get(
        '/proveedores',
        [ProveedorController::class, 'getProveedores']
    );
    Route::post(
        '/guardar-proveedor',
        [ProveedorController::class, 'guardarProveedor']
    );


});