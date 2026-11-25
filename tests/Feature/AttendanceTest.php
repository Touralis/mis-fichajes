<?php
namespace Tests\Feature;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class AttendanceTest extends TestCase {
 use RefreshDatabase;
 public function test_login_accepts_email_phone_and_identity_with_the_same_password(): void {
  $user=User::factory()->create(['email'=>'ana@example.com','password'=>bcrypt('password123')]);
  $user->forceFill(['phone'=>'612345678','identity_number'=>'12345678A','active'=>false])->save();
  foreach (['ana@example.com','612345678','12345678A'] as $identifier) {
   $this->postJson('/login',['email'=>$identifier,'password'=>'password123'])->assertOk();
   $this->assertAuthenticatedAs($user);
   $this->postJson('/logout')->assertOk();
  }
  $this->postJson('/login',['email'=>'612345678','password'=>'incorrecta'])->assertStatus(422);
  $this->assertGuest();
 }
 public function test_geolocation_is_required_and_saved_for_each_clock_action_when_enabled(): void {
  $user=User::factory()->create();
  \Illuminate\Support\Facades\DB::table('company_settings')->updateOrInsert(['id'=>1],['require_geolocation'=>true]);
  $this->actingAs($user)->postJson('/api/clock')->assertUnprocessable();
  $this->assertDatabaseCount('attendance_records',0);
  $this->postJson('/api/clock',['latitude'=>91,'longitude'=>-3,'accuracy'=>10])->assertUnprocessable();
  $this->postJson('/api/clock',['latitude'=>40.4168,'longitude'=>-3.7038,'accuracy'=>10])->assertOk();
  $this->postJson('/api/clock')->assertUnprocessable();
  $this->postJson('/api/clock',['latitude'=>40.42,'longitude'=>-3.7,'accuracy'=>20])->assertOk();
  $record=AttendanceRecord::firstOrFail();
  $this->assertEquals(40.4168,$record->start_latitude);
  $this->assertEquals(-3.7,$record->end_longitude);
  $this->assertNotNull($record->ended_at);
 }
 public function test_employee_can_start_and_finish_a_shift(): void {
  $user=User::factory()->create();
  $this->actingAs($user)->postJson('/api/clock')->assertOk();
  $this->assertDatabaseHas('attendance_records',['user_id'=>$user->id,'ended_at'=>null]);
  $this->postJson('/api/clock')->assertOk();
  $this->assertNotNull(AttendanceRecord::first()->ended_at);
 }
 public function test_employee_cannot_manage_or_export_other_employees(): void {
  $user=User::factory()->create();
  $this->actingAs($user)->postJson('/api/employees',[])->assertForbidden();
  $this->get('/api/export')->assertForbidden();
 }
 public function test_admin_can_create_employee_with_hashed_password(): void {
  $admin=User::factory()->create(); $admin->forceFill(['role'=>'admin'])->save();
  $this->actingAs($admin)->postJson('/api/employees',['name'=>'Ana','email'=>'ana@example.com','password'=>'password123','position'=>'Recepción','daily_hours'=>8,'role'=>'employee'])->assertOk();
  $employee=User::where('email','ana@example.com')->firstOrFail();
  $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password123',$employee->password));
 }
 public function test_admin_exports_filtered_records_and_employees_cannot_export(): void {
  $admin=User::factory()->create(); $admin->forceFill(['role'=>'admin'])->save();
  $ana=User::factory()->create(['name'=>'Ana']); $other=User::factory()->create(['name'=>'Luis']);
  AttendanceRecord::create(['user_id'=>$ana->id,'started_at'=>'2026-11-20 09:00:00','ended_at'=>'2026-11-20 17:00:00']);
  AttendanceRecord::create(['user_id'=>$other->id,'started_at'=>'2026-11-20 09:00:00','ended_at'=>'2026-11-20 17:00:00']);
  $response=$this->actingAs($admin)->get('/api/export?from=2026-11-20&to=2026-11-20&employee_id='.$ana->id);
  $response->assertOk(); $csv=$response->streamedContent(); $this->assertStringContainsString('Ana',$csv); $this->assertStringNotContainsString('Luis',$csv);
  $this->get('/api/export?to=2026-11-20')->assertOk();
 }
 public function test_daily_records_are_not_limited_by_team_history(): void {
  $admin=User::factory()->create(); $admin->forceFill(['role'=>'admin'])->save();
  AttendanceRecord::create(['user_id'=>$admin->id,'started_at'=>now()->subHour()]);
  $this->actingAs($admin)->getJson('/api/state')->assertOk()->assertJsonCount(1,'todayRecords');
 }
 public function test_filters_and_pdf_are_scoped_to_employee(): void {
  $ana=User::factory()->create(['name'=>'Ana']); $luis=User::factory()->create(['name'=>'Luis']);
  AttendanceRecord::create(['user_id'=>$ana->id,'started_at'=>'2026-10-05 09:00:00','ended_at'=>'2026-10-05 17:00:00']);
  AttendanceRecord::create(['user_id'=>$luis->id,'started_at'=>'2026-10-05 09:00:00','ended_at'=>'2026-10-05 17:00:00']);
  $this->actingAs($ana)->getJson('/api/records?from=2026-10-01&to=2026-10-31&employee_id='.$luis->id)->assertOk()->assertJsonCount(1,'data')->assertJsonPath('data.0.user.name','Ana');
  $this->get('/api/records/pdf?from=2026-10-01&to=2026-10-31&employee_id='.$luis->id)->assertOk()->assertHeader('content-type','application/pdf')->assertSee('%PDF',false);
 }
 public function test_corrections_require_admin_and_enabled_setting_and_are_audited(): void {
  $admin=User::factory()->create(); $admin->forceFill(['role'=>'admin'])->save(); $employee=User::factory()->create();
  $record=AttendanceRecord::create(['user_id'=>$employee->id,'started_at'=>'2026-10-05 09:00:00','ended_at'=>'2026-10-05 12:00:00']);
  $payload=['started_at'=>'2026-10-05T09:30','ended_at'=>'2026-10-05T12:00','reason'=>'Ajuste solicitado'];
  $this->actingAs($admin)->putJson('/api/records/'.$record->id,$payload)->assertForbidden();
  $this->getJson('/api/records/'.$record->id.'/history')->assertForbidden();
  \Illuminate\Support\Facades\DB::table('company_settings')->updateOrInsert(['id'=>1],['allow_attendance_correction'=>true]);
  $this->actingAs($employee)->putJson('/api/records/'.$record->id,$payload)->assertForbidden();
  $this->actingAs($admin)->putJson('/api/records/'.$record->id,$payload)->assertOk();
  $this->assertEquals('09:30',$record->fresh()->started_at->format('H:i'));
  $this->assertDatabaseHas('attendance_corrections',['attendance_record_id'=>$record->id,'actor_id'=>$admin->id,'reason'=>'Ajuste solicitado']);
  $this->getJson('/api/records/'.$record->id.'/history')->assertOk()->assertJsonCount(1);
  \Illuminate\Support\Facades\DB::table('company_settings')->where('id',1)->update(['allow_attendance_correction'=>false]);
  $this->putJson('/api/records/'.$record->id,$payload)->assertForbidden();
 }
 public function test_company_settings_signature_and_pdf_are_protected_and_persisted(): void {
  \Illuminate\Support\Facades\Storage::fake('local');
  $admin=User::factory()->create(); $admin->forceFill(['role'=>'admin'])->save(); $employee=User::factory()->create();
  $this->actingAs($employee)->getJson('/api/company-settings')->assertForbidden();
  $this->postJson('/api/company-settings',['name'=>'Empresa'])->assertForbidden();
  $file=\Illuminate\Http\UploadedFile::fake()->image('firma.png',100,50);
  $this->actingAs($admin)->post('/api/company-settings',['name'=>'Empresa Ejemplo','tax_id'=>'B12345678','address'=>'Calle Mayor','email'=>'info@example.com','phone'=>'123456789','signature'=>$file])->assertOk()->assertJsonPath('has_signature',true);
  $this->assertDatabaseHas('company_settings',['id'=>1,'name'=>'Empresa Ejemplo','tax_id'=>'B12345678']);
  $this->get('/api/company-settings/signature')->assertOk();
  $this->get('/api/records/pdf?from=2026-10-01&to=2026-10-31&employee_id='.$employee->id)->assertOk()->assertHeader('content-type','application/pdf');
  $this->postJson('/api/company-settings',['name'=>'Empresa Ejemplo','remove_signature'=>true])->assertOk()->assertJsonPath('has_signature',false);
  $this->get('/api/company-settings/signature')->assertNotFound();
 }
}
