<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use App\Models\WalletHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    /**
     * Menampilkan daftar semua top up / deposit.
     */
    public function index(Request $request)
    {
        $depositFilter = (string) $request->query('deposit_filter', 'all');
        $allowedDepositFilters = ['all', 'paid', 'failed', 'topup'];

        if (! in_array($depositFilter, $allowedDepositFilters, true)) {
            $depositFilter = 'all';
        }

        $query = Deposit::with('user')
            ->where('invoice_number', 'not like', 'MEM-%')
            ->where(function ($statusQuery) {
                $statusQuery->whereNull('payment_status')
                    ->orWhereNotIn('payment_status', ['unpaid', 'pending']);
            })
            ->latest();

        // Fitur Pencarian
        if ($request->filled('search')) {
            $query->where(function ($searchQuery) use ($request) {
                $searchQuery->where('invoice_number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($depositFilter === 'paid') {
            $query->where('payment_status', 'paid');
        } elseif ($depositFilter === 'failed') {
            $query->whereIn('payment_status', ['failed', 'expired']);
        } elseif ($depositFilter === 'topup') {
            $query->where('invoice_number', 'not like', 'MEM-%');
        }

        $deposits = $query->paginate(20)->withQueryString();

        return view('admin.deposits.index', compact('deposits', 'depositFilter'));
    }

    /**
     * Update status deposit (Berguna untuk Approve transfer manual)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,failed,unpaid'
        ]);

        $deposit = Deposit::findOrFail($id);

        // Jika admin mengubah status menjadi LUNAS, dan sebelumnya belum lunas
        if ($request->payment_status == 'paid' && $deposit->payment_status != 'paid') {
            $message = DB::transaction(function () use ($deposit) {
                $lockedDeposit = Deposit::query()
                    ->lockForUpdate()
                    ->findOrFail($deposit->id);

                $lockedDeposit->update(['payment_status' => 'paid', 'payment_method' => 'manual_admin']);

                $user = User::query()
                    ->lockForUpdate()
                    ->findOrFail($lockedDeposit->user_id);

                if ($lockedDeposit->isMembershipPayment()) {
                    if (! $lockedDeposit->membershipActivated()) {
                        $user->activateMembership((int) data_get($lockedDeposit->payment_response, 'membership_days', 30));
                        $lockedDeposit->markMembershipActivated();
                    }

                    return 'Membership ' . $user->name . ' berhasil diaktifkan 30 hari.';
                }

                $user->increment('balance', $lockedDeposit->amount);

                WalletHistory::firstOrCreate(
                    [
                        'invoice_number' => $lockedDeposit->invoice_number,
                        'type' => 'topup',
                    ],
                    [
                        'user_id' => $lockedDeposit->user_id,
                        'amount' => $lockedDeposit->amount,
                        'description' => 'Top Up Saldo via MANUAL ADMIN',
                    ]
                );

                return 'Deposit disetujui! Saldo Rp ' . number_format($lockedDeposit->amount, 0, ',', '.') . ' telah ditambahkan ke akun ' . $user->name;
            });

            return back()->with('success', $message);
        }

        // Jika admin membatalkan (mengubah jadi failed)
        $deposit->update(['payment_status' => $request->payment_status]);
        return back()->with('success', 'Status deposit berhasil diubah.');
    }
}
