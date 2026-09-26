<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_credentials', 'requires_logout_proof')) {
            Schema::table('product_credentials', function (Blueprint $table) {
                $table->boolean('requires_logout_proof')
                    ->default(false)
                    ->after('requires_login_proof');
            });
        }

        if (! Schema::hasColumn('product_credentials', 'duration_value')) {
            Schema::table('product_credentials', function (Blueprint $table) {
                $table->unsignedInteger('duration_value')
                    ->nullable()
                    ->after('max_usage');
            });
        }

        if (! Schema::hasColumn('product_credentials', 'duration_unit')) {
            Schema::table('product_credentials', function (Blueprint $table) {
                $table->string('duration_unit', 20)
                    ->nullable()
                    ->after('duration_value');
            });
        }

        if (! Schema::hasColumn('transactions', 'credential_started_at')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->timestamp('credential_started_at')
                    ->nullable()
                    ->after('login_proof_uploaded_at');
            });
        }

        if (! Schema::hasColumn('transactions', 'credential_expires_at')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->timestamp('credential_expires_at')
                    ->nullable()
                    ->after('credential_started_at');
            });
        }

        if (! Schema::hasColumn('transactions', 'logout_proof_path')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('logout_proof_path')
                    ->nullable()
                    ->after('credential_expires_at');
            });
        }

        if (! Schema::hasColumn('transactions', 'logout_proof_uploaded_at')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->timestamp('logout_proof_uploaded_at')
                    ->nullable()
                    ->after('logout_proof_path');
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'logout_proof_uploaded_at',
            'logout_proof_path',
            'credential_expires_at',
            'credential_started_at',
        ] as $column) {
            if (Schema::hasColumn('transactions', $column)) {
                Schema::table('transactions', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }

        foreach ([
            'duration_unit',
            'duration_value',
            'requires_logout_proof',
        ] as $column) {
            if (Schema::hasColumn('product_credentials', $column)) {
                Schema::table('product_credentials', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
