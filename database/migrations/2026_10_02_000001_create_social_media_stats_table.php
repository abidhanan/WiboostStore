<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('social_media_stats')) {
            return;
        }

        Schema::create('social_media_stats', function (Blueprint $table): void {
            $table->id();
            $table->string('platform', 20)->unique();
            $table->unsignedBigInteger('total_posts')->default(0);
            $table->unsignedBigInteger('total_followers')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('profile_visits')->default(0);
            $table->unsignedBigInteger('bio_link_clicks')->default(0);
            $table->string('top_post_url', 500)->nullable();
            $table->string('low_post_url', 500)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['platform', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_media_stats');
    }
};
