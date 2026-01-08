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
    Schema::create('fichajes_configuracion', function (Blueprint $table) {
      $table->id();
      $table->boolean('geolocalizacion')->default(false);
      $table->string('firma_empresa')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('fichajes_configuracion');
  }
};
