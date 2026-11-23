<?php
namespace App\Http\Controllers;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
class AttendanceController extends Controller {
 public function login(Request $request) {
  $data = $request->validate(['email'=>'required|email','password'=>'required|string']);
  if (!Auth::attempt($data + ['active'=>true], $request->boolean('remember'))) return response()->json(['message'=>'El correo o la contraseña no son correctos.'], 422);
  $request->session()->regenerate(); return response()->json(['ok'=>true]);
 }
 public function logout(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return response()->json(['ok'=>true]); }
 public function state(Request $request) {
  $user=$request->user(); $admin=$user->role === 'admin';
  $records=AttendanceRecord::with('user:id,name,position')->when(!$admin, fn($q)=>$q->where('user_id',$user->id))->latest('started_at')->limit(100)->get();
  return response()->json(['user'=>$user->only('id','name','email','role','position','daily_hours'), 'records'=>$records, 'employees'=>$admin ? User::orderBy('name')->get(['id','name','email','role','position','daily_hours','active','identity_number','social_security_number']) : [], 'activeRecord'=>AttendanceRecord::where('user_id',$user->id)->whereNull('ended_at')->first(), 'todayRecords'=>AttendanceRecord::where('user_id',$user->id)->where('started_at','<',now()->startOfDay()->addDay())->where(fn($q)=>$q->whereNull('ended_at')->orWhere('ended_at','>',now()->startOfDay()))->get(['id','started_at','ended_at'])]);
 }
 public function clock(Request $request) {
  DB::transaction(function () use ($request) { User::whereKey($request->user()->id)->lockForUpdate()->first(); $record=AttendanceRecord::where('user_id',$request->user()->id)->whereNull('ended_at')->first(); if ($record) $record->update(['ended_at'=>now()]); else AttendanceRecord::create(['user_id'=>$request->user()->id,'started_at'=>now()]); });
  return response()->json(['ok'=>true]);
 }
 private function admin(Request $request): void { abort_unless($request->user()->role === 'admin',403); }
 public function saveEmployee(Request $request, ?User $employee = null) {
  $this->admin($request); $data=$request->validate(['name'=>'required|string|max:255','email'=>['required','email','max:255',Rule::unique('users')->ignore($employee?->id)],'password'=>[$employee ? 'nullable':'required','string','min:8'],'position'=>'nullable|string|max:255','identity_number'=>'nullable|string|max:50','social_security_number'=>'nullable|string|max:50','daily_hours'=>'required|numeric|min:0|max:24','role'=>['required',Rule::in(['admin','employee'])], 'active'=>'required|boolean']);
  if ($employee && $employee->id === $request->user()->id && (!$data['active'] || $data['role'] !== 'admin')) return response()->json(['message'=>'No puedes desactivar tu cuenta ni quitarte el acceso de administrador.'],422);
  if (empty($data['password'])) unset($data['password']); else $data['password']=Hash::make($data['password']);
  if ($employee) $employee->forceFill($data)->save(); else { $employee=new User; $employee->forceFill($data)->save(); }
  return response()->json(['ok'=>true]);
 }
 public function export(Request $request) {
  $this->admin($request);
  $filters=$request->validate(['from'=>'nullable|date_format:Y-m-d','to'=>'nullable|date_format:Y-m-d|after_or_equal:from','employee_id'=>'nullable|integer|exists:users,id']);
  $query=AttendanceRecord::with('user')->when($filters['from'] ?? null, fn($q,$date)=>$q->where('started_at','>=',\Carbon\Carbon::parse($date)->startOfDay()))->when($filters['to'] ?? null, fn($q,$date)=>$q->where('started_at','<',\Carbon\Carbon::parse($date)->addDay()->startOfDay()))->when($filters['employee_id'] ?? null, fn($q,$id)=>$q->where('user_id',$id))->orderBy('started_at')->orderBy('id');
  return response()->streamDownload(function () use ($query) {
   $out=fopen('php://output','w'); fwrite($out, "\xEF\xBB\xBF"); fputcsv($out,['Empleado','Correo','Entrada','Salida','Horas'], ';');
   foreach($query->lazy(500) as $r) { $safe=fn($v)=>preg_match('/^[=+\-@\t\r]/',(string)$v) ? "'".$v : $v; fputcsv($out,[$safe($r->user->name),$safe($r->user->email),$r->started_at->format('Y-m-d H:i:s'),$r->ended_at?->format('Y-m-d H:i:s'),$r->ended_at ? round($r->started_at->diffInSeconds($r->ended_at)/3600,2) : ''], ';'); } fclose($out);
  }, 'fichajes.csv', ['Content-Type'=>'text/csv; charset=UTF-8']);
 }
}
