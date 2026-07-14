<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->updateOrInsert(
            ['id' => 3],
            [
                'name' => 'Reseller',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        if (! Schema::hasColumn('users', 'reseller_payout_method')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('reseller_payout_method')->nullable()->after('profile_photo_path');
            });
        }

        if (! Schema::hasColumn('users', 'reseller_payout_account')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('reseller_payout_account')->nullable()->after('reseller_payout_method');
            });
        }

        if (! Schema::hasColumn('users', 'reseller_joined_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('reseller_joined_at')->nullable()->after('reseller_payout_account');
            });
        }

        if (! Schema::hasTable('reseller_commissions')) {
            Schema::create('reseller_commissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type', 20);
                $table->string('category_slug')->nullable();
                $table->string('category_name')->nullable();
                $table->string('product_name')->nullable();
                $table->decimal('rate', 8, 4)->default(0);
                $table->decimal('base_amount', 14, 2)->default(0);
                $table->decimal('amount', 14, 2)->default(0);
                $table->string('status', 20)->default('success');
                $table->string('payout_method')->nullable();
                $table->string('payout_account')->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->unique(['transaction_id', 'type']);
                $table->index(['user_id', 'type', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_commissions');

        Schema::table('users', function (Blueprint $table) {
            foreach (['reseller_joined_at', 'reseller_payout_account', 'reseller_payout_method'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
