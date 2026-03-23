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
        Schema::create('product_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            // data_1: Untuk menyimpan Email (Aplikasi) ATAU Nomor Luar
            $table->string('data_1'); 
            
            // data_2: Untuk menyimpan Password (bisa kosong jika tipe produknya Nomor Luar)
            $table->string('data_2')->nullable(); 
            
            // max_usage: 1 Akun bisa untuk berapa pembeli? (Sistem Shared Account)
            $table->integer('max_usage')->default(1); 
            
            // current_usage: Akun ini sudah diberikan ke berapa pembeli?
            $table->integer('current_usage')->default(0); 
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_credentials');
    }
};