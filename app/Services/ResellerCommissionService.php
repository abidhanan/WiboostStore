<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ResellerCommission;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResellerCommissionService
{
    public const MINIMUM_WITHDRAWAL = 20000;

    public const CATEGORY_RATES = [
        'suntik-sosmed' => 0.10,
        'top-up-game' => 0.02,
        'paket-data' => 0.02,
        'kuota-murah' => 0.02,
        'aplikasi-premium' => 0.10,
    ];

    public function isReady(): bool
    {
        return Schema::hasTable('reseller_commissions');
    }

    public function awardForTransaction(Transaction $transaction): ?ResellerCommission
    {
        if (! $this->isReady()) {
            return null;
        }

        if ($transaction->payment_status !== 'paid' || $transaction->order_status !== 'success') {
            return null;
        }

        $transaction->loadMissing('user', 'product.category.parent.parent.parent');
        $user = $transaction->user;
        $product = $transaction->product;

        if (! $user?->isReseller() || ! $product) {
            return null;
        }

        $category = $this->topCategory($product);
        $categorySlug = $category?->slug;
        $rate = self::CATEGORY_RATES[$categorySlug] ?? 0;

        if ($rate <= 0) {
            return null;
        }

        return DB::transaction(function () use ($transaction, $user, $product, $category, $categorySlug, $rate) {
            $existing = ResellerCommission::query()
                ->where('transaction_id', $transaction->id)
                ->where('type', 'income')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $baseAmount = (float) $transaction->display_total_payable;
            if ($baseAmount <= 0) {
                $baseAmount = (float) $transaction->amount;
            }

            $commissionAmount = (int) round($baseAmount * $rate);

            if ($commissionAmount < 1) {
                return null;
            }

            return ResellerCommission::query()->create([
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'type' => 'income',
                'category_slug' => $categorySlug,
                'category_name' => $category?->name,
                'product_name' => $product->name,
                'rate' => $rate,
                'base_amount' => $baseAmount,
                'amount' => $commissionAmount,
                'status' => 'success',
            ]);
        });
    }

    public function availableBalance(User $user): int
    {
        if (! $this->isReady()) {
            return 0;
        }

        $income = (float) ResellerCommission::query()
            ->where('user_id', $user->id)
            ->where('type', 'income')
            ->where('status', 'success')
            ->sum('amount');

        $outgoing = (float) ResellerCommission::query()
            ->where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->whereIn('status', ['pending', 'approved'])
            ->sum('amount');

        return max(0, (int) round($income - $outgoing));
    }

    public function canWithdrawNow(): bool
    {
        return true;
    }

    public function rateForCategory(?string $slug): float
    {
        return self::CATEGORY_RATES[$slug] ?? 0;
    }

    protected function topCategory(Product $product)
    {
        $product->loadMissing('category.parent.parent.parent');
        $category = $product->category;

        while ($category?->parent) {
            $category = $category->parent;
        }

        return $category;
    }
}
