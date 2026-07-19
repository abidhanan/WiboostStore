<?php

use App\Models\Transaction;
use App\Services\ResellerCommissionService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reseller_commissions') || ! Schema::hasTable('transactions')) {
            return;
        }

        $commissionService = app(ResellerCommissionService::class);

        Transaction::query()
            ->with(['user', 'product.category.parent.parent.parent'])
            ->where('created_at', '>=', $commissionService->commissionStartDate())
            ->where('payment_status', 'paid')
            ->where('order_status', 'success')
            ->whereHas('user', fn ($query) => $query->withResellerCommissionAccess())
            ->orderBy('id')
            ->chunkById(300, function ($transactions) use ($commissionService) {
                foreach ($transactions as $transaction) {
                    $commissionService->awardForTransaction($transaction);
                }
            });
    }

    public function down(): void
    {
        //
    }
};
