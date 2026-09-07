<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\DiscordWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    /**
     * Menampilkan daftar semua top up / deposit.
     */
    public function index(Request $request)
    {
        $this->syncPointExchangeDeposits();

        $depositFilter = (string) $request->query('deposit_filter', 'all');
        $selectedMonth = $request->query('month', date('m'));
        $selectedYear = $request->query('year', date('Y'));
        $allowedDepositFilters = ['all', 'paid', 'failed', 'topup', 'poin'];

        if (! in_array($depositFilter, $allowedDepositFilters, true)) {
            $depositFilter = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', (string) $selectedMonth)) {
            $selectedMonth = date('m');
        }

        if (! preg_match('/^\d{4}$/', (string) $selectedYear)) {
            $selectedYear = date('Y');
        }

        $query = Deposit::with('user')
            ->where('invoice_number', 'not like', 'MEM-%')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
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
            $query->where('invoice_number', 'not like', 'MEM-%')
                ->where('invoice_number', 'not like', 'POIN-%')
                ->where(function ($topupQuery) {
                    $topupQuery->whereNull('payment_method')
                        ->orWhere('payment_method', '!=', 'poin');
                })
                ->where(function ($topupQuery) {
                    $topupQuery->whereNull('payment_response->purpose')
                        ->orWhere('payment_response->purpose', '!=', 'point_exchange');
                });
        } elseif ($depositFilter === 'poin') {
            $query->where(function ($pointQuery) {
                $pointQuery->where('invoice_number', 'like', 'POIN-%')
                    ->orWhere('payment_method', 'poin')
                    ->orWhere('payment_response->purpose', 'point_exchange');
            });
        }

        $deposits = $query->paginate(20)->withQueryString();

        return view('admin.deposits.index', compact('deposits', 'depositFilter', 'selectedMonth', 'selectedYear'));
    }

    protected function syncPointExchangeDeposits(): void
    {
        WalletHistory::query()
            ->where('type', 'topup')
            ->where(function ($query) {
                $query->where('invoice_number', 'like', 'POIN-%')
                    ->orWhere('description', 'like', 'Klaim penukaran%poin%');
            })
            ->latest('id')
            ->limit(300)
            ->get()
            ->each(function (WalletHistory $history) {
                $invoiceNumber = filled($history->invoice_number)
                    ? (string) $history->invoice_number
                    : 'POIN-HIS' . $history->id;

                $deposit = Deposit::firstOrNew(['invoice_number' => $invoiceNumber]);

                if ($deposit->exists) {
                    return;
                }

                $deposit->forceFill([
                    'user_id' => $history->user_id,
                    'amount' => $history->amount,
                    'paid_amount' => $history->amount,
                    'payment_status' => 'paid',
                    'payment_method' => 'poin',
                    'payment_response' => [
                        'purpose' => 'point_exchange',
                        'wallet_history_id' => $history->id,
                    ],
                    'created_at' => $history->created_at,
                    'updated_at' => $history->updated_at,
                ])->save();
            });
    }

    /**
     * Update status deposit (Berguna untuk Approve transfer manual)
     */
    public function update(Request $request, DiscordWebhookService $discordWebhookService, $id)
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

            $freshDeposit = $deposit->fresh('user') ?: $deposit;
            $discordWebhookService->sendDepositAlertOnce(
                $freshDeposit,
                'manual_paid',
                $freshDeposit->isMembershipPayment() ? 'Membership ditandai lunas admin' : 'Deposit ditandai lunas admin',
                $message,
                4968121
            );

            return back()->with('success', $message);
        }

        // Jika admin membatalkan (mengubah jadi failed)
        $deposit->update(['payment_status' => $request->payment_status]);

        if ($request->payment_status === 'failed') {
            $discordWebhookService->sendDepositAlertOnce(
                $deposit->fresh('user') ?: $deposit,
                'manual_failed',
                'Deposit ditandai gagal admin',
                'Admin mengubah status deposit menjadi gagal.',
                16738680
            );
        }

        return back()->with('success', 'Status deposit berhasil diubah.');
    }
}
