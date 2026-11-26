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
  abort_unless($request->user()->role === 'admin' || DB::table('company_settings')->where('id',1)->value('allow_attendance_generation'), 403, 'La generación de fichajes no está habilitada.');
 }
 public function preview(Request $request) {
  $this->enabled($request);
  $data=$request->validate(['from'=>'required|date_format:Y-m-d','to'=>'required|date_format:Y-m-d|after_or_equal:from|before_or_equal:today','start'=>'required|date_format:H:i','end'=>'required|date_format:H:i|after:start','weekends'=>'required|boolean','variation'=>'required|boolean','employee_id'=>[$request->user()->role === 'admin' ? 'required' : 'prohibited','integer','exists:users,id']]);
  $targetId=$request->user()->role === 'admin' ? (int)$data['employee_id'] : $request->user()->id;
  $from=Carbon::parse($data['from'])->startOfDay(); $to=Carbon::parse($data['to'])->startOfDay();
  if ($from->diffInDays($to)>365) throw ValidationException::withMessages(['to'=>'El período no puede superar 366 días.']);
  $items=[];
  for ($day=$from->copy(); $day->lte($to); $day->addDay()) {
   if (!$data['weekends'] && $day->isWeekend()) continue;
   $start=Carbon::parse($day->toDateString().' '.$data['start'])->addMinutes($data['variation'] ? random_int(-5,5) : 0);
   $end=Carbon::parse($day->toDateString().' '.$data['end'])->addMinutes($data['variation'] ? random_int(-5,5) : 0);
   if (!$start->isSameDay($day) || !$end->isSameDay($day) || $end->lte($start)) throw ValidationException::withMessages(['start'=>'La salida debe ser posterior a la entrada y ambas deben estar en el mismo día.']);
   if ($end->gt(now())) throw ValidationException::withMessages(['end'=>'La salida todavía no ha ocurrido. Selecciona una hora pasada y comprueba la variación de minutos.']);
   $items[]=['started_at'=>$start->format('Y-m-d H:i:s'),'ended_at'=>$end->format('Y-m-d H:i:s')];
  }
  if (!$items) throw ValidationException::withMessages(['from'=>'No hay días que generar en el período seleccionado.']);
  $this->checkOverlaps($targetId,$items);
  return response()->json(['items'=>$items,'token'=>Crypt::encryptString(json_encode(['actor_id'=>$request->user()->id,'user_id'=>$targetId,'items'=>$items,'expires'=>now()->addMinutes(30)->timestamp]))]);
 }
 private function checkOverlaps(int $userId, array $items): void {
  foreach ($items as $item) if (AttendanceRecord::where('user_id',$userId)->where('started_at','<',$item['ended_at'])->where(fn($q)=>$q->whereNull('ended_at')->orWhere('ended_at','>',$item['started_at']))->exists()) throw ValidationException::withMessages(['from'=>'Ya existe un fichaje que se solapa con la jornada del '.Carbon::parse($item['started_at'])->format('d/m/Y').'.']);
 }
 public function store(Request $request) {
  $this->enabled($request); $data=$request->validate(['token'=>'required|string']);
  try { $payload=json_decode(Crypt::decryptString($data['token']),true,512,JSON_THROW_ON_ERROR); } catch (\Throwable $e) { throw ValidationException::withMessages(['token'=>'La previsualización no es válida. Vuelve a generarla.']); }
  if (($payload['actor_id'] ?? null)!==$request->user()->id || ($request->user()->role !== 'admin' && ($payload['user_id'] ?? null)!==$request->user()->id) || ($payload['expires'] ?? 0)<now()->timestamp) throw ValidationException::withMessages(['token'=>'La previsualización ha caducado. Vuelve a generarla.']);
  DB::transaction(function () use ($request,$payload) {
   User::whereKey($payload['user_id'])->lockForUpdate()->firstOrFail();
   $this->enabled($request); $this->checkOverlaps($payload['user_id'],$payload['items']);
   foreach ($payload['items'] as $item) AttendanceRecord::create($item+['user_id'=>$payload['user_id'],'origin'=>'generated','generated_by'=>$request->user()->id]);
  });
  return response()->json(['ok'=>true,'count'=>count($payload['items'])]);
 }
}
