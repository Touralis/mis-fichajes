<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceUuidMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_records_and_corrections_keep_their_relationship(): void
    {
        $this->checkMigration(false);
    }

    public function test_phpmyadmin_sql_preserves_existing_records_on_mysql(): void
    {
        if (DB::getDriverName() !== 'mysql') $this->markTestSkipped('El script SQL está diseñado para MySQL/MariaDB.');
        $this->checkMigration(true);
    }

    private function checkMigration(bool $sql): void
    {
        Schema::drop('attendance_corrections');
        Schema::drop('attendance_records');
        Schema::create('attendance_records', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained(); $t->timestamp('started_at'); $t->timestamp('ended_at')->nullable();
            foreach (['start_latitude','start_longitude','end_latitude','end_longitude'] as $column) $t->decimal($column, 10, 7)->nullable();
            foreach (['start_accuracy','end_accuracy'] as $column) $t->decimal($column, 10, 2)->nullable();
            $t->string('origin', 20)->nullable(); $t->foreignId('generated_by')->nullable()->constrained('users'); $t->timestamps();
        });
        Schema::create('attendance_corrections', function (Blueprint $t) {
            $t->id(); $t->foreignId('attendance_record_id')->constrained('attendance_records'); $t->foreignId('actor_id')->constrained('users');
            $t->timestamp('previous_started_at'); $t->timestamp('previous_ended_at')->nullable(); $t->timestamp('new_started_at'); $t->timestamp('new_ended_at')->nullable(); $t->text('reason'); $t->timestamps();
        });
        $user = \App\Models\User::factory()->create();
        $oldId = DB::table('attendance_records')->insertGetId(['user_id'=>$user->id,'started_at'=>'2026-11-01 09:00:00','origin'=>'clock']);
        DB::table('attendance_corrections')->insert(['attendance_record_id'=>$oldId,'actor_id'=>$user->id,'previous_started_at'=>'2026-11-01 08:00:00','new_started_at'=>'2026-11-01 09:00:00','reason'=>'Corrección']);
        if ($sql) DB::unprepared(file_get_contents(base_path('migracion-fichajes-uuid.sql')));
        else (require database_path('migrations/2026_11_26_000001_convert_attendance_ids_to_uuid.php'))->up();
        $record = DB::table('attendance_records')->first();
        $this->assertTrue(Str::isUuid($record->id));
        $this->assertSame('clock', $record->origin);
        $this->assertDatabaseHas('attendance_corrections', ['attendance_record_id'=>$record->id, 'reason'=>'Corrección']);
    }
}
