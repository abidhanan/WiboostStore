<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('displays', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text', 50)->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('emoji', 20)->nullable();
            $table->string('theme')->default('blue');
            $table->string('image')->nullable();
            $table->string('link', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('displays');
    }
};
