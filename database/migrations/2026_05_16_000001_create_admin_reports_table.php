<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category_key', 50);
            $table->string('category_label', 100);
            $table->text('issue');
            $table->string('record_id')->nullable();
            $table->string('related_invoice')->nullable();
            $table->string('related_title')->nullable();
            $table->json('related_snapshot')->nullable();
            $table->string('proof_path')->nullable();
            $table->string('proof_original_name')->nullable();
            $table->longText('whatsapp_message')->nullable();
            $table->string('status', 30)->default('new');
            $table->timestamps();

            $table->index(['category_key', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_reports');
    }
};
