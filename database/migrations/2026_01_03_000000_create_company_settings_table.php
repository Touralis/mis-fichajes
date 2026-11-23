<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('company_settings',function(Blueprint $table){$table->id();$table->string('name')->nullable();$table->string('tax_id',50)->nullable();$table->string('address')->nullable();$table->string('email')->nullable();$table->string('phone',50)->nullable();$table->string('signature_path')->nullable();$table->timestamps();}); }
 public function down(): void {Schema::dropIfExists('company_settings');}
};
