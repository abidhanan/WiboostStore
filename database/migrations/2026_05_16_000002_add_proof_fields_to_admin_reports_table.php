<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('admin_reports', 'proof_path')) {
                $table->string('proof_path')->nullable()->after('related_snapshot');
            }

            if (! Schema::hasColumn('admin_reports', 'proof_original_name')) {
                $table->string('proof_original_name')->nullable()->after('proof_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admin_reports', function (Blueprint $table) {
            if (Schema::hasColumn('admin_reports', 'proof_original_name')) {
                $table->dropColumn('proof_original_name');
            }

            if (Schema::hasColumn('admin_reports', 'proof_path')) {
                $table->dropColumn('proof_path');
            }
        });
    }
};
