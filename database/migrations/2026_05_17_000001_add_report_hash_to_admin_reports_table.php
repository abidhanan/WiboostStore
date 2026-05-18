<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('admin_reports', 'report_hash')) {
                $table->string('report_hash', 64)->nullable()->after('user_id')->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('admin_reports', function (Blueprint $table) {
            if (Schema::hasColumn('admin_reports', 'report_hash')) {
                $table->dropUnique(['report_hash']);
                $table->dropColumn('report_hash');
            }
        });
    }
};
