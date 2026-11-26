<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;

class UserSignatureController extends Controller {
 public function show(Request $request) {
  $data=$request->user()->signature_data;
  abort_unless($data,404);
  return response(base64_decode($data, true),200,['Content-Type'=>'image/png','Cache-Control'=>'private, no-store']);
 }
 public function save(Request $request) {
  $request->validate(['signature'=>['required',File::image()->types(['png'])->max('2mb')]]);
  $request->user()->forceFill(['signature_data'=>base64_encode(file_get_contents($request->file('signature')->getRealPath()))])->save();
  return response()->json(['ok'=>true]);
 }
 public function destroy(Request $request) {
  $request->user()->forceFill(['signature_data'=>null])->save();
  return response()->json(['ok'=>true]);
 }
}
