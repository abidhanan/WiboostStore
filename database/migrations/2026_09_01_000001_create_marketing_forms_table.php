<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('marketing_forms')) {
            Schema::create('marketing_forms', function (Blueprint $table) {
                $table->id();
                $table->string('title', 180);
                $table->string('slug', 220)->unique();
                $table->text('description')->nullable();
                $table->json('fields')->nullable();
                $table->boolean('benefit_enabled')->default(false);
                $table->string('benefit_title', 180)->nullable();
                $table->text('benefit_description')->nullable();
                $table->json('benefit_credentials')->nullable();
                $table->string('benefit_url', 500)->nullable();
                $table->string('benefit_button_label', 80)->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('marketing_form_submissions')) {
            Schema::create('marketing_form_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('marketing_form_id')->constrained('marketing_forms')->cascadeOnDelete();
                $table->string('public_token', 64)->unique();
                $table->json('answers')->nullable();
                $table->string('ip_address', 64)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamps();

                $table->index(['marketing_form_id', 'created_at'], 'marketing_form_submissions_form_created_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_form_submissions');
        Schema::dropIfExists('marketing_forms');
    }
};
