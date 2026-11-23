<?php
namespace Tests\Feature;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class AttendanceTest extends TestCase {
 use RefreshDatabase;
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
  $this->actingAs($admin)->postJson('/api/employees',['name'=>'Ana','email'=>'ana@example.com','password'=>'password123','position'=>'Recepción','daily_hours'=>8,'role'=>'employee','active'=>true])->assertOk();
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
 public function test_admin_correction_is_audited_and_overlap_is_rejected(): void {
  $admin=User::factory()->create(); $admin->forceFill(['role'=>'admin'])->save(); $employee=User::factory()->create();
  $record=AttendanceRecord::create(['user_id'=>$employee->id,'started_at'=>'2026-10-05 09:00:00','ended_at'=>'2026-10-05 12:00:00']);
  AttendanceRecord::create(['user_id'=>$employee->id,'started_at'=>'2026-10-05 14:00:00','ended_at'=>'2026-10-05 17:00:00']);
  $payload=['started_at'=>'2026-10-05T09:30','ended_at'=>'2026-10-05T12:00','reason'=>'Ajuste solicitado'];
  $this->actingAs($employee)->putJson('/api/records/'.$record->id,$payload)->assertForbidden();
  $this->actingAs($admin)->putJson('/api/records/'.$record->id,$payload)->assertOk();
  $this->assertDatabaseHas('attendance_corrections',['attendance_record_id'=>$record->id,'actor_id'=>$admin->id,'reason'=>'Ajuste solicitado']);
  $this->getJson('/api/records/'.$record->id.'/history')->assertOk()->assertJsonCount(1);
  $payload['ended_at']='2026-10-05T15:00';
  $this->putJson('/api/records/'.$record->id,$payload)->assertUnprocessable();
  $this->assertDatabaseCount('attendance_corrections',1);
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
