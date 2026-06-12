<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RazonSocial extends Model
{
    protected $table = 'razones_sociales';
    public $timestamps = false;
    protected $primaryKey = 'id';

    use HasFactory;
}
