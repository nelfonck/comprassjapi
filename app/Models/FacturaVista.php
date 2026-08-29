<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaVista extends Model
{
    protected $connection = 'qupos';
    protected $table = 'vw_facturas_no_registradas';
    public $timestamps = false;
    use HasFactory;
}
