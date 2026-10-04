<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\SimpleXlsx;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        [$search, $filter, $selectedMonth, $selectedYear] = $this->filters($request);

        $logs = $this->filteredQuery($search, $filter, $selectedMonth, $selectedYear)
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.logs.index', compact('logs', 'search', 'filter', 'selectedMonth', 'selectedYear'));
    }

    public function exportExcel(Request $request)
    {
        [$search, $filter, $selectedMonth, $selectedYear] = $this->filters($request);
        $logs = $this->filteredQuery($search, $filter, $selectedMonth, $selectedYear)
            ->orderByDesc('id')
            ->get();

        Carbon::setLocale('id');
        $period = Carbon::createFromDate((int) $selectedYear, (int) $selectedMonth, 1)->translatedFormat('F Y');
        $filterLabels = [
            'all' => 'Semua',
            'member' => 'Pengguna Masuk',
            'guest' => 'Tamu',
            'proxy' => 'Proxy/VPN',
            'hosting' => 'Hosting/Bot',
        ];

        $rows = $logs->values()->map(function (ActivityLog $log, int $index) {
            $geo = $log->geo;
            $ipStatuses = [];

            if ($geo?->mobile) {
                $ipStatuses[] = 'Mobile';
            }
            if ($geo?->proxy) {
                $ipStatuses[] = 'Proxy/VPN';
            }
            if ($geo?->hosting) {
                $ipStatuses[] = 'Hosting/Bot';
            }

            $location = implode(', ', array_filter([$geo?->city, $geo?->region_name, $geo?->country]));
            $network = implode(' | ', array_filter([$geo?->isp, $geo?->asname]));

            return [
                $index + 1,
                $log->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-',
                $log->user?->name ?? 'Tamu',
                $log->user?->email ?? '-',
                $log->ip ?: '-',
                $geo ? ($ipStatuses !== [] ? implode(', ', $ipStatuses) : 'Normal') : '-',
                strtoupper((string) ($log->method ?: '-')),
                '/'.ltrim((string) $log->url, '/'),
                $log->route_name ?: '-',
                $location !== '' ? $location : '-',
                $network !== '' ? $network : '-',
            ];
        })->all();

        $subtitle = 'Periode: '.$period.' | Filter: '.($filterLabels[$filter] ?? 'Semua');
        if ($search !== '') {
            $subtitle .= ' | Pencarian: '.$search;
        }

        return SimpleXlsx::download(
            'Wiboost Store - Laporan Log Aktivitas',
            'Log Aktivitas',
            'laporan-log-aktivitas-'.$selectedYear.'-'.$selectedMonth.'.xlsx',
            $subtitle,
            ['No', 'Waktu', 'Nama', 'Email', 'IP', 'Status IP', 'Metode', 'URL', 'Route', 'Lokasi', 'Jaringan'],
            $rows,
            [8, 20, 28, 34, 28, 22, 14, 56, 30, 40, 44],
            [1]
        );
    }

    /** @return array{string, string, string, string} */
    private function filters(Request $request): array
    {
        $search = trim((string) $request->query('search', ''));
        $filter = (string) $request->query('filter', 'all');
        $allowedFilters = ['all', 'guest', 'member', 'proxy', 'hosting'];
        $monthNumber = (int) $request->query('month', now()->month);
        $yearNumber = (int) $request->query('year', now()->year);

        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $monthNumber = $monthNumber >= 1 && $monthNumber <= 12 ? $monthNumber : now()->month;
        $yearNumber = $yearNumber >= 2000 && $yearNumber <= now()->year + 1 ? $yearNumber : now()->year;

        return [$search, $filter, sprintf('%02d', $monthNumber), (string) $yearNumber];
    }

    private function filteredQuery(string $search, string $filter, string $month, string $year): Builder
    {
        return ActivityLog::query()
            ->with(['user', 'geo'])
            ->whereMonth('created_at', (int) $month)
            ->whereYear('created_at', (int) $year)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('ip', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('route_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('whatsapp', 'like', "%{$search}%");
                        })
                        ->orWhereHas('geo', function (Builder $geoQuery) use ($search): void {
                            $geoQuery->where('country', 'like', "%{$search}%")
                                ->orWhere('region_name', 'like', "%{$search}%")
                                ->orWhere('city', 'like', "%{$search}%")
                                ->orWhere('isp', 'like', "%{$search}%")
                                ->orWhere('asname', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filter === 'guest', fn (Builder $query) => $query->whereNull('user_id'))
            ->when($filter === 'member', fn (Builder $query) => $query->whereNotNull('user_id'))
            ->when($filter === 'proxy', fn (Builder $query) => $query->whereHas('geo', fn (Builder $geoQuery) => $geoQuery->where('proxy', true)))
            ->when($filter === 'hosting', fn (Builder $query) => $query->whereHas('geo', fn (Builder $geoQuery) => $geoQuery->where('hosting', true)));
    }
}
