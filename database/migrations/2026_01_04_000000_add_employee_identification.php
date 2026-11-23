<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('users',function(Blueprint $table){$table->string('identity_number',50)->nullable();$table->string('social_security_number',50)->nullable();}); }
 public function down(): void { Schema::table('users',fn(Blueprint $table)=>$table->dropColumn(['identity_number','social_security_number'])); }
};
