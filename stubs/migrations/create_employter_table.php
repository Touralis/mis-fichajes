<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('fichaje_employers', function (Blueprint $table) {
      $table->id();
      $table->string('nombre');
      $table->string('apellidos');
      $table->string('dni')->nullable();
      $table->string('mail')->nullable();
      $table->string('telefono')->nullable();
      $table->string('password')->nullable();
      $table->string('puesto_trabajo');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('fichaje_employers');
  }
};
