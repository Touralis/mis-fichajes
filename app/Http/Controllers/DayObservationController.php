<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class DayObservationController extends Controller {
 private function admin(Request $request): void { abort_unless($request->user()->role==='admin',403); }
 public function index(Request $request) {
  $this->admin($request);
  return DB::table('day_observations')->leftJoin('users','users.id','=','day_observations.user_id')->orderByDesc('date')->orderByDesc('day_observations.id')->limit(500)->get(['day_observations.*','users.name as employee_name']);
 }
 public function store(Request $request) {
  $this->admin($request);
  $data=$request->validate(['date'=>'required|date_format:Y-m-d','user_id'=>'nullable|integer|exists:users,id','description'=>'required|string|max:255']);
  DB::table('day_observations')->insert(['date'=>$data['date'],'user_id'=>$data['user_id']??null,'description'=>trim($data['description']),'created_at'=>now(),'updated_at'=>now()]);
  return response()->json(['ok'=>true]);
 }
 public function destroy(Request $request, int $id) {
  $this->admin($request); abort_unless(DB::table('day_observations')->where('id',$id)->delete(),404);
  return response()->json(['ok'=>true]);
 }
}
