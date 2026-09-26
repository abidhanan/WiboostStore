<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\DiscordWebhookService;
use App\Support\SimpleXlsx;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    /**
     * Menampilkan daftar top up, penukaran poin, dan refund saldo.
     */
    public function index(Request $request)
    {
        $depositFilter = (string) $request->query('deposit_filter', 'all');
        $selectedMonth = $request->query('month', now()->format('m'));
        $selectedYear = $request->query('year', now()->format('Y'));
        $allowedDepositFilters = ['all', 'paid', 'failed', 'topup', 'poin', 'refund'];

        if (! in_array($depositFilter, $allowedDepositFilters, true)) {
            $depositFilter = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', (string) $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', (string) $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $this->syncPointExchangeDeposits($selectedMonth, $selectedYear);
        $this->syncRefundWalletHistories($selectedMonth, $selectedYear);

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
                ->where('invoice_number', 'not like', 'REFUND-%')
                ->where(function ($topupQuery) {
                    $topupQuery->whereNull('payment_method')
                        ->orWhereNotIn('payment_method', ['poin', 'refund']);
                })
                ->where(function ($topupQuery) {
                    $topupQuery->whereNull('payment_response->purpose')
                        ->orWhereNotIn('payment_response->purpose', ['point_exchange', 'refund_wallet']);
                });
        } elseif ($depositFilter === 'poin') {
            $query->where(function ($pointQuery) {
                $pointQuery->where('invoice_number', 'like', 'POIN-%')
                    ->orWhere('payment_method', 'poin')
                    ->orWhere('payment_response->purpose', 'point_exchange');
            });
        } elseif ($depositFilter === 'refund') {
            $query->where(function ($refundQuery) {
                $refundQuery->where('invoice_number', 'like', 'REFUND-%')
                    ->orWhere('payment_method', 'refund')
                    ->orWhere('payment_response->purpose', 'refund_wallet');
            });
        }

        $deposits = $query->paginate(20)->withQueryString();

        return view('admin.deposits.index', compact('deposits', 'depositFilter', 'selectedMonth', 'selectedYear'));
    }

    public function exportExcel(Request $request)
    {
        $depositFilter = (string) $request->query('deposit_filter', 'all');
        $selectedMonth = (string) $request->query('month', now()->format('m'));
        $selectedYear = (string) $request->query('year', now()->format('Y'));
        $search = trim((string) $request->query('search', ''));
        $allowedDepositFilters = ['all', 'paid', 'failed', 'topup', 'poin', 'refund'];

        if (! in_array($depositFilter, $allowedDepositFilters, true)) {
            $depositFilter = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $this->syncPointExchangeDeposits($selectedMonth, $selectedYear);
        $this->syncRefundWalletHistories($selectedMonth, $selectedYear);

        $query = Deposit::with('user')
            ->where('invoice_number', 'not like', 'MEM-%')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->where(function ($statusQuery) {
                $statusQuery->whereNull('payment_status')
                    ->orWhereNotIn('payment_status', ['unpaid', 'pending']);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            });

        if ($depositFilter === 'paid') {
            $query->where('payment_status', 'paid');
        } elseif ($depositFilter === 'failed') {
            $query->whereIn('payment_status', ['failed', 'expired']);
        } elseif ($depositFilter === 'topup') {
            $query->where('invoice_number', 'not like', 'MEM-%')
                ->where('invoice_number', 'not like', 'POIN-%')
                ->where('invoice_number', 'not like', 'REFUND-%')
                ->where(function ($topupQuery) {
                    $topupQuery->whereNull('payment_method')
                        ->orWhereNotIn('payment_method', ['poin', 'refund']);
                })
                ->where(function ($topupQuery) {
                    $topupQuery->whereNull('payment_response->purpose')
                        ->orWhereNotIn('payment_response->purpose', ['point_exchange', 'refund_wallet']);
                });
        } elseif ($depositFilter === 'poin') {
            $query->where(function ($pointQuery) {
                $pointQuery->where('invoice_number', 'like', 'POIN-%')
                    ->orWhere('payment_method', 'poin')
                    ->orWhere('payment_response->purpose', 'point_exchange');
            });
        } elseif ($depositFilter === 'refund') {
            $query->where(function ($refundQuery) {
                $refundQuery->where('invoice_number', 'like', 'REFUND-%')
                    ->orWhere('payment_method', 'refund')
                    ->orWhere('payment_response->purpose', 'refund_wallet');
            });
        }

        $deposits = $query->latest()->get();
        $totalAmount = $deposits->sum(fn (Deposit $deposit) => (float) $deposit->amount);
        $totalPaid = $deposits->sum(fn (Deposit $deposit) => (float) ($deposit->paid_amount ?? $deposit->amount ?? 0));

        Carbon::setLocale('id');
        $monthName = Carbon::createFromDate((int) $selectedYear, (int) $selectedMonth, 1)->translatedFormat('F Y');
        $statusLabels = [
            'paid' => 'Berhasil',
            'success' => 'Berhasil',
            'unpaid' => 'Pending',
            'pending' => 'Pending',
            'failed' => 'Gagal',
            'expired' => 'Expired',
            'refund' => 'Refund',
        ];

        $rows = $deposits->map(function (Deposit $deposit, int $index) use ($statusLabels) {
            return [
                $index + 1,
                $deposit->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-',
                $deposit->invoice_number ?? '-',
                $deposit->user->name ?? 'Guest',
                $deposit->user->email ?? '-',
                $deposit->isRefundCredit() ? 'Refund' : ($deposit->isPointExchange() ? 'Poin' : 'Top Up'),
                (float) ($deposit->amount ?? 0),
                (float) ($deposit->paid_amount ?? $deposit->amount ?? 0),
                $statusLabels[strtolower((string) ($deposit->payment_status ?? 'pending'))] ?? (string) ($deposit->payment_status ?? '-'),
            ];
        })->all();

        return SimpleXlsx::download(
            'Wiboost Store - Laporan Deposit',
            'Deposit',
            'laporan-deposit-' . str_replace(' ', '-', strtolower($monthName)) . '.xlsx',
            'Periode: ' . $monthName,
            ['No', 'Waktu', 'Invoice', 'Nama', 'Email', 'Jenis', 'Nominal', 'Dibayar', 'Status'],
            $rows,
            [8, 20, 24, 28, 34, 18, 18, 18, 18],
            [1],
            [7, 8],
            ['', '', '', '', '', 'TOTAL', (float) $totalAmount, (float) $totalPaid, '']
        );
    }

    protected function syncPointExchangeDeposits(string $selectedMonth, string $selectedYear): void
    {
        WalletHistory::query()
            ->where('type', 'topup')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->where(function ($query) {
                $query->where('invoice_number', 'like', 'POIN-%')
                    ->orWhere('description', 'like', 'Klaim penukaran%poin%');
            })
            ->chunkById(300, function ($histories) {
                $histories->each(function (WalletHistory $history) {
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
            });
    }

    protected function syncRefundWalletHistories(string $selectedMonth, string $selectedYear): void
    {
        WalletHistory::query()
            ->where('type', 'refund')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->chunkById(300, function ($histories) {
                $histories->each(function (WalletHistory $history) {
                    $sourceInvoice = filled($history->invoice_number)
                        ? (string) $history->invoice_number
                        : 'HIS-' . $history->id;
                    $invoiceNumber = str_starts_with($sourceInvoice, 'REFUND-')
                        ? $sourceInvoice
                        : 'REFUND-' . $sourceInvoice;

                    $deposit = Deposit::firstOrNew(['invoice_number' => $invoiceNumber]);
                    $deposit->forceFill([
                        'user_id' => $history->user_id,
                        'amount' => $history->amount,
                        'paid_amount' => $history->amount,
                        'payment_status' => 'refund',
                        'payment_method' => 'refund',
                        'payment_response' => [
                            'purpose' => 'refund_wallet',
                            'wallet_history_id' => $history->id,
                            'source_invoice_number' => $history->invoice_number,
                            'description' => $history->description,
                        ],
                        'created_at' => $history->created_at,
                        'updated_at' => $history->updated_at,
                    ])->save();
                });
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

        if ($deposit->isRefundCredit()) {
            return back()->with('error', 'Catatan refund saldo tidak bisa diproses sebagai deposit/top up.');
        }

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
