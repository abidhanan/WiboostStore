<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminReport;
use App\Support\SimpleXlsx;
use App\Support\WiboostAdminContact;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $reportFilter = (string) $request->query('report_filter', 'all');
        $selectedMonth = $request->query('month', now()->format('m'));
        $selectedYear = $request->query('year', now()->format('Y'));
        $allowedReportFilters = ['all', 'new', 'read', 'closed'];

        if (! in_array($reportFilter, $allowedReportFilters, true)) {
            $reportFilter = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', (string) $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', (string) $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $reports = AdminReport::query()
            ->with('user')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->when($reportFilter !== 'all', fn ($query) => $query->where('status', $reportFilter))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('category_label', 'like', "%{$search}%")
                        ->orWhere('issue', 'like', "%{$search}%")
                        ->orWhere('related_invoice', 'like', "%{$search}%")
                        ->orWhere('related_title', 'like', "%{$search}%")
                        ->orWhere('proof_original_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('whatsapp', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'search' => $search,
            'reportFilter' => $reportFilter,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'adminCsWhatsapp' => WiboostAdminContact::csWhatsappNumber(),
            'adminOtpWhatsapp' => WiboostAdminContact::otpWhatsappNumber(),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $reportFilter = (string) $request->query('report_filter', 'all');
        $selectedMonth = (string) $request->query('month', now()->format('m'));
        $selectedYear = (string) $request->query('year', now()->format('Y'));
        $allowedReportFilters = ['all', 'new', 'read', 'closed'];

        if (! in_array($reportFilter, $allowedReportFilters, true)) {
            $reportFilter = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $reports = AdminReport::query()
            ->with('user')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->when($reportFilter !== 'all', fn ($query) => $query->where('status', $reportFilter))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('category_label', 'like', "%{$search}%")
                        ->orWhere('issue', 'like', "%{$search}%")
                        ->orWhere('related_invoice', 'like', "%{$search}%")
                        ->orWhere('related_title', 'like', "%{$search}%")
                        ->orWhere('proof_original_name', 'like', "%{$search}%")
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
        $rows = $reports->map(function (AdminReport $report, int $index) {
            return [
                $index + 1,
                $report->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-',
                $report->related_invoice ?: 'LAP-' . str_pad((string) $report->id, 5, '0', STR_PAD_LEFT),
                $report->user?->name ?? '-',
                $report->user?->email ?? '-',
                $report->user?->whatsapp ?? '-',
                $report->category_label ?: '-',
                $report->issue ?: '-',
                $report->proof_original_name ?: (filled($report->proof_path) ? 'Ada' : '-'),
                $report->status_label,
            ];
        })->all();

        return SimpleXlsx::download(
            'Wiboost Store - Laporan Kendala',
            'Laporan Kendala',
            'laporan-kendala-' . str_replace(' ', '-', strtolower($monthName)) . '.xlsx',
            'Periode: ' . $monthName,
            ['No', 'Waktu', 'Invoice', 'Nama', 'Email', 'Nomor Kontak', 'Jenis', 'Kendala', 'Bukti', 'Status'],
            $rows,
            [8, 20, 24, 28, 34, 22, 24, 60, 34, 18],
            [1]
        );
    }

    public function update(Request $request, AdminReport $report)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['new', 'read', 'closed'])],
        ]);

        $report->update($validated);

        return back()->with('success', 'Status laporan berhasil diperbarui.');
    }
}
