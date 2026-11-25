<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('company_settings', fn(Blueprint $table) => $table->boolean('allow_attendance_correction')->default(false)); }
 public function down(): void { Schema::table('company_settings', fn(Blueprint $table) => $table->dropColumn('allow_attendance_correction')); }
};
