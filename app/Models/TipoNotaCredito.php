<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoNotaCredito extends Model
{
    protected $connection = 'qupos';
    protected $table = 'tipo_nota_credito';
    public $timestamps = false;
    use HasFactory;
}
