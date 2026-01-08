<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichajesConfiguracion extends Model
{
  use HasFactory;

  protected $table = 'fichajes_configuracion';

  protected $fillable = [
    'geolocalizacion',
    'firma_empresa',
  ];

  protected $casts = [
    'geolocalizacion' => 'boolean',
  ];
}
