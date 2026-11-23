<?php
namespace Tests\Feature;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class AttendanceGenerationTest extends TestCase {
 use RefreshDatabase;
 public function test_generation_is_admin_controlled_and_preview_is_confirmed_once(): void {
  $this->travelTo(now()->setDate(2026,11,23)->setTime(20,0));
  $employee=User::factory()->create()->fresh(); $other=User::factory()->create()->fresh(); $admin=User::factory()->create(); $admin->forceFill(['role'=>'admin'])->save();
  $input=['from'=>'2026-11-20','to'=>'2026-11-22','start'=>'09:00','end'=>'17:00','weekends'=>false,'variation'=>true];
  $this->actingAs($employee)->postJson('/api/records/generate/preview',$input)->assertForbidden();
  $this->postJson('/api/company-settings',['allow_attendance_generation'=>true])->assertForbidden();
  $this->actingAs($admin)->postJson('/api/company-settings',['allow_attendance_generation'=>true])->assertOk();
  $response=$this->actingAs($employee)->postJson('/api/records/generate/preview',$input)->assertOk()->assertJsonCount(1,'items');
  $item=$response->json('items.0'); $token=$response->json('token');
  $this->assertLessThanOrEqual(5,abs(\Carbon\Carbon::parse($item['started_at'])->diffInMinutes(\Carbon\Carbon::parse('2026-11-20 09:00'),false)));
  $this->actingAs($other)->postJson('/api/records/generate',['token'=>$token])->assertUnprocessable();
  $this->actingAs($employee)->postJson('/api/records/generate',['token'=>$token])->assertOk();
  $this->assertDatabaseHas('attendance_records',['user_id'=>$employee->id,'started_at'=>$item['started_at'],'ended_at'=>$item['ended_at']]);
  $this->postJson('/api/records/generate',['token'=>$token])->assertUnprocessable();
  $this->assertDatabaseCount('attendance_records',1);
 }
 public function test_conflicts_and_disabled_setting_are_rechecked_on_confirmation(): void {
  $this->travelTo(now()->setDate(2026,11,23)->setTime(20,0));
  $employee=User::factory()->create()->fresh(); DB::table('company_settings')->insert(['id'=>1,'allow_attendance_generation'=>true]);
  $input=['from'=>'2026-11-20','to'=>'2026-11-20','start'=>'09:00','end'=>'17:00','weekends'=>true,'variation'=>false];
  $token=$this->actingAs($employee)->postJson('/api/records/generate/preview',$input)->assertOk()->json('token');
  AttendanceRecord::create(['user_id'=>$employee->id,'started_at'=>'2026-11-20 10:00','ended_at'=>'2026-11-20 11:00']);
  $this->postJson('/api/records/generate',['token'=>$token])->assertUnprocessable();
  DB::table('company_settings')->where('id',1)->update(['allow_attendance_generation'=>false]);
  $this->postJson('/api/records/generate',['token'=>$token])->assertForbidden();
  $this->assertDatabaseCount('attendance_records',1);
 }
}
