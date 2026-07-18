<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResellerCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResellerController extends Controller
{
    public function commissions(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $commissionFilter = (string) $request->query('commission_filter', 'all');
        $selectedMonth = $request->query('month', date('m'));
        $selectedYear = $request->query('year', date('Y'));
        $allowedCommissionFilters = ['all', 'suntik-sosmed', 'top-up-game', 'paket-data', 'aplikasi-premium'];

        if (! in_array($commissionFilter, $allowedCommissionFilters, true)) {
            $commissionFilter = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', (string) $selectedMonth)) {
            $selectedMonth = date('m');
        }

        if (! preg_match('/^\d{4}$/', (string) $selectedYear)) {
            $selectedYear = date('Y');
        }

        $incomeCommissions = ResellerCommission::query()
            ->with(['user', 'transaction.user', 'transaction.product'])
            ->where('type', 'income')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
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
            ->latest()
            ->limit(80)
            ->get();

        return view('admin.commissions.index', compact('incomeCommissions', 'search', 'commissionFilter', 'selectedMonth', 'selectedYear'));
    }

    public function exportCommissions(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $commissionFilter = (string) $request->query('commission_filter', 'all');
        $selectedMonth = $request->query('month', date('m'));
        $selectedYear = $request->query('year', date('Y'));
        $allowedCommissionFilters = ['all', 'suntik-sosmed', 'top-up-game', 'paket-data', 'aplikasi-premium'];

        if (! in_array($commissionFilter, $allowedCommissionFilters, true)) {
            $commissionFilter = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', (string) $selectedMonth)) {
            $selectedMonth = date('m');
        }

        if (! preg_match('/^\d{4}$/', (string) $selectedYear)) {
            $selectedYear = date('Y');
        }

        $commissions = ResellerCommission::query()
            ->with(['user', 'transaction.user', 'transaction.product'])
            ->where('type', 'income')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
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
            ->latest()
            ->get();

        $fileName = 'laporan-komisi-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($commissions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Waktu', 'Invoice', 'Reseller', 'Email Reseller', 'Produk', 'Kategori', 'Harga Transaksi', 'Komisi', 'Status']);

            foreach ($commissions as $commission) {
                $transaction = $commission->transaction;
                fputcsv($handle, [
                    optional($transaction?->created_at ?: $commission->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i'),
                    $transaction?->invoice_number ?? '-',
                    $commission->user?->name ?? '-',
                    $commission->user?->email ?? '-',
                    $commission->product_name ?: ($transaction?->product?->name ?? '-'),
                    $commission->category_name ?: '-',
                    (float) ($transaction?->display_total_payable ?? $transaction?->amount ?? $commission->base_amount),
                    (float) $commission->amount,
                    $commission->display_status,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function withdrawals(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'all');
        $allowedStatuses = ['all', 'pending', 'approved', 'rejected'];

        if (! in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        $withdrawals = ResellerCommission::query()
            ->with(['user', 'approver'])
            ->where('type', 'withdrawal')
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
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
            ->limit(100)
            ->get();

        return view('admin.withdrawals.index', compact('withdrawals', 'search', 'status'));
    }

    public function updateWithdrawal(Request $request, ResellerCommission $commission)
    {
        abort_unless($commission->type === 'withdrawal', 404);

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,pending'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'status' => 'status penarikan',
            'notes' => 'catatan admin',
        ]);

        DB::transaction(function () use ($commission, $validated) {
            $lockedCommission = ResellerCommission::query()
                ->whereKey($commission->id)
                ->where('type', 'withdrawal')
                ->lockForUpdate()
                ->firstOrFail();

            $status = $validated['status'];
            $notes = trim((string) ($validated['notes'] ?? ''));

            if ($notes === '') {
                $notes = match ($status) {
                    'approved' => 'Transfer komisi sudah dikonfirmasi admin.',
                    'rejected' => 'Penarikan komisi ditolak oleh admin.',
                    default => 'Menunggu konfirmasi admin setelah transfer manual.',
                };
            }

            $lockedCommission->forceFill([
                'status' => $status,
                'notes' => $notes,
                'approved_by' => $status === 'pending' ? null : auth()->id(),
                'approved_at' => $status === 'pending' ? null : now(),
            ])->save();
        });

        return redirect()->route('admin.withdrawals.index', array_filter([
            'search' => $request->input('search'),
            'status' => $request->input('filter_status', 'all'),
        ], fn ($value) => filled($value) && $value !== 'all'))
            ->with('success', 'Status penarikan berhasil diperbarui.');
    }
}
