<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\File;
class CompanySettingsController extends Controller {
 private function admin(Request $request): void { abort_unless($request->user()->role === 'admin',403); }
 public function show(Request $request) { $this->admin($request); $settings=DB::table('company_settings')->where('id',1)->first(); return response()->json(['name'=>$settings->name ?? '', 'tax_id'=>$settings->tax_id ?? '', 'address'=>$settings->address ?? '', 'email'=>$settings->email ?? '', 'phone'=>$settings->phone ?? '', 'allow_attendance_generation'=>(bool)($settings->allow_attendance_generation ?? false), 'allow_attendance_correction'=>(bool)($settings->allow_attendance_correction ?? false), 'require_geolocation'=>(bool)($settings->require_geolocation ?? false), 'has_signature'=>!empty($settings->signature_data)]); }
 public function save(Request $request) {
  $this->admin($request);
  $data=$request->validate(['name'=>'nullable|string|max:255','tax_id'=>'nullable|string|max:50','address'=>'nullable|string|max:255','email'=>'nullable|email|max:255','phone'=>'nullable|string|max:50','signature'=>['nullable',File::image()->types(['png','jpg','jpeg'])->max('2mb')], 'remove_signature'=>'nullable|boolean','allow_attendance_generation'=>'nullable|boolean','require_geolocation'=>'nullable|boolean','allow_attendance_correction'=>'nullable|boolean']);
  $values=['name'=>$data['name'] ?? null,'tax_id'=>$data['tax_id'] ?? null,'address'=>$data['address'] ?? null,'email'=>$data['email'] ?? null,'phone'=>$data['phone'] ?? null,'allow_attendance_generation'=>$request->boolean('allow_attendance_generation'),'require_geolocation'=>$request->boolean('require_geolocation'),'allow_attendance_correction'=>$request->boolean('allow_attendance_correction'),'updated_at'=>now()];
  if ($request->hasFile('signature')) {
   $file=$request->file('signature');
   $values['signature_data']=base64_encode(file_get_contents($file->getRealPath()));
   $values['signature_mime']=$file->getMimeType();
  } elseif ($request->boolean('remove_signature')) {
   $values['signature_data']=null; $values['signature_mime']=null;
  }
  DB::table('company_settings')->updateOrInsert(['id'=>1],$values);
  return $this->show($request);
 }
 public function signature(Request $request) { $this->admin($request); $settings=DB::table('company_settings')->where('id',1)->first(); abort_unless($settings?->signature_data,404); return response(base64_decode($settings->signature_data,true),200,['Content-Type'=>$settings->signature_mime,'Cache-Control'=>'private, no-store']); }
}
