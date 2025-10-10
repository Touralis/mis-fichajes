<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FichajeEmployer extends Model
{
  protected $table = 'fichaje_employers';

  protected $fillable = [
    'nombre',
    'apellidos',
    'dni',
    'mail',
    'telefono',
    'password',
    'puesto_trabajo',
    'horas_diarias',
    'numero_afiliacion_ss',
    'user_id'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public static function generatePassword()
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 12; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }
}
