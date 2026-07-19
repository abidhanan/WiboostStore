<?php

use App\Models\Transaction;
use App\Models\User;
use App\Services\ResellerCommissionService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        User::query()
            ->whereNotIn('role_id', User::ADMIN_ROLE_IDS)
            ->where(function ($query) {
                $query->where('role_id', User::ROLE_RESELLER)
                    ->orWhereNotNull('reseller_joined_at')
                    ->orWhere(function ($payoutQuery) {
                        $payoutQuery->whereNotNull('reseller_payout_account')
                            ->where('reseller_payout_account', '!=', '');
                    })
                    ->orWhere(function ($payoutQuery) {
                        $payoutQuery->whereNotNull('reseller_payout_name')
                            ->where('reseller_payout_name', '!=', '');
                    })
                    ->orWhereHas('resellerCommissions', fn ($commissionQuery) => $commissionQuery->where('type', 'income'));
            })
            ->orderBy('id')
            ->chunkById(200, function ($users) {
                foreach ($users as $user) {
                    $updates = [];

                    if ((int) $user->role_id !== User::ROLE_RESELLER) {
                        $updates['role_id'] = User::ROLE_RESELLER;
                    }

                    if ($user->reseller_joined_at === null) {
                        $updates['reseller_joined_at'] = now();
                    }

                    if ($updates !== []) {
                        $user->forceFill($updates)->saveQuietly();
                    }
                }
            });

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
