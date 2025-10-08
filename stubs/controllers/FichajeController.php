<?php

namespace App\Http\Controllers;

use App\Models\FichajeEmployer;
use App\Models\Fichaje;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FichajeController extends Controller
{

  public function index()
  {
    $user = Auth::user();

    // Si no está autenticado o no tiene empleado asignado
    $empleado = FichajeEmployer::where('user_id', $user->id)->first();
    if (!$empleado) {
      FichajeEmployer::create([
        'user_id' => $user->id,
        'nombre' => $user->name,
        'apellidos' => $user->name,
        'dni' => $user->dni,
        'mail' => $user->email,
        'telefono' => $user->phone,
        'password' => 'hola',
        'puesto_trabajo' => 'Trabajo',
        'horas_semanales' => 40,
        'numero_afiliacion_ss' => 12345678,
      ]);
      return redirect()->route('fichajes.dashboard');
    }

    $fichajes = Fichaje::where('user_id', $user->id)->get();
    $ultimaSalida = $fichajes
      ->where('tipo', 'Trabajo')
      ->sortByDesc('dia_salida')
      ->first();

    // Determinar estado actual del timer
    $timezone = 'Europe/Madrid';
    $fichajeActivo = Fichaje::where('user_id', $user->id)->whereNull('dia_salida')->first();

    $buttonText = $fichajeActivo ? 'Salida' : 'Entrada';
    $startTime = $fichajeActivo
      ? Carbon::parse($fichajeActivo->dia_entrada, $timezone)->timestamp
      : null;

    return view('fichajes.dashboard', [
      'user' => $user,
      'empleado' => $empleado,
      'fichajes' => $fichajes,
      'ultimaSalida' => $ultimaSalida,
      'buttonText' => $buttonText,
      'startTime' => $startTime,
      'estadisticas' => $this->estadisticas($empleado, $fichajes),
      'fichajesHoy' => $this->fichajesHoy($fichajes),
    ]);
  }

  public function clickButton(Request $request)
  {
    $timezone = 'Europe/Madrid';
    $user = Auth::user();

    $ultimo = Fichaje::orderBy('id', 'desc')->where('user_id', $user->id)->first();

    if (!$ultimo || $ultimo->dia_salida !== null) {
      Fichaje::create([
        'user_id' => $user->id,
        'dia_entrada' => Carbon::now($timezone)->format('Y-m-d H:i:s'),
      ]);
    } else {
      $ultimo->update([
        'dia_salida' => Carbon::now($timezone)->format('Y-m-d H:i:s'),
      ]);
    }

    return redirect()->route('fichajes.dashboard');
  }

  private function fichajesHoy(Collection $fichajes)
  {
    $timezone = 'Europe/Madrid';

    return $fichajes
      ->filter(
        fn($fichaje) =>
        Carbon::parse($fichaje->dia_entrada, $timezone)->isToday() ||
          ($fichaje->dia_salida && Carbon::parse($fichaje->dia_salida, $timezone)->isToday())
      )
      ->sortBy(fn($fichaje) => Carbon::parse($fichaje->dia_entrada, $timezone));
  }

  private function estadisticas(FichajeEmployer $empleado, Collection $fichajes)
  {
    $timezone = 'Europe/Madrid';
    $hoy = Carbon::today($timezone);
    $inicioSemana = Carbon::now($timezone)->startOfWeek();
    $inicioMes = Carbon::now($timezone)->startOfMonth();
    $finMes = Carbon::now($timezone)->endOfMonth();

    $horasHoy = $horasSemana = $horasMes = 0;

    foreach ($fichajes as $fichaje) {
      if (!$fichaje->dia_salida) continue;

      $entrada = Carbon::parse($fichaje->dia_entrada, $timezone);
      $salida = Carbon::parse($fichaje->dia_salida, $timezone);
      $segundos = $salida->diffInSeconds($entrada);

      if ($entrada->isSameDay($hoy)) $horasHoy += $segundos;
      if ($entrada->greaterThanOrEqualTo($inicioSemana)) $horasSemana += $segundos;
      if ($entrada->greaterThanOrEqualTo($inicioMes)) $horasMes += $segundos;
    }

    $horasHoyMax = $empleado->horas_semanales * 3600;
    $horasSemanaMax = $empleado->horas_semanales * 5 * 3600;

    $diasLaborables = $inicioMes->diffInDaysFiltered(
      fn($date) => !$date->isWeekend(),
      $finMes
    );

    $horasMesMax = $diasLaborables * $empleado->horas_semanales * 3600;

    return [
      ['label' => 'Hoy', 'current' => $horasHoy, 'max' => $horasHoyMax, 'total' => intval($horasHoyMax / 3600) . 'H'],
      ['label' => 'Semana', 'current' => $horasSemana, 'max' => $horasSemanaMax, 'total' => intval($horasSemanaMax / 3600) . 'H'],
      ['label' => 'Mes', 'current' => $horasMes, 'max' => $horasMesMax, 'total' => intval($horasMesMax / 3600) . 'H'],
    ];
  }
}
