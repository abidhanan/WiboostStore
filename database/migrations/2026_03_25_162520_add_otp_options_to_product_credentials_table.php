<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_credentials', function (Blueprint $table) {
            $table->string('tutorial_link')->nullable()->after('data_5');
            $table->boolean('needs_otp')->default(false)->after('tutorial_link');
        });
    }

    public function down(): void
    {
        Schema::table('product_credentials', function (Blueprint $table) {
            $table->dropColumn(['tutorial_link', 'needs_otp']);
        });
    }
};