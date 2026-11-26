<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('day_observations', function(Blueprint $table) { $table->id(); $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete(); $table->date('date'); $table->string('description',255); $table->timestamps(); $table->index(['date','user_id']); }); }
 public function down(): void { Schema::dropIfExists('day_observations'); }
};
