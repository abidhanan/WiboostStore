<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            if (! Schema::hasColumn('deposits', 'payment_url')) {
                $table->text('payment_url')->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('deposits', 'tripay_reference')) {
                $table->string('tripay_reference')->nullable()->after('payment_url');
            }

            if (! Schema::hasColumn('deposits', 'payment_response')) {
                $table->json('payment_response')->nullable()->after('tripay_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            foreach (['payment_response', 'tripay_reference', 'payment_url'] as $column) {
                if (Schema::hasColumn('deposits', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
