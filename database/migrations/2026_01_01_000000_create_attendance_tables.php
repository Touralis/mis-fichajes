<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('users', function (Blueprint $table) { $table->string('role')->default('employee'); $table->string('position')->nullable(); $table->decimal('daily_hours', 4, 2)->default(8); $table->boolean('active')->default(true); });
  Schema::create('attendance_records', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->restrictOnDelete(); $table->timestamp('started_at'); $table->timestamp('ended_at')->nullable(); $table->timestamps(); $table->index(['user_id', 'started_at']); });
 }
 public function down(): void { Schema::dropIfExists('attendance_records'); Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role','position','daily_hours','active'])); }
};
