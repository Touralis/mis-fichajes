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
    'horas_semanales',
    'numero_afiliacion_ss',
    'user_id'
  ];

  public static function booted()
  {
    static::created(function ($model) {
      $model->password = bcrypt(static::generatePassword());
      $model->save();
      $newUser = User::create([
        'name' => $model->nombre,
        'email' => $model->mail . 'prueba ' . rand(0, 1000),
        'password' => bcrypt($model->password),
        'role' => 'empleado',
      ]);
      $newUser->save();
      $model->user_id = $newUser->id;
      $model->save();
    });

    static::updated(function ($model) {
      if ($model->user_id) {
        $user = User::find($model->user_id);
        if ($user) {
          $user->name = $model->nombre;
          $user->save();
        }
      }
    });

    static::deleted(function ($model) {
      if ($model->user_id) {
        $user = User::find($model->user_id);
        if ($user) {
          $user->delete();
        }
      }
    });
  }

  public function user()
  {
    return $this->hasOne(User::class);
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
