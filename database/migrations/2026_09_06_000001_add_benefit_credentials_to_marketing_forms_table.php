<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marketing_forms') && ! Schema::hasColumn('marketing_forms', 'benefit_credentials')) {
            Schema::table('marketing_forms', function (Blueprint $table) {
                $table->json('benefit_credentials')->nullable()->after('benefit_description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('marketing_forms') && Schema::hasColumn('marketing_forms', 'benefit_credentials')) {
            Schema::table('marketing_forms', function (Blueprint $table) {
                $table->dropColumn('benefit_credentials');
            });
        }
    }
};
