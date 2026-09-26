<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Support\SimpleXlsx;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DurationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $durationFilter = (string) $request->query('duration_filter', 'all');
        $selectedMonth = (string) $request->query('month', now()->format('m'));
        $selectedYear = (string) $request->query('year', now()->format('Y'));
        $allowedDurationFilters = ['all', 'waiting_logout', 'uploaded'];

        if (! in_array($durationFilter, $allowedDurationFilters, true)) {
            $durationFilter = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $durations = Transaction::query()
            ->with(['user', 'product'])
            ->where('payment_status', 'paid')
            ->whereIn('order_status', ['processing', 'success'])
            ->whereNotNull('credential_expires_at')
            ->where('credential_expires_at', '<=', now())
            ->whereMonth('credential_expires_at', (int) $selectedMonth)
            ->whereYear('credential_expires_at', (int) $selectedYear)
            ->when($durationFilter === 'waiting_logout', fn ($query) => $query->whereNull('logout_proof_path'))
            ->when($durationFilter === 'uploaded', fn ($query) => $query->whereNotNull('logout_proof_path'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('product_name_snapshot', 'like', "%{$search}%")
                        ->orWhere('credential_data', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('whatsapp', 'like', "%{$search}%");
                        })
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('credential_expires_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.durations.index', compact('durations', 'search', 'durationFilter', 'selectedMonth', 'selectedYear'));
    }

    public function exportExcel(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $durationFilter = (string) $request->query('duration_filter', 'all');
        $selectedMonth = (string) $request->query('month', now()->format('m'));
        $selectedYear = (string) $request->query('year', now()->format('Y'));
        $allowedDurationFilters = ['all', 'waiting_logout', 'uploaded'];

        if (! in_array($durationFilter, $allowedDurationFilters, true)) {
            $durationFilter = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $durations = Transaction::query()
            ->with(['user', 'product'])
            ->where('payment_status', 'paid')
            ->whereIn('order_status', ['processing', 'success'])
            ->whereNotNull('credential_expires_at')
            ->where('credential_expires_at', '<=', now())
            ->whereMonth('credential_expires_at', (int) $selectedMonth)
            ->whereYear('credential_expires_at', (int) $selectedYear)
            ->when($durationFilter === 'waiting_logout', fn ($query) => $query->whereNull('logout_proof_path'))
            ->when($durationFilter === 'uploaded', fn ($query) => $query->whereNotNull('logout_proof_path'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('product_name_snapshot', 'like', "%{$search}%")
                        ->orWhere('credential_data', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('whatsapp', 'like', "%{$search}%");
                        })
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('credential_expires_at')
            ->get();

        Carbon::setLocale('id');
        $monthName = Carbon::createFromDate((int) $selectedYear, (int) $selectedMonth, 1)->translatedFormat('F Y');
        $rows = $durations->map(function (Transaction $duration, int $index) {
            return [
                $index + 1,
                $duration->invoice_number ?? '-',
                $duration->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-',
                $duration->credential_expires_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-',
                $duration->user?->name ?? 'User',
                $duration->user?->email ?? '-',
                $duration->user?->whatsapp ?? '-',
                $duration->display_product_name,
                filled($duration->logout_proof_path) ? 'Sudah Upload' : 'Belum Logout',
                filled($duration->logout_proof_path) ? 'Ada' : '-',
            ];
        })->all();

        return SimpleXlsx::download(
            'Wiboost Store - Laporan Durasi',
            'Durasi',
            'laporan-durasi-' . str_replace(' ', '-', strtolower($monthName)) . '.xlsx',
            'Periode: ' . $monthName,
            ['No', 'Invoice', 'Waktu Beli', 'Waktu Habis', 'Nama', 'Email', 'Nomor Kontak', 'Produk', 'Status', 'Bukti'],
            $rows,
            [8, 24, 20, 20, 28, 34, 22, 58, 18, 14],
            [1]
        );
    }
}
