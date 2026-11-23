<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('attendance_corrections', function (Blueprint $table) { $table->id(); $table->foreignId('attendance_record_id')->constrained()->restrictOnDelete(); $table->foreignId('actor_id')->constrained('users')->restrictOnDelete(); $table->timestamp('previous_started_at'); $table->timestamp('previous_ended_at')->nullable(); $table->timestamp('new_started_at'); $table->timestamp('new_ended_at')->nullable(); $table->text('reason'); $table->timestamps(); }); }
 public function down(): void { Schema::dropIfExists('attendance_corrections'); }
};
