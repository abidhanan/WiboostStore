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
        Schema::table('products', function (Blueprint $table) {
            // Menambahkan kolom provider_product_id setelah kolom price
            $table->string('provider_product_id')->nullable()->after('price')
                  ->comment('ID Layanan dari provider asli (misal: 150 untuk OrderSosmed)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('provider_product_id');
        });
    }
};