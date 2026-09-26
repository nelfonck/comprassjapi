<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlCaja extends Model
{
    protected $connection = 'qupos';
    protected $table = 'control_caja';
    public $timestamps = false;
    use HasFactory;
}
