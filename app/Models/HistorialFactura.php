<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialFactura extends Model
{
    protected $connection = 'qupos';
    protected $table = 'historial_factura';
    public $timestamps = false;
    use HasFactory;
}
