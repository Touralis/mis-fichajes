<?php
namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class AttendanceGenerationController extends Controller {
 private function enabled(Request $request): void {
  abort_unless($request->user()->role === 'employee' && DB::table('company_settings')->where('id',1)->value('allow_attendance_generation'), 403, 'La generación de fichajes no está habilitada.');
 }
 public function preview(Request $request) {
  $this->enabled($request);
  $data=$request->validate(['from'=>'required|date_format:Y-m-d','to'=>'required|date_format:Y-m-d|after_or_equal:from|before_or_equal:today','start'=>'required|date_format:H:i','end'=>'required|date_format:H:i|after:start','weekends'=>'required|boolean','variation'=>'required|boolean']);
  $from=Carbon::parse($data['from'])->startOfDay(); $to=Carbon::parse($data['to'])->startOfDay();
  if ($from->diffInDays($to)>365) throw ValidationException::withMessages(['to'=>'El período no puede superar 366 días.']);
  $items=[];
  for ($day=$from->copy(); $day->lte($to); $day->addDay()) {
   if (!$data['weekends'] && $day->isWeekend()) continue;
   $start=Carbon::parse($day->toDateString().' '.$data['start'])->addMinutes($data['variation'] ? random_int(-5,5) : 0);
   $end=Carbon::parse($day->toDateString().' '.$data['end'])->addMinutes($data['variation'] ? random_int(-5,5) : 0);
   if (!$start->isSameDay($day) || !$end->isSameDay($day) || $end->lte($start) || $end->gt(now())) throw ValidationException::withMessages(['start'=>'Comprueba el horario: las jornadas deben terminar el mismo día, después de la entrada y sin fechas futuras.']);
   $items[]=['started_at'=>$start->format('Y-m-d H:i:s'),'ended_at'=>$end->format('Y-m-d H:i:s')];
  }
  if (!$items) throw ValidationException::withMessages(['from'=>'No hay días que generar en el período seleccionado.']);
  $this->checkOverlaps($request->user()->id,$items);
  return response()->json(['items'=>$items,'token'=>Crypt::encryptString(json_encode(['user_id'=>$request->user()->id,'items'=>$items,'expires'=>now()->addMinutes(30)->timestamp]))]);
 }
 private function checkOverlaps(int $userId, array $items): void {
  foreach ($items as $item) if (AttendanceRecord::where('user_id',$userId)->where('started_at','<',$item['ended_at'])->where(fn($q)=>$q->whereNull('ended_at')->orWhere('ended_at','>',$item['started_at']))->exists()) throw ValidationException::withMessages(['from'=>'Ya existe un fichaje que se solapa con la jornada del '.Carbon::parse($item['started_at'])->format('d/m/Y').'.']);
 }
 public function store(Request $request) {
  $this->enabled($request); $data=$request->validate(['token'=>'required|string']);
  try { $payload=json_decode(Crypt::decryptString($data['token']),true,512,JSON_THROW_ON_ERROR); } catch (\Throwable $e) { throw ValidationException::withMessages(['token'=>'La previsualización no es válida. Vuelve a generarla.']); }
  if (($payload['user_id'] ?? null)!==$request->user()->id || ($payload['expires'] ?? 0)<now()->timestamp) throw ValidationException::withMessages(['token'=>'La previsualización ha caducado. Vuelve a generarla.']);
  DB::transaction(function () use ($request,$payload) {
   User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
   $this->enabled($request); $this->checkOverlaps($request->user()->id,$payload['items']);
   foreach ($payload['items'] as $item) AttendanceRecord::create($item+['user_id'=>$request->user()->id]);
  });
  return response()->json(['ok'=>true,'count'=>count($payload['items'])]);
 }
}
