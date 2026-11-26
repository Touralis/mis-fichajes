<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class UserSignatureController extends Controller {
 public function show(Request $request) {
  $path=$request->user()->signature_path;
  abort_unless($path && Storage::disk('local')->exists($path),404);
  return Storage::disk('local')->response($path,null,['Cache-Control'=>'private, no-store']);
 }
 public function save(Request $request) {
  $request->validate(['signature'=>['required',File::image()->types(['png'])->max('2mb')]]);
  $disk=Storage::disk('local'); $old=$request->user()->signature_path;
  $new=$request->file('signature')->store('user-signatures','local');
  try { $request->user()->forceFill(['signature_path'=>$new])->save(); }
  catch (\Throwable $e) { $disk->delete($new); throw $e; }
  if ($old && $old!==$new) $disk->delete($old);
  return response()->json(['ok'=>true]);
 }
 public function destroy(Request $request) {
  $old=$request->user()->signature_path;
  $request->user()->forceFill(['signature_path'=>null])->save();
  if ($old) Storage::disk('local')->delete($old);
  return response()->json(['ok'=>true]);
 }
}
