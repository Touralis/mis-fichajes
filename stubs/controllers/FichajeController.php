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

    $empleado = FichajeEmployer::where('user_id', $user->id)->first();
    if (!$empleado) {
      return redirect()->route('fichajes.dashboard.admin');
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

    // Variables para acumular SEGUNDOS
    $horasHoy = 0;
    $horasSemana = 0;
    $horasMes = 0;

    foreach ($fichajes as $fichaje) {
      // Solo contar fichajes que tengan entrada Y salida
      if (!$fichaje->dia_salida) continue;

      $entrada = Carbon::parse($fichaje->dia_entrada, $timezone);
      $salida = Carbon::parse($fichaje->dia_salida, $timezone);
      $segundos = abs($salida->diffInSeconds($entrada));

      // HOY: Solo fichajes que entraron hoy
      if ($entrada->isSameDay($hoy)) {
        $horasHoy += $segundos;
      }

      // SEMANA: Solo fichajes que entraron esta semana
      if ($entrada->greaterThanOrEqualTo($inicioSemana) && $entrada->lessThan($inicioSemana->copy()->addWeek())) {
        $horasSemana += $segundos;
      }

      // MES: Solo fichajes que entraron este mes
      if ($entrada->greaterThanOrEqualTo($inicioMes) && $entrada->lessThanOrEqualTo($finMes)) {
        $horasMes += $segundos;
      }
    }

    // Para hoy: x horas diarias
    $horasHoyMax = $empleado->horas_diarias * 3600;

    // Para la semana: 5 días laborables * x horas diarias
    $diasLaborablesEstaSemana = $inicioSemana->copy()->addDays(6)->diffInDaysFiltered(
      fn($date) => !$date->isWeekend(),
      $inicioSemana,
      true
    ) + 1;
    $horasSemanaMax = $diasLaborablesEstaSemana * $empleado->horas_diarias * 3600;

    // Para el mes: días laborables del mes * x horas diarias
    $diasLaborablesMes = $inicioMes->diffInDaysFiltered(
      fn($date) => !$date->isWeekend(),
      $finMes
    );
    $horasMesMax = $diasLaborablesMes * $empleado->horas_diarias * 3600;

    return [
      [
        'label' => 'Hoy',
        'current' => $horasHoy,
        'max' => $horasHoyMax,
        'total' => intval($horasHoyMax / 3600) . 'H',
        'percentage' => $horasHoyMax > 0 ? ($horasHoy / $horasHoyMax) * 100 : 0
      ],
      [
        'label' => 'Semana',
        'current' => $horasSemana,
        'max' => $horasSemanaMax,
        'total' => intval($horasSemanaMax / 3600) . 'H',
        'percentage' => $horasSemanaMax > 0 ? ($horasSemana / $horasSemanaMax) * 100 : 0
      ],
      [
        'label' => 'Mes',
        'current' => $horasMes,
        'max' => $horasMesMax,
        'total' => intval($horasMesMax / 3600) . 'H',
        'percentage' => $horasMesMax > 0 ? ($horasMes / $horasMesMax) * 100 : 0
      ],
    ];
  }

  /**--- Admin ---**/
  public function indexAdmin()
  {
    $fichajes = Fichaje::paginate(15);
    $empleados = FichajeEmployer::paginate(10);

    return view('fichajes.dashboard_admin', [
      'fichajes' => $fichajes,
      'empleados' => $empleados,
    ]);
  }

  public function storeEmpleado(Request $request)
  {
    $validated = $request->validate([
      'nombre' => 'required|string',
      'apellidos' => 'required|string',
      'email' => 'required|email|unique:fichaje_employers,mail',
      'telefono' => 'required|string',
      'dni' => 'required|string|unique:fichaje_employers,dni',
      'puesto_trabajo' => 'required|string',
      'horas_diarias' => 'required|numeric',
      'numero_afiliacion_ss' => 'required|string',
    ]);

    $password = FichajeEmployer::generatePassword();

    FichajeEmployer::create([
      'nombre' => $validated['nombre'],
      'apellidos' => $validated['apellidos'],
      'mail' => $validated['email'],
      'telefono' => $validated['telefono'],
      'dni' => $validated['dni'],
      'puesto_trabajo' => $validated['puesto_trabajo'],
      'horas_diarias' => $validated['horas_diarias'],
      'numero_afiliacion_ss' => $validated['numero_afiliacion_ss'],
      'password' => bcrypt($password),
    ]);

    return response()->json(['success' => true]);
  }

  public function updateEmpleado(Request $request, $id)
  {
    $empleado = FichajeEmployer::findOrFail($id);

    $validated = $request->validate([
      'nombre' => 'required|string',
      'apellidos' => 'required|string',
      'email' => 'required|email|unique:fichaje_employers,mail,' . $id,
      'telefono' => 'required|string',
      'dni' => 'required|string|unique:fichaje_employers,dni,' . $id,
      'puesto_trabajo' => 'required|string',
      'horas_diarias' => 'required|numeric',
      'numero_afiliacion_ss' => 'required|string',
    ]);

    $empleado->update([
      'nombre' => $validated['nombre'],
      'apellidos' => $validated['apellidos'],
      'mail' => $validated['email'],
      'telefono' => $validated['telefono'],
      'dni' => $validated['dni'],
      'puesto_trabajo' => $validated['puesto_trabajo'],
      'horas_diarias' => $validated['horas_diarias'],
      'numero_afiliacion_ss' => $validated['numero_afiliacion_ss'],
    ]);

    return response()->json(['success' => true]);
  }

  public function destroyEmpleado($id)
  {
    FichajeEmployer::findOrFail($id)->delete();
    return redirect()->back()->with('success', 'Empleado eliminado correctamente');
  }
}
