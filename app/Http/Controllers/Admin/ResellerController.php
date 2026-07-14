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

        return view('admin.resellers.index', compact('resellers', 'pendingWithdrawals', 'withdrawalHistories'));
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
