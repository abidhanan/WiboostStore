<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminReport;
use App\Support\WiboostAdminContact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $reportFilter = (string) $request->query('report_filter', 'all');
        $allowedReportFilters = ['all', 'new', 'read', 'closed'];

        if (! in_array($reportFilter, $allowedReportFilters, true)) {
            $reportFilter = 'all';
        }

        $reports = AdminReport::query()
            ->with('user')
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
                                ->orWhere('email', 'like', "%{$search}%");
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
            'adminCsWhatsapp' => WiboostAdminContact::csWhatsappNumber(),
            'adminOtpWhatsapp' => WiboostAdminContact::otpWhatsappNumber(),
        ]);
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
