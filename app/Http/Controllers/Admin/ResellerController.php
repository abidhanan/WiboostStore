<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResellerCommission;
use App\Models\Transaction;
use App\Services\DiscordWebhookService;
use App\Services\ResellerCommissionService;
use App\Support\SimpleXlsx;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResellerController extends Controller
{
    public function commissions(Request $request)
    {
        $commissionService = app(ResellerCommissionService::class);
        $commissionService->syncRecentMissing();
        $commissionStartDate = $commissionService->commissionStartDate();

        $search = trim((string) $request->query('search', ''));
        $commissionFilter = (string) $request->query('commission_filter', 'all');
        $selectedMonth = $request->query('month', now()->format('m'));
        $selectedYear = $request->query('year', now()->format('Y'));
        $allowedCommissionFilters = ['all', 'suntik-sosmed', 'top-up-game', 'paket-data', 'aplikasi-premium'];

        if (! in_array($commissionFilter, $allowedCommissionFilters, true)) {
            $commissionFilter = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', (string) $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', (string) $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $incomeCommissions = ResellerCommission::query()
            ->with(['user', 'transaction.user', 'transaction.product'])
            ->where('type', 'income')
            ->whereHas('transaction', function ($query) use ($commissionStartDate, $selectedMonth, $selectedYear) {
                $query->where('created_at', '>=', $commissionStartDate)
                    ->whereMonth('created_at', $selectedMonth)
                    ->whereYear('created_at', $selectedYear);
            })
            ->when($commissionFilter !== 'all', fn ($query) => $query->where('category_slug', $commissionFilter))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('product_name', 'like', "%{$search}%")
                        ->orWhere('category_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('whatsapp', 'like', "%{$search}%");
                        })
                        ->orWhereHas('transaction', function ($transactionQuery) use ($search) {
                            $transactionQuery->where('invoice_number', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc(Transaction::select('created_at')->whereColumn('transactions.id', 'reseller_commissions.transaction_id')->limit(1))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.commissions.index', compact('incomeCommissions', 'search', 'commissionFilter', 'selectedMonth', 'selectedYear'));
    }

    public function exportCommissions(Request $request)
    {
        $commissionService = app(ResellerCommissionService::class);
        $commissionService->syncRecentMissing();
        $commissionStartDate = $commissionService->commissionStartDate();

        $search = trim((string) $request->query('search', ''));
        $commissionFilter = (string) $request->query('commission_filter', 'all');
        $selectedMonth = $request->query('month', now()->format('m'));
        $selectedYear = $request->query('year', now()->format('Y'));
        $allowedCommissionFilters = ['all', 'suntik-sosmed', 'top-up-game', 'paket-data', 'aplikasi-premium'];

        if (! in_array($commissionFilter, $allowedCommissionFilters, true)) {
            $commissionFilter = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', (string) $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', (string) $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $commissions = ResellerCommission::query()
            ->with(['user', 'transaction.user', 'transaction.product'])
            ->where('type', 'income')
            ->whereHas('transaction', function ($query) use ($commissionStartDate, $selectedMonth, $selectedYear) {
                $query->where('created_at', '>=', $commissionStartDate)
                    ->whereMonth('created_at', $selectedMonth)
                    ->whereYear('created_at', $selectedYear);
            })
            ->when($commissionFilter !== 'all', fn ($query) => $query->where('category_slug', $commissionFilter))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('product_name', 'like', "%{$search}%")
                        ->orWhere('category_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('whatsapp', 'like', "%{$search}%");
                        })
                        ->orWhereHas('transaction', function ($transactionQuery) use ($search) {
                            $transactionQuery->where('invoice_number', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc(Transaction::select('created_at')->whereColumn('transactions.id', 'reseller_commissions.transaction_id')->limit(1))
            ->orderByDesc('created_at')
            ->get();

        Carbon::setLocale('id');
        $monthName = Carbon::createFromDate((int) $selectedYear, (int) $selectedMonth, 1)->translatedFormat('F Y');
        $rows = $commissions->map(function (ResellerCommission $commission, int $index) {
            $transaction = $commission->transaction;

            return [
                $index + 1,
                optional($transaction?->created_at ?: $commission->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i'),
                $transaction?->invoice_number ?? '-',
                $commission->user?->name ?? '-',
                $commission->user?->email ?? '-',
                $commission->product_name ?: ($transaction?->display_product_name ?? '-'),
                $commission->category_name ?: '-',
                (float) ($transaction?->display_total_payable ?? $transaction?->amount ?? $commission->base_amount),
                (float) $commission->amount,
                $commission->display_status,
            ];
        })->all();

        return SimpleXlsx::download(
            'Wiboost Store - Laporan Komisi',
            'Komisi',
            'laporan-komisi-' . str_replace(' ', '-', strtolower($monthName)) . '.xlsx',
            'Periode: ' . $monthName,
            ['No', 'Waktu', 'Invoice', 'Reseller', 'Email Reseller', 'Produk', 'Kategori', 'Harga Transaksi', 'Komisi', 'Status'],
            $rows,
            [8, 20, 24, 28, 34, 52, 26, 20, 18, 18],
            [1],
            [8, 9],
            ['', '', '', '', '', '', 'TOTAL', (float) $commissions->sum('base_amount'), (float) $commissions->sum('amount'), '']
        );
    }

    public function withdrawals(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'all');
        $selectedMonth = $request->query('month', now()->format('m'));
        $selectedYear = $request->query('year', now()->format('Y'));
        $allowedStatuses = ['all', 'pending', 'processing', 'success', 'failed'];

        if (! in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', (string) $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', (string) $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $withdrawals = ResellerCommission::query()
            ->with(['user', 'approver'])
            ->where('type', 'withdrawal')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->when($status !== 'all', function ($query) use ($status) {
                $statusValues = match ($status) {
                    'success' => ['success', 'approved'],
                    'failed' => ['failed', 'rejected'],
                    default => [$status],
                };

                $query->whereIn('status', $statusValues);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('payout_method', 'like', "%{$search}%")
                        ->orWhere('payout_account', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('whatsapp', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.withdrawals.index', compact('withdrawals', 'search', 'status', 'selectedMonth', 'selectedYear'));
    }

    public function exportWithdrawals(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'all');
        $selectedMonth = (string) $request->query('month', now()->format('m'));
        $selectedYear = (string) $request->query('year', now()->format('Y'));
        $allowedStatuses = ['all', 'pending', 'processing', 'success', 'failed'];

        if (! in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $withdrawals = ResellerCommission::query()
            ->with(['user', 'approver'])
            ->where('type', 'withdrawal')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->when($status !== 'all', function ($query) use ($status) {
                $statusValues = match ($status) {
                    'success' => ['success', 'approved'],
                    'failed' => ['failed', 'rejected'],
                    default => [$status],
                };

                $query->whereIn('status', $statusValues);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('payout_method', 'like', "%{$search}%")
                        ->orWhere('payout_account', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('whatsapp', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        Carbon::setLocale('id');
        $monthName = Carbon::createFromDate((int) $selectedYear, (int) $selectedMonth, 1)->translatedFormat('F Y');
        $rows = $withdrawals->map(function (ResellerCommission $withdrawal, int $index) {
            return [
                $index + 1,
                'WD-' . str_pad((string) $withdrawal->id, 5, '0', STR_PAD_LEFT),
                $withdrawal->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-',
                $withdrawal->user?->name ?? '-',
                $withdrawal->user?->email ?? '-',
                $withdrawal->user?->whatsapp ?? '-',
                $withdrawal->payout_method ?: '-',
                $withdrawal->payout_account ?: '-',
                data_get($withdrawal->metadata, 'payout_name') ?: ($withdrawal->user?->reseller_payout_name ?? '-'),
                (float) $withdrawal->amount,
                $withdrawal->display_status,
                $withdrawal->approver?->name ?? '-',
                $withdrawal->approved_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-',
                $withdrawal->notes ?: '-',
            ];
        })->all();

        return SimpleXlsx::download(
            'Wiboost Store - Laporan Penarikan',
            'Penarikan',
            'laporan-penarikan-' . str_replace(' ', '-', strtolower($monthName)) . '.xlsx',
            'Periode: ' . $monthName,
            ['No', 'Invoice', 'Waktu', 'Reseller', 'Email', 'WhatsApp', 'Metode', 'Nomor Tujuan', 'Nama Tujuan', 'Nominal', 'Status', 'Admin', 'Waktu Proses', 'Catatan'],
            $rows,
            [8, 16, 20, 28, 34, 20, 18, 26, 28, 18, 16, 24, 20, 48],
            [1],
            [10],
            ['', '', '', '', '', '', '', '', 'TOTAL', (float) $withdrawals->sum('amount'), '', '', '', '']
        );
    }

    public function updateWithdrawal(Request $request, ResellerCommission $commission, DiscordWebhookService $discordWebhookService)
    {
        abort_unless($commission->type === 'withdrawal', 404);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,success,failed'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'status' => 'status penarikan',
            'notes' => 'catatan admin',
        ]);

        $updatedWithdrawal = DB::transaction(function () use ($commission, $validated) {
            $lockedCommission = ResellerCommission::query()
                ->whereKey($commission->id)
                ->where('type', 'withdrawal')
                ->lockForUpdate()
                ->firstOrFail();

            $status = $validated['status'];
            $notes = trim((string) ($validated['notes'] ?? ''));

            if ($notes === '') {
                $notes = match ($status) {
                    'processing' => 'Penarikan komisi sedang diproses admin.',
                    'success' => 'Transfer komisi sudah dikonfirmasi admin.',
                    'failed' => 'Penarikan komisi gagal diproses admin.',
                    default => 'Menunggu konfirmasi admin setelah transfer manual.',
                };
            }

            $lockedCommission->forceFill([
                'status' => $status,
                'notes' => $notes,
                'approved_by' => in_array($status, ['success', 'failed'], true) ? auth()->id() : null,
                'approved_at' => in_array($status, ['success', 'failed'], true) ? now() : null,
            ])->save();

            return $lockedCommission->fresh(['user', 'approver']);
        });

        if ($updatedWithdrawal instanceof ResellerCommission) {
            $status = (string) $updatedWithdrawal->status;
            $discordWebhookService->sendWithdrawalAlertOnce(
                $updatedWithdrawal,
                'status_' . $status,
                match ($status) {
                    'processing' => 'Penarikan komisi sedang diproses',
                    'success' => 'Penarikan komisi berhasil',
                    'failed' => 'Penarikan komisi gagal',
                    default => 'Penarikan komisi menunggu',
                },
                $updatedWithdrawal->notes ?: 'Status penarikan komisi diperbarui admin.',
                match ($status) {
                    'success' => 4968121,
                    'failed' => 16738680,
                    default => 16433050,
                }
            );
        }

        return redirect()->route('admin.withdrawals.index', array_filter([
            'search' => $request->input('search'),
            'status' => $request->input('filter_status', 'all'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ], fn ($value) => filled($value) && $value !== 'all'))
            ->with('success', 'Status penarikan berhasil diperbarui.');
    }
}
