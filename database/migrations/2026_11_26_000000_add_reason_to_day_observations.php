<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::table('day_observations', function (Blueprint $table) { $table->string('reason')->nullable(); }); }
    public function down(): void { Schema::table('day_observations', function (Blueprint $table) { $table->dropColumn('reason'); }); }
};
