<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compania extends Model
{
    protected $connection = 'qupos';
    protected $table = 'compania';
    public $timestamps = false;
    use HasFactory;
}
