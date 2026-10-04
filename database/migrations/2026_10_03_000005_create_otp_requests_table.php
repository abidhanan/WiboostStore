<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('otp_requests')) {
            return;
        }

        Schema::create('otp_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transaction_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('invoice_number', 100)->index();
            $table->string('product_name')->nullable();
            $table->string('otp_admin_name', 80)->nullable();
            $table->string('otp_admin_whatsapp', 40)->nullable();
            $table->enum('status', ['pending', 'handled'])->default('pending')->index();
            $table->timestamp('requested_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_requests');
    }
};
