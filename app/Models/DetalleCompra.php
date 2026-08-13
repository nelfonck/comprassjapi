<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    protected $table = 'factura_detalle';
    public $timestamps = false;
    protected $primaryKey = 'id';
    use HasFactory;
}
