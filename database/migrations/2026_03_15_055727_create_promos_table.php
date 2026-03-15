<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text'); // Contoh: INFO PROMO, PENGUMUMAN
            $table->string('title'); // Contoh: Diskon 50% Top Up MLBB!
            $table->text('description'); // Penjelasan promo
            $table->string('emoji')->default('✨'); // Emoji besar di kanan
            $table->string('theme')->default('blue'); // Tema warna (blue, teal, orange, rose)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('promos');
    }
};