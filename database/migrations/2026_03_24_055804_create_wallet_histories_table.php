<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['topup', 'purchase', 'refund']); // Tipe transaksi
            $table->decimal('amount', 15, 2); // Jumlah uang
            $table->string('description'); // Keterangan (ex: "Beli Netflix", "Refund API Gagal")
            $table->string('invoice_number')->nullable(); // Referensi ke invoice
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_histories');
    }
};