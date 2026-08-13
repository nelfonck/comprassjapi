<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'facturas';
    public $timestamps = false;
    protected $primaryKey = 'id';
    use HasFactory;
}
