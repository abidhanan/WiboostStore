<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_credentials', 'created_by_user_id')) {
            Schema::table('product_credentials', function (Blueprint $table) {
                $table->foreignId('created_by_user_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('product_credentials', 'requires_login_proof')) {
            Schema::table('product_credentials', function (Blueprint $table) {
                $table->boolean('requires_login_proof')
                    ->default(false)
                    ->after('needs_otp');
            });
        }

        if (! Schema::hasColumn('transactions', 'login_proof_path')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('login_proof_path')->nullable()->after('credential_data');
            });
        }

        if (! Schema::hasColumn('transactions', 'login_proof_uploaded_at')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->timestamp('login_proof_uploaded_at')->nullable()->after('login_proof_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'login_proof_uploaded_at')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('login_proof_uploaded_at');
            });
        }

        if (Schema::hasColumn('transactions', 'login_proof_path')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('login_proof_path');
            });
        }

        if (Schema::hasColumn('product_credentials', 'requires_login_proof')) {
            Schema::table('product_credentials', function (Blueprint $table) {
                $table->dropColumn('requires_login_proof');
            });
        }

        if (Schema::hasColumn('product_credentials', 'created_by_user_id')) {
            Schema::table('product_credentials', function (Blueprint $table) {
                $table->dropConstrainedForeignId('created_by_user_id');
            });
        }
    }
};
