<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_credentials') && ! Schema::hasColumn('product_credentials', 'otp_admin_whatsapp')) {
            Schema::table('product_credentials', function (Blueprint $table) {
                $table->string('otp_admin_whatsapp', 40)->nullable()->after('needs_otp');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_credentials') && Schema::hasColumn('product_credentials', 'otp_admin_whatsapp')) {
            Schema::table('product_credentials', function (Blueprint $table) {
                $table->dropColumn('otp_admin_whatsapp');
            });
        }
    }
};
