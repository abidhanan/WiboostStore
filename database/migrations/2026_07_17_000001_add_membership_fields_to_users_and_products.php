<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'membership_started_at')) {
                $table->timestamp('membership_started_at')->nullable()->after('reseller_joined_at');
            }

            if (! Schema::hasColumn('users', 'membership_expires_at')) {
                $table->timestamp('membership_expires_at')->nullable()->after('membership_started_at')->index();
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'membership_price')) {
                $table->decimal('membership_price', 15, 2)->nullable()->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'membership_price')) {
                $table->dropColumn('membership_price');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'membership_expires_at')) {
                $table->dropColumn('membership_expires_at');
            }

            if (Schema::hasColumn('users', 'membership_started_at')) {
                $table->dropColumn('membership_started_at');
            }
        });
    }
};
