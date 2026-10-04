<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_credentials') && ! Schema::hasColumn('product_credentials', 'otp_admin_name')) {
            Schema::table('product_credentials', function (Blueprint $table) {
                $table->string('otp_admin_name', 80)->nullable()->after('otp_admin_whatsapp');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_credentials') && Schema::hasColumn('product_credentials', 'otp_admin_name')) {
            Schema::table('product_credentials', function (Blueprint $table) {
                $table->dropColumn('otp_admin_name');
            });
        }
    }
};
