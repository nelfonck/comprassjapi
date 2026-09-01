<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoRecepcion extends Model
{
    protected $table = 'documento_recepcion';
    public $timestamps = false;
    use HasFactory;
}
