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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('logo', 500)->nullable()->after('singleton');
            $table->string('favicon', 500)->nullable()->after('logo');
            $table->string('placeholder', 500)->nullable()->after('favicon');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['logo', 'favicon', 'placeholder']);
        });
    }
};
