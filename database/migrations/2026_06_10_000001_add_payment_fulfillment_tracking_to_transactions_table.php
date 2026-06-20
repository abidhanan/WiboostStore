<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->nullable()->after('amount');
            }

            if (! Schema::hasColumn('transactions', 'points_awarded_at')) {
                $table->timestamp('points_awarded_at')->nullable()->after('credential_data');
            }

            if (! Schema::hasColumn('transactions', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('points_awarded_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            foreach (['refunded_at', 'points_awarded_at', 'paid_amount'] as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
