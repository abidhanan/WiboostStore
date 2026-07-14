<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'reseller_payout_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('reseller_payout_name')->nullable()->after('reseller_payout_account');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'reseller_payout_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('reseller_payout_name');
            });
        }
    }
};
