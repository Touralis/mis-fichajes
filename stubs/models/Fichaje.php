<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fichaje extends Model
{
  use HasFactory;

  protected $table = 'fichajes';

  protected $fillable = [
    'user_id',
    'tipo',
    'dia_entrada',
    'dia_salida',
    'geolocalizacion',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
