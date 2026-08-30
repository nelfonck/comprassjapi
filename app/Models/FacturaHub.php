<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaHub extends Model
{
    protected $connection = 'factura_hub';
    protected $table = 'facturas';
    public $timestamps = false;
    use HasFactory;
}
