<?php
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
Artisan::command('misfichajes:admin', function () {
 $name=$this->ask('Nombre del administrador'); $email=$this->ask('Correo electrónico'); $password=$this->secret('Contraseña (mínimo 8 caracteres)');
 if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen((string)$password)<8 || User::where('email',$email)->exists()) { $this->error('Datos inválidos o correo ya registrado.'); return 1; }
 $user=new User; $user->forceFill(['name'=>$name,'email'=>$email,'password'=>Hash::make($password),'role'=>'admin','active'=>true])->save(); $this->info('Administrador creado.');
})->purpose('Crear un administrador de Mis Fichajes');
