<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ip_geolocations')) {
            return;
        }

        Schema::create('ip_geolocations', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->unique();
            $table->string('status', 20)->nullable();
            $table->string('continent')->nullable();
            $table->string('continent_code', 8)->nullable();
            $table->string('country')->nullable();
            $table->string('country_code', 8)->nullable();
            $table->string('region')->nullable();
            $table->string('region_name')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('zip', 30)->nullable();
            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lon', 10, 6)->nullable();
            $table->string('timezone')->nullable();
            $table->integer('offset')->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('isp')->nullable();
            $table->string('org')->nullable();
            $table->string('as_number')->nullable();
            $table->string('asname')->nullable();
            $table->boolean('mobile')->default(false);
            $table->boolean('proxy')->default(false);
            $table->boolean('hosting')->default(false);
            $table->json('raw')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_geolocations');
    }
};
