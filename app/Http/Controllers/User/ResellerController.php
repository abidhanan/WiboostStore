<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ResellerCommission;
use App\Models\User;
use App\Services\ResellerCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResellerController extends Controller
{
    public function index(Request $request, ResellerCommissionService $commissionService)
    {
        $user = $request->user();
        $isReseller = $user->isReseller();
        $resellerStorageReady = $commissionService->isReady();
        $availableCommission = $isReseller && $resellerStorageReady ? $commissionService->availableBalance($user) : 0;
        $minimumWithdrawal = ResellerCommissionService::MINIMUM_WITHDRAWAL;
        $canWithdrawNow = $availableCommission >= $minimumWithdrawal;

        $totals = [
            'income' => $resellerStorageReady ? (int) ResellerCommission::query()
                ->where('user_id', $user->id)
                ->where('type', 'income')
                ->where('status', 'success')
                ->sum('amount') : 0,
            'pending_withdrawal' => $resellerStorageReady ? (int) ResellerCommission::query()
                ->where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->where('status', 'pending')
                ->sum('amount') : 0,
            'approved_withdrawal' => $resellerStorageReady ? (int) ResellerCommission::query()
                ->where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->where('status', 'approved')
                ->sum('amount') : 0,
        ];

        $productCommissions = $resellerStorageReady ? ResellerCommission::query()
            ->select('product_name', 'category_name')
            ->selectRaw('COUNT(*) as total_sales')
            ->selectRaw('SUM(amount) as total_commission')
            ->where('user_id', $user->id)
            ->where('type', 'income')
            ->where('status', 'success')
            ->groupBy('product_name', 'category_name')
            ->orderByDesc('total_commission')
            ->limit(20)
            ->get() : collect();

        $commissionHistories = $resellerStorageReady ? ResellerCommission::query()
            ->with('transaction')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(30)
            ->get() : collect();

        $pendingWithdrawal = $resellerStorageReady ? ResellerCommission::query()
            ->where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->latest()
            ->first() : null;

        return view('user.reseller.index', compact(
            'user',
            'isReseller',
            'availableCommission',
            'canWithdrawNow',
            'minimumWithdrawal',
            'totals',
            'productCommissions',
            'commissionHistories',
            'pendingWithdrawal',
            'resellerStorageReady'
        ));
    }

    public function join(Request $request)
    {
        if (! app(ResellerCommissionService::class)->isReady()) {
            return redirect()->route('user.reseller.index')->with('error', 'Fitur reseller sedang disiapkan. Jalankan migrasi database terlebih dahulu.');
        }

        $validated = $this->validatePayout($request);

        DB::transaction(function () use ($request, $validated) {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);

            $user->forceFill([
                'role_id' => 3,
                'reseller_payout_method' => $validated['reseller_payout_method'],
                'reseller_payout_account' => $validated['reseller_payout_account'],
                'reseller_payout_name' => $validated['reseller_payout_name'],
                'reseller_joined_at' => $user->reseller_joined_at ?: now(),
            ])->save();
        });

        return redirect()->route('user.reseller.index')->with('success', 'Selamat bergabung menjadi reseller!');
    }

    public function leave(Request $request)
    {
        if (! $request->user()->isReseller()) {
            return redirect()->route('user.reseller.index');
        }

        $request->user()->forceFill([
            'role_id' => 2,
        ])->save();

        return redirect()->route('user.dashboard')->with('success', 'Kamu sudah berhenti menjadi reseller.');
    }

    public function updatePayout(Request $request)
    {
        if (! $request->user()->isReseller()) {
            return redirect()->route('user.reseller.index')->with('error', 'Gabung reseller dulu sebelum mengubah tujuan penarikan.');
        }

        $validated = $this->validatePayout($request);

        $request->user()->forceFill([
            'reseller_payout_method' => $validated['reseller_payout_method'],
            'reseller_payout_account' => $validated['reseller_payout_account'],
            'reseller_payout_name' => $validated['reseller_payout_name'],
        ])->save();

        return redirect()->route('user.reseller.index')->with('success', 'Tujuan penarikan berhasil diperbarui.');
    }

    public function withdraw(Request $request, ResellerCommissionService $commissionService)
    {
        $user = $request->user();

        if (! $user->isReseller()) {
            return redirect()->route('user.reseller.index')->with('error', 'Gabung reseller dulu sebelum menarik komisi.');
        }

        if (! $commissionService->isReady()) {
            return redirect()->route('user.reseller.index')->with('error', 'Fitur reseller sedang disiapkan. Jalankan migrasi database terlebih dahulu.');
        }

        if (blank($user->reseller_payout_method) || blank($user->reseller_payout_account) || blank($user->reseller_payout_name)) {
            return back()->with('error', 'Lengkapi data e-wallet terlebih dahulu.');
        }

        return DB::transaction(function () use ($user, $commissionService) {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            $hasPending = ResellerCommission::query()
                ->where('user_id', $lockedUser->id)
                ->where('type', 'withdrawal')
                ->where('status', 'pending')
                ->lockForUpdate()
                ->exists();

            if ($hasPending) {
                return back()->with('error', 'Masih ada penarikan komisi yang menunggu konfirmasi admin.');
            }

            $available = $commissionService->availableBalance($lockedUser);

            if ($available < ResellerCommissionService::MINIMUM_WITHDRAWAL) {
                return back()->with('error', 'Minimal komisi yang bisa ditarik adalah Rp 20.000.');
            }

            ResellerCommission::query()->create([
                'user_id' => $lockedUser->id,
                'type' => 'withdrawal',
                'amount' => $available,
                'status' => 'pending',
                'payout_method' => $lockedUser->reseller_payout_method,
                'payout_account' => $lockedUser->reseller_payout_account,
                'metadata' => [
                    'payout_name' => $lockedUser->reseller_payout_name,
                ],
                'notes' => 'Menunggu konfirmasi admin maksimal 24 jam.',
            ]);

            return redirect()->route('user.reseller.index')->with('success', 'Permintaan penarikan komisi berhasil dikirim ke admin.');
        });
    }

    protected function validatePayout(Request $request): array
    {
        return $request->validate([
            'reseller_payout_method' => ['required', 'string', 'max:40'],
            'reseller_payout_account' => ['required', 'string', 'max:120'],
            'reseller_payout_name' => ['required', 'string', 'max:120'],
        ], [], [
            'reseller_payout_method' => 'metode penarikan',
            'reseller_payout_account' => 'nomor e-wallet',
            'reseller_payout_name' => 'nama pemilik e-wallet',
        ]);
    }
}
