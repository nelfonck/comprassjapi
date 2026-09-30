<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CXC extends Model
{
    protected $connection = 'qupos';
    protected $table = 'movimiento_cxc';
    public $timestamps = false;
    use HasFactory;
}
