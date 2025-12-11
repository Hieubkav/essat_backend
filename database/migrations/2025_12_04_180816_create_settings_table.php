<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 255)->nullable();
            $table->string('primary_color', 32)->nullable();
            $table->string('secondary_color', 32)->nullable();
            $table->string('seo_title', 255)->nullable();
            $table->text('seo_description')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('singleton', 32)->default('default')->unique();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'site_name' => 'My Website',
            'primary_color' => '#000000',
            'secondary_color' => '#FFFFFF',
            'seo_title' => 'My Website',
            'seo_description' => null,
            'phone' => null,
            'address' => null,
            'email' => null,
            'singleton' => 'default',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
