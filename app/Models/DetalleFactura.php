<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleFactura extends Model
{
    protected $connection = 'qupos';
    protected $table = 'detalle_factura';
    public $timestamps = false;
    use HasFactory;
}
