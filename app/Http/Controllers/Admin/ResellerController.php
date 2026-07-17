<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResellerCommission;
use App\Models\User;
use App\Services\ResellerCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResellerController extends Controller
{
    public function index(ResellerCommissionService $commissionService)
    {
        $resellers = User::query()
            ->where('role_id', 3)
            ->where(function ($query) {
                $query->whereNull('membership_expires_at')
                    ->orWhere('membership_expires_at', '<=', now());
            })
            ->withCount([
                'resellerCommissions as total_sales' => fn ($query) => $query->where('type', 'income')->where('status', 'success'),
            ])
            ->latest('reseller_joined_at')
            ->get()
            ->map(function (User $user) use ($commissionService) {
                $user->available_commission = $commissionService->availableBalance($user);
                $user->total_income = (int) ResellerCommission::query()
                    ->where('user_id', $user->id)
                    ->where('type', 'income')
                    ->where('status', 'success')
                    ->sum('amount');

                return $user;
            });

        $memberUpgrades = User::query()
            ->whereNotNull('reseller_joined_at')
            ->where('membership_expires_at', '>', now())
            ->count();

        $pendingWithdrawals = ResellerCommission::query()
            ->with('user')
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $withdrawalHistories = ResellerCommission::query()
            ->with(['user', 'approver'])
            ->where('type', 'withdrawal')
            ->latest()
            ->limit(40)
            ->get();

        return view('admin.resellers.index', compact('resellers', 'memberUpgrades', 'pendingWithdrawals', 'withdrawalHistories'));
    }

    public function commissions()
    {
        $incomeCommissions = ResellerCommission::query()
            ->with(['user', 'transaction'])
            ->where('type', 'income')
            ->latest()
            ->limit(80)
            ->get();

        $pendingWithdrawals = ResellerCommission::query()
            ->with('user')
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $withdrawalHistories = ResellerCommission::query()
            ->with(['user', 'approver'])
            ->where('type', 'withdrawal')
            ->latest()
            ->limit(60)
            ->get();

        $totalIncome = (int) ResellerCommission::query()
            ->where('type', 'income')
            ->where('status', 'success')
            ->sum('amount');
        $totalApprovedWithdrawal = (int) ResellerCommission::query()
            ->where('type', 'withdrawal')
            ->where('status', 'approved')
            ->sum('amount');
        $pendingWithdrawalTotal = (int) $pendingWithdrawals->sum('amount');

        return view('admin.commissions.index', compact(
            'incomeCommissions',
            'pendingWithdrawals',
            'withdrawalHistories',
            'totalIncome',
            'totalApprovedWithdrawal',
            'pendingWithdrawalTotal'
        ));
    }

    public function updateWithdrawal(Request $request, ResellerCommission $commission)
    {
        abort_unless($commission->type === 'withdrawal', 404);

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'status' => 'status penarikan',
            'notes' => 'catatan',
        ]);

        DB::transaction(function () use ($commission, $validated) {
            $lockedCommission = ResellerCommission::query()->lockForUpdate()->findOrFail($commission->id);

            if ($lockedCommission->status !== 'pending') {
                return;
            }

            $lockedCommission->forceFill([
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?: ($validated['status'] === 'approved'
                    ? 'Penarikan komisi sudah dikonfirmasi admin.'
                    : 'Penarikan komisi ditolak admin.'),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ])->save();
        });

        return back()->with('success', 'Status penarikan reseller berhasil diperbarui.');
    }
}
