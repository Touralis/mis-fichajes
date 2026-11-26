<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public $withinTransaction = false;

    public function up(): void
    {
        // Keep the old key until every correction has been mapped to its new key.
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique();
        });
        DB::table('attendance_records')->orderBy('id')->chunkById(500, function ($records) {
            foreach ($records as $record) {
                DB::table('attendance_records')->where('id', $record->id)->update(['uuid' => (string) Str::uuid()]);
            }
        });
        Schema::table('attendance_corrections', function (Blueprint $table) {
            $table->uuid('record_uuid')->nullable();
        });
        DB::table('attendance_corrections')->orderBy('id')->chunkById(500, function ($corrections) {
            foreach ($corrections as $correction) {
                $uuid = DB::table('attendance_records')->where('id', $correction->attendance_record_id)->value('uuid');
                if (!$uuid) throw new RuntimeException('No se encuentra el fichaje de la corrección '.$correction->id);
                DB::table('attendance_corrections')->where('id', $correction->id)->update(['record_uuid' => $uuid]);
            }
        });

        Schema::create('attendance_records_uuid_new', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamp('started_at'); $table->timestamp('ended_at')->nullable();
            foreach (['start_latitude', 'start_longitude', 'end_latitude', 'end_longitude'] as $column) $table->decimal($column, 10, 7)->nullable();
            foreach (['start_accuracy', 'end_accuracy'] as $column) $table->decimal($column, 10, 2)->nullable();
            $table->string('origin', 20)->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps(); $table->index(['user_id', 'started_at']);
        });
        Schema::create('attendance_corrections_uuid_new', function (Blueprint $table) {
            $table->id(); $table->uuid('attendance_record_id');
            $table->foreign('attendance_record_id')->references('id')->on('attendance_records_uuid_new')->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('previous_started_at'); $table->timestamp('previous_ended_at')->nullable();
            $table->timestamp('new_started_at'); $table->timestamp('new_ended_at')->nullable();
            $table->text('reason'); $table->timestamps();
        });
        $recordColumns = ['user_id','started_at','ended_at','start_latitude','start_longitude','start_accuracy','end_latitude','end_longitude','end_accuracy','origin','generated_by','created_at','updated_at'];
        DB::table('attendance_records')->orderBy('id')->chunkById(500, function ($records) use ($recordColumns) {
            foreach ($records as $record) {
                $row = (array) $record; $new = array_intersect_key($row, array_flip($recordColumns)); $new['id'] = $row['uuid'];
                DB::table('attendance_records_uuid_new')->insert($new);
            }
        });
        DB::table('attendance_corrections')->orderBy('id')->chunkById(500, function ($corrections) {
            foreach ($corrections as $correction) {
                $row = (array) $correction; $row['attendance_record_id'] = $row['record_uuid']; unset($row['record_uuid']);
                DB::table('attendance_corrections_uuid_new')->insert($row);
            }
        });
        if (DB::table('attendance_records')->count() !== DB::table('attendance_records_uuid_new')->count() || DB::table('attendance_corrections')->count() !== DB::table('attendance_corrections_uuid_new')->count()) throw new RuntimeException('No coinciden los datos migrados.');
        Schema::rename('attendance_corrections', 'attendance_corrections_uuid_old');
        Schema::rename('attendance_records', 'attendance_records_uuid_old');
        Schema::rename('attendance_records_uuid_new', 'attendance_records');
        Schema::rename('attendance_corrections_uuid_new', 'attendance_corrections');
        Schema::drop('attendance_corrections_uuid_old');
        Schema::drop('attendance_records_uuid_old');
    }

    public function down(): void
    {
        throw new RuntimeException('La conversión a UUID no se puede revertir automáticamente sin perder identificadores.');
    }
};
