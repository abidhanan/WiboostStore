<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Menambahkan kolom payment_method dan snap_token
            $table->string('payment_method')->nullable()->after('order_status');
            $table->text('snap_token')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Menghapus kembali kolom jika migration di-rollback
            $table->dropColumn(['payment_method', 'snap_token']);
        });
    }
};