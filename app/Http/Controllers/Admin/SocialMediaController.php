<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialMediaStat;
use App\Support\SimpleXlsx;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SocialMediaController extends Controller
{
    private const PLATFORMS = ['instagram', 'tiktok'];

    public function index(Request $request): View
    {
        $platform = strtolower(trim((string) $request->query('platform', 'all')));
        $search = trim((string) $request->query('search', ''));
        $selectedYear = $this->normalizeYear($request->query('year'));

        if ($platform !== 'all' && ! in_array($platform, self::PLATFORMS, true)) {
            $platform = 'all';
        }

        $matchingMonths = collect($this->monthLabels())
            ->filter(fn (string $label) => $search !== '' && str_contains(strtolower($label), strtolower($search)))
            ->keys()
            ->map(fn ($month) => (int) $month)
            ->all();

        $stats = SocialMediaStat::query()
            ->with('editor:id,name')
            ->when($platform !== 'all', fn (Builder $query) => $query->where('platform', $platform))
            ->where('year', $selectedYear)
            ->when($search !== '', function (Builder $query) use ($search, $matchingMonths): void {
                $query->where(function (Builder $searchQuery) use ($search, $matchingMonths): void {
                    $searchQuery
                        ->where('platform', 'like', "%{$search}%")
                        ->orWhere('year', 'like', "%{$search}%")
                        ->orWhere('top_post_url', 'like', "%{$search}%")
                        ->orWhere('low_post_url', 'like', "%{$search}%");

                    if ($matchingMonths !== []) {
                        $searchQuery->orWhereIn('month', $matchingMonths);
                    }
                });
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByRaw("CASE platform WHEN 'instagram' THEN 1 WHEN 'tiktok' THEN 2 ELSE 3 END")
            ->paginate(20)
            ->withQueryString();

        return view('admin.social-media.index', [
            'stats' => $stats,
            'platform' => $platform,
            'search' => $search,
            'selectedYear' => $selectedYear,
            'availableYears' => $this->availableYears(),
            'monthLabels' => $this->monthLabels(),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $platform = strtolower(trim((string) $request->query('platform', 'all')));
        $search = trim((string) $request->query('search', ''));
        $selectedYear = $this->normalizeYear($request->query('year'));

        if ($platform !== 'all' && ! in_array($platform, self::PLATFORMS, true)) {
            $platform = 'all';
        }

        $matchingMonths = collect($this->monthLabels())
            ->filter(fn (string $label) => $search !== '' && str_contains(strtolower($label), strtolower($search)))
            ->keys()
            ->map(fn ($month) => (int) $month)
            ->all();

        $stats = SocialMediaStat::query()
            ->when($platform !== 'all', fn (Builder $query) => $query->where('platform', $platform))
            ->where('year', $selectedYear)
            ->when($search !== '', function (Builder $query) use ($search, $matchingMonths): void {
                $query->where(function (Builder $searchQuery) use ($search, $matchingMonths): void {
                    $searchQuery
                        ->where('platform', 'like', "%{$search}%")
                        ->orWhere('year', 'like', "%{$search}%")
                        ->orWhere('top_post_url', 'like', "%{$search}%")
                        ->orWhere('low_post_url', 'like', "%{$search}%");

                    if ($matchingMonths !== []) {
                        $searchQuery->orWhereIn('month', $matchingMonths);
                    }
                });
            })
            ->orderByDesc('month')
            ->orderByRaw("CASE platform WHEN 'instagram' THEN 1 WHEN 'tiktok' THEN 2 ELSE 3 END")
            ->get();

        $platformLabels = ['instagram' => 'Instagram', 'tiktok' => 'TikTok'];
        $monthLabels = $this->monthLabels();
        $rows = $stats->map(fn (SocialMediaStat $stat, int $index) => [
            $index + 1,
            $platformLabels[$stat->platform] ?? ucfirst($stat->platform),
            ($monthLabels[$stat->month] ?? '-') . ' ' . $stat->year,
            $stat->total_posts,
            $stat->total_followers,
            $stat->reach,
            $stat->profile_visits,
            $stat->bio_link_clicks,
            $stat->top_post_url ?? '-',
            $stat->low_post_url ?? '-',
        ])->all();

        $platformLabel = $platform === 'all' ? 'Semua Kategori' : ($platformLabels[$platform] ?? ucfirst($platform));

        return SimpleXlsx::download(
            'Wiboost Store - Laporan Media Sosial',
            'Media Sosial',
            'laporan-media-sosial-' . $selectedYear . '-' . $platform . '.xlsx',
            'Tahun: ' . $selectedYear . ' | Kategori: ' . $platformLabel,
            ['No', 'Kategori', 'Waktu', 'Total Postingan', 'Total Followers', 'Jangkauan', 'Kunjungan Profil', 'Klik Link Bio', 'Link Postingan Teramai', 'Link Postingan Tersepi'],
            $rows,
            [8, 18, 18, 18, 18, 18, 20, 18, 48, 48],
            [1]
        );
    }

    public function create(): View
    {
        $requestedPlatform = strtolower((string) request('platform', 'instagram'));
        $requestedPlatform = in_array($requestedPlatform, self::PLATFORMS, true)
            ? $requestedPlatform
            : 'instagram';

        return view('admin.social-media.create', [
            'socialMediaStat' => new SocialMediaStat([
                'platform' => $requestedPlatform,
                'month' => now()->month,
                'year' => now()->year,
            ]),
            'monthLabels' => $this->monthLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules($request), $this->messages(), $this->attributes());
        $validated = $this->normalizePayload($validated, $request);

        SocialMediaStat::query()->create($validated);

        return $this
            ->redirectToReturnTo($request, 'admin.social-media.index', ['platform' => $validated['platform']])
            ->with('success', 'Data media sosial berhasil ditambahkan.');
    }

    public function edit(SocialMediaStat $socialMediaStat): View
    {
        return view('admin.social-media.edit', [
            'socialMediaStat' => $socialMediaStat,
            'monthLabels' => $this->monthLabels(),
        ]);
    }

    public function update(Request $request, SocialMediaStat $socialMediaStat): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules($request, $socialMediaStat),
            $this->messages(),
            $this->attributes(),
        );
        $validated = $this->normalizePayload($validated, $request);

        $socialMediaStat->update($validated);

        return $this
            ->redirectToReturnTo($request, 'admin.social-media.index', ['platform' => $validated['platform']])
            ->with('success', 'Data media sosial berhasil diperbarui.');
    }

    public function destroy(Request $request, SocialMediaStat $socialMediaStat): RedirectResponse
    {
        $socialMediaStat->delete();

        return $this
            ->redirectToReturnTo($request, 'admin.social-media.index')
            ->with('success', 'Data media sosial berhasil dihapus.');
    }

    private function rules(Request $request, ?SocialMediaStat $socialMediaStat = null): array
    {
        return [
            'platform' => [
                'required',
                Rule::in(self::PLATFORMS),
                Rule::unique('social_media_stats', 'platform')
                    ->where(fn ($query) => $query
                        ->where('month', $request->integer('month'))
                        ->where('year', $request->integer('year')))
                    ->ignore($socialMediaStat?->id),
            ],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2020,2100'],
            'total_posts' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'total_followers' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'reach' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'profile_visits' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'bio_link_clicks' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'top_post_url' => ['nullable', 'url:http,https', 'max:500'],
            'low_post_url' => ['nullable', 'url:http,https', 'max:500'],
        ];
    }

    private function normalizePayload(array $validated, Request $request): array
    {
        $validated['top_post_url'] = filled($validated['top_post_url'] ?? null)
            ? trim($validated['top_post_url'])
            : null;
        $validated['low_post_url'] = filled($validated['low_post_url'] ?? null)
            ? trim($validated['low_post_url'])
            : null;
        $validated['updated_by'] = $request->user()?->id;

        return $validated;
    }

    private function messages(): array
    {
        return [
            'platform.required' => 'Pilih kategori media sosial.',
            'platform.in' => 'Kategori media sosial tidak valid.',
            'platform.unique' => 'Data untuk kategori, bulan, dan tahun tersebut sudah tersedia.',
            'month.between' => 'Bulan yang dipilih tidak valid.',
            'year.between' => 'Tahun harus berada di antara 2020 sampai 2100.',
            'top_post_url.url' => 'Link postingan teramai harus berupa URL http atau https yang valid.',
            'low_post_url.url' => 'Link postingan tersepi harus berupa URL http atau https yang valid.',
        ];
    }

    private function attributes(): array
    {
        return [
            'month' => 'bulan',
            'year' => 'tahun',
            'total_posts' => 'total postingan',
            'total_followers' => 'total followers',
            'reach' => 'jangkauan',
            'profile_visits' => 'kunjungan profil',
            'bio_link_clicks' => 'klik link bio',
        ];
    }

    private function monthLabels(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    private function normalizeYear(mixed $year): int
    {
        $year = (string) ($year ?? now()->year);

        return preg_match('/^\d{4}$/', $year) && (int) $year >= 2020 && (int) $year <= 2100
            ? (int) $year
            : now()->year;
    }

    private function availableYears(): array
    {
        $storedYears = SocialMediaStat::query()
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return $storedYears
            ->merge(range(now()->year + 1, now()->year - 10))
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }
}
