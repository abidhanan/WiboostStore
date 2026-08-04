<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ResellerCommission;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResellerCommissionService
{
    public const MINIMUM_WITHDRAWAL = 20000;

    public const COMMISSION_START_DATE = '2026-07-01 00:00:00';

    // Aturan "komisi mulai dihitung dari hari daftar" HANYA berlaku untuk reseller yang
    // mendaftar sejak tanggal ini (hari ini) ke depan. Reseller yang sudah daftar sebelum
    // tanggal ini tetap memakai COMMISSION_START_DATE -> total komisi mereka tidak berubah.
    public const NEW_RESELLER_RULE_START_DATE = '2026-08-05 00:00:00';

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

        if (! $transaction->created_at) {
            return null;
        }

        if ($transaction->payment_status !== 'paid' || $transaction->order_status !== 'success') {
            return null;
        }

        $transaction->loadMissing('user', 'product.category.parent.parent.parent');
        $user = $transaction->user;
        $product = $transaction->product;

        if (! $this->isCommissionEligible($user) || ! $product) {
            return null;
        }

        // Komisi hanya dihitung sejak hari user terdaftar sebagai reseller (atau sejak program dimulai, mana yang lebih baru).
        if ($transaction->created_at->lt($this->commissionStartDateForUser($user))) {
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

            $baseAmount = (float) $transaction->display_total_payable;
            if ($baseAmount <= 0) {
                $baseAmount = (float) $transaction->amount;
            }

            $commissionAmount = (int) round($baseAmount * $rate);

            if ($commissionAmount < 1) {
                return null;
            }

            if ($existing) {
                $existing->forceFill([
                    'user_id' => $user->id,
                    'category_slug' => $categorySlug,
                    'category_name' => $category?->name,
                    'product_name' => $product->name,
                    'rate' => $rate,
                    'base_amount' => $baseAmount,
                    'amount' => $commissionAmount,
                    'status' => 'success',
                ])->save();

                return $existing;
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

        $balanceWindowStart = $this->balanceWindowStart($user);

        $income = (float) ResellerCommission::query()
            ->where('user_id', $user->id)
            ->where('type', 'income')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereNotIn('status', ['failed', 'rejected']);
            })
            ->whereHas('transaction', fn ($query) => $query
                ->where('created_at', '>=', $balanceWindowStart)
                ->where('payment_status', 'paid')
                ->where('order_status', 'success'))
            ->sum('amount');

        $outgoing = (float) ResellerCommission::query()
            ->where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->where('created_at', '>=', $balanceWindowStart)
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        return max(0, (int) round($income - $outgoing));
    }

    public function balanceWindowStart(User $user): Carbon
    {
        $commissionStartDate = $this->commissionStartDateForUser($user);

        if (! $this->isReady()) {
            return $commissionStartDate;
        }

        $latestSettledWithdrawal = ResellerCommission::query()
            ->where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->where('created_at', '>=', $commissionStartDate)
            ->whereIn('status', ['success', 'approved'])
            ->latest('created_at')
            ->latest('id')
            ->first();

        if (! $latestSettledWithdrawal?->created_at) {
            return $commissionStartDate;
        }

        return $latestSettledWithdrawal->created_at->greaterThan($commissionStartDate)
            ? $latestSettledWithdrawal->created_at->copy()
            : $commissionStartDate;
    }

    public function canWithdrawNow(): bool
    {
        return true;
    }

    public function syncMissingForUser(User $user, int $limit = 2000): void
    {
        if (! $this->isReady() || ! $this->isCommissionEligible($user)) {
            return;
        }

        Transaction::query()
            ->with(['user', 'product.category.parent.parent.parent'])
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $this->commissionStartDateForUser($user))
            ->where('payment_status', 'paid')
            ->where('order_status', 'success')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->each(fn (Transaction $transaction) => $this->awardForTransaction($transaction));
    }

    public function syncRecentMissing(int $limit = 2000): void
    {
        if (! $this->isReady()) {
            return;
        }

        Transaction::query()
            ->with(['user', 'product.category.parent.parent.parent'])
            ->where('created_at', '>=', $this->commissionStartDate())
            ->where('payment_status', 'paid')
            ->where('order_status', 'success')
            ->whereHas('user', fn ($query) => $query->withResellerCommissionAccess())
            ->latest('id')
            ->limit($limit)
            ->get()
            ->each(fn (Transaction $transaction) => $this->awardForTransaction($transaction));
    }

    public function rateForCategory(?string $slug): float
    {
        return self::CATEGORY_RATES[$slug] ?? 0;
    }

    public function commissionStartDate(): Carbon
    {
        return Carbon::parse(self::COMMISSION_START_DATE, config('app.timezone'))->startOfDay();
    }

    /**
     * Tanggal mulai komisi untuk 1 user.
     * - Reseller yang mendaftar SEJAK NEW_RESELLER_RULE_START_DATE (hari ini) ke depan:
     *   dihitung dari hari dia mendaftar (awal hari reseller_joined_at).
     * - Reseller lama (daftar sebelum tanggal itu) atau tanpa reseller_joined_at:
     *   tetap memakai COMMISSION_START_DATE -> total komisi mereka TIDAK berubah.
     */
    public function commissionStartDateForUser(?User $user): Carbon
    {
        $globalStart = $this->commissionStartDate();
        $joinedAt = $user?->reseller_joined_at;

        if ($joinedAt) {
            $newRuleStart = Carbon::parse(self::NEW_RESELLER_RULE_START_DATE, config('app.timezone'))->startOfDay();

            if ($joinedAt->greaterThanOrEqualTo($newRuleStart)) {
                $joinedStart = $joinedAt->copy()->startOfDay();

                return $joinedStart->greaterThan($globalStart) ? $joinedStart : $globalStart;
            }
        }

        return $globalStart;
    }

    protected function isCommissionEligible(?User $user): bool
    {
        return $user !== null
            && $user->hasResellerCommissionAccess();
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
