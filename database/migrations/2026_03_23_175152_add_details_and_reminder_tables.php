<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Tambah kolom profil, pin, link di tabel kredensial
        Schema::table('product_credentials', function (Blueprint $table) {
            $table->string('data_3')->nullable()->after('data_2'); // Profil
            $table->string('data_4')->nullable()->after('data_3'); // PIN
            $table->string('data_5')->nullable()->after('data_4'); // Link
        });
        // Tambah kolom pengingat stok di produk
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock_reminder')->default(0)->after('process_type');
        });
    }

    public function down(): void {
        Schema::table('product_credentials', function (Blueprint $table) {
            $table->dropColumn(['data_3', 'data_4', 'data_5']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock_reminder');
        });
    }
};