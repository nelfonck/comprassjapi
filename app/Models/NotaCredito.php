<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaCredito extends Model
{
    protected $connection = 'qupos';
    protected $table = 'nota_credito';
    public $timestamps = false;
    use HasFactory;
}
