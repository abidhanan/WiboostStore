<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OtpAdmin;
use App\Models\OtpRequest;
use App\Models\SiteSetting;
use App\Services\DiscordWebhookService;
use App\Support\SimpleXlsx;
use App\Support\WiboostAdminContact;
use App\Support\WiboostInputRules;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    public function index(Request $request)
    {
        [$search, $month, $year] = $this->filters($request);

        $requests = Schema::hasTable('otp_requests')
            ? $this->filteredRequestQuery($search, $month, $year)
                ->latest('requested_at')
                ->paginate(20)
                ->withQueryString()
            : new LengthAwarePaginator([], 0, 20, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

        $savedAdmins = Schema::hasTable('otp_admins')
            ? OtpAdmin::query()
                ->orderBy('name')
                ->get(['id', 'name', 'whatsapp'])
                ->map(fn (OtpAdmin $admin) => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'whatsapp' => $admin->whatsapp,
                ])->values()->all()
            : collect(WiboostAdminContact::otpAdmins())
                ->map(fn (array $admin) => [
                    'id' => null,
                    'name' => $admin['name'] ?? '',
                    'whatsapp' => $admin['number'] ?? '',
                ])->values()->all();

        $formAdmins = old('admins', $savedAdmins);
        if (! is_array($formAdmins) || $formAdmins === []) {
            $formAdmins = [['id' => null, 'name' => '', 'whatsapp' => '']];
        }

        return view('admin.otp.index', [
            'requests' => $requests,
            'savedOtpAdmins' => $savedAdmins,
            'formOtpAdmins' => array_values($formAdmins),
            'search' => $search,
            'selectedMonth' => $month,
            'selectedYear' => $year,
        ]);
    }

    public function saveAdmins(Request $request)
    {
        if (! Schema::hasTable('otp_admins')) {
            return back();
        }

        $validated = $request->validate([
            'admins' => ['nullable', 'array'],
            'admins.*.name' => WiboostInputRules::nameRules(80),
            'admins.*.whatsapp' => WiboostInputRules::numberRules(40),
        ], WiboostInputRules::validationMessages(), [
            'admins.*.name' => 'nama Admin OTP',
            'admins.*.whatsapp' => 'nomor Admin OTP',
        ]);

        $admins = collect($validated['admins'] ?? [])
            ->map(fn (array $admin) => [
                'name' => trim($admin['name']),
                'whatsapp' => WiboostAdminContact::normalizePhone($admin['whatsapp']),
            ]);

        if ($admins->pluck('whatsapp')->filter()->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'admins' => 'Nomor Admin OTP tidak boleh sama.',
            ]);
        }

        DB::transaction(function () use ($admins): void {
            $numbers = $admins->pluck('whatsapp')->filter()->values();

            OtpAdmin::query()
                ->when($numbers->isNotEmpty(), fn (Builder $query) => $query->whereNotIn('whatsapp', $numbers))
                ->delete();

            foreach ($admins as $admin) {
                OtpAdmin::query()->updateOrCreate(
                    ['whatsapp' => $admin['whatsapp']],
                    ['name' => $admin['name'], 'status' => 'active']
                );
            }
        });

        $this->syncLegacySettings();

        return back()->with('success', 'Nomor Admin OTP berhasil disimpan.');
    }

    public function updateRequest(Request $request, OtpRequest $otpRequest, DiscordWebhookService $discordWebhookService)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'handled'])],
        ]);

        $wasHandled = $otpRequest->status === 'handled';

        $otpRequest->update($validated);

        if (! $wasHandled && $otpRequest->status === 'handled') {
            $discordWebhookService->sendOtpHandledAlert(
                $otpRequest->fresh(['user', 'transaction']),
                $request->user()
            );
        }

        return back()->with('success', 'Status permintaan OTP berhasil diperbarui.');
    }

    public function exportExcel(Request $request)
    {
        if (! Schema::hasTable('otp_requests')) {
            return redirect()->route('admin.otp.index');
        }

        [$search, $month, $year] = $this->filters($request);
        $requests = $this->filteredRequestQuery($search, $month, $year)
            ->latest('requested_at')
            ->get();
        Carbon::setLocale('id');
        $period = Carbon::createFromDate((int) $year, (int) $month, 1)->translatedFormat('F Y');

        $rows = $requests->map(fn (OtpRequest $otpRequest, int $index) => [
            $index + 1,
            $otpRequest->requested_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-',
            $otpRequest->invoice_number,
            $otpRequest->user?->name ?? '-',
            $otpRequest->user?->email ?? '-',
            $otpRequest->user?->whatsapp ?? '-',
            $otpRequest->product_name ?: '-',
            $otpRequest->otp_admin_name ?: '-',
            $otpRequest->otp_admin_whatsapp ?: '-',
            $otpRequest->status_label,
        ])->all();

        return SimpleXlsx::download(
            'Wiboost Store - Permintaan OTP',
            'Permintaan OTP',
            'permintaan-otp-' . $year . '-' . $month . '.xlsx',
            'Periode: ' . $period,
            ['No', 'Waktu', 'Invoice', 'Nama', 'Email', 'Nomor Kontak', 'Produk', 'Nama Admin OTP', 'Nomor Admin OTP', 'Status'],
            $rows,
            [8, 22, 24, 28, 34, 22, 38, 26, 24, 16],
            [1]
        );
    }

    private function filteredRequestQuery(string $search, string $month, string $year): Builder
    {
        return OtpRequest::query()
            ->with(['user', 'transaction.product.category.parent.parent.parent'])
            ->whereMonth('requested_at', $month)
            ->whereYear('requested_at', $year)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('product_name', 'like', "%{$search}%")
                        ->orWhere('otp_admin_name', 'like', "%{$search}%")
                        ->orWhere('otp_admin_whatsapp', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('whatsapp', 'like', "%{$search}%");
                        });
                });
            });
    }

    private function filters(Request $request): array
    {
        $search = trim((string) $request->query('search', ''));
        $month = (string) $request->query('month', now()->format('m'));
        $year = (string) $request->query('year', now()->format('Y'));

        if (! preg_match('/^(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', $year)) {
            $year = now()->format('Y');
        }

        return [$search, $month, $year];
    }

    private function syncLegacySettings(): void
    {
        $admins = OtpAdmin::query()
            ->orderBy('name')
            ->get(['name', 'whatsapp'])
            ->map(fn (OtpAdmin $admin) => ['name' => $admin->name, 'number' => $admin->whatsapp])
            ->values()
            ->all();
        $numbers = collect($admins)->pluck('number')->all();

        SiteSetting::setValue('admin_otp_admins', json_encode($admins, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        SiteSetting::setValue('admin_otp_whatsapps', json_encode($numbers, JSON_UNESCAPED_SLASHES));
        SiteSetting::setValue('admin_otp_whatsapp', $numbers[0] ?? null);
    }
}
