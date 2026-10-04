<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('social_media_stats') || Schema::hasColumn('social_media_stats', 'month')) {
            return;
        }

        Schema::table('social_media_stats', function (Blueprint $table): void {
            $table->unsignedTinyInteger('month')->default(now()->month)->after('platform');
            $table->unsignedSmallInteger('year')->default(now()->year)->after('month');
        });

        Schema::table('social_media_stats', function (Blueprint $table): void {
            $table->dropUnique('social_media_stats_platform_unique');
            $table->unique(['platform', 'month', 'year'], 'social_media_stats_platform_period_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('social_media_stats') || ! Schema::hasColumn('social_media_stats', 'month')) {
            return;
        }

        Schema::table('social_media_stats', function (Blueprint $table): void {
            $table->dropUnique('social_media_stats_platform_period_unique');
            $table->unique('platform', 'social_media_stats_platform_unique');
            $table->dropColumn(['month', 'year']);
        });
    }
};
