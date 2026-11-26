<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', fn (Blueprint $table) => $table->longText('signature_data')->nullable());
        Schema::table('company_settings', function (Blueprint $table) {
            $table->longText('signature_data')->nullable();
            $table->string('signature_mime', 32)->nullable();
        });
    }
    public function down(): void {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('signature_data'));
        Schema::table('company_settings', fn (Blueprint $table) => $table->dropColumn(['signature_data', 'signature_mime']));
    }
};
