<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('company_settings', fn(Blueprint $table) => $table->boolean('require_geolocation')->default(false));
  Schema::table('attendance_records', function(Blueprint $table) {
   foreach (['start_latitude','start_longitude','end_latitude','end_longitude'] as $column) $table->decimal($column, 10, 7)->nullable();
   foreach (['start_accuracy','end_accuracy'] as $column) $table->decimal($column, 10, 2)->nullable();
  });
 }
 public function down(): void {
  Schema::table('company_settings', fn(Blueprint $table) => $table->dropColumn('require_geolocation'));
  Schema::table('attendance_records', fn(Blueprint $table) => $table->dropColumn(['start_latitude','start_longitude','start_accuracy','end_latitude','end_longitude','end_accuracy']));
 }
};
