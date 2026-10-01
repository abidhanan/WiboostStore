<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendUserBroadcastEmail;
use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmailController extends Controller
{
    private const RECIPIENT_SCOPES = ['all', 'buyer', 'member', 'reseller', 'specific'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $campaigns = EmailCampaign::query()
            ->with('creator:id,name,email')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('subject', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%")
                        ->orWhere('recipient_scope', 'like', "%{$search}%")
                        ->orWhereHas('creator', function (Builder $creatorQuery) use ($search): void {
                            $creatorQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.emails.index', compact('campaigns', 'search'));
    }

    public function create(): View
    {
        $specificRecipients = $this->recipientQuery('all')
            ->select(['id', 'name', 'email'])
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->map(fn (User $user) => [
                'id' => (string) $user->id,
                'name' => $user->name ?: 'Tanpa Nama',
                'email' => $user->email,
            ])
            ->values();

        return view('admin.emails.create', [
            'submissionToken' => (string) Str::uuid(),
            'recipientCounts' => collect(self::RECIPIENT_SCOPES)
                ->mapWithKeys(fn (string $scope) => [$scope => $scope === 'specific' ? 0 : $this->recipientQuery($scope)->count()])
                ->all(),
            'specificRecipients' => $specificRecipients,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_scope' => ['required', Rule::in(self::RECIPIENT_SCOPES)],
            'recipient_ids' => ['nullable', 'array', 'required_if:recipient_scope,specific'],
            'recipient_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'submission_token' => ['required', 'uuid'],
            'subject' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:20000'],
            'action_label' => ['nullable', 'string', 'max:80', 'required_with:action_url'],
            'action_url' => ['nullable', 'url:http,https', 'max:500', 'required_with:action_label'],
        ], [
            'recipient_scope.required' => 'Pilih kelompok penerima email.',
            'recipient_ids.required_if' => 'Pilih minimal satu email penerima.',
            'recipient_ids.*.distinct' => 'Daftar penerima tidak boleh berisi email yang sama.',
            'recipient_ids.*.exists' => 'Salah satu penerima email sudah tidak tersedia.',
            'subject.required' => 'Subjek email wajib diisi.',
            'subject.max' => 'Subjek email maksimal 150 karakter.',
            'body.required' => 'Isi email wajib diisi.',
            'body.max' => 'Isi email maksimal 20.000 karakter.',
            'action_label.required_with' => 'Tulisan tombol wajib diisi jika tautan tombol digunakan.',
            'action_url.required_with' => 'Tautan tombol wajib diisi jika tulisan tombol digunakan.',
            'action_url.url' => 'Tautan tombol harus berupa URL http atau https yang valid.',
        ]);

        $recipientIds = array_values(array_unique(array_map('intval', $validated['recipient_ids'] ?? [])));
        $query = $this->recipientQuery($validated['recipient_scope'], $recipientIds);
        $recipientCount = (clone $query)->count();

        if ($recipientCount === 0) {
            return back()
                ->withInput()
                ->withErrors([
                    $validated['recipient_scope'] === 'specific' ? 'recipient_ids' : 'recipient_scope'
                        => 'Tidak ada pengguna dengan email aktif pada pilihan tersebut.',
                ]);
        }

        $submissionCacheKey = 'admin-email-submission:' . hash('sha256', $validated['submission_token']);

        if (! Cache::add($submissionCacheKey, true, now()->addDay())) {
            return redirect()
                ->route('admin.emails.index')
                ->with('success', 'Pengiriman email tersebut sudah diproses sebelumnya dan tidak dimasukkan ke antrean dua kali.');
        }

        $subject = trim($validated['subject']);
        $body = trim($validated['body']);
        $actionLabel = filled($validated['action_label'] ?? null) ? trim($validated['action_label']) : null;
        $actionUrl = filled($validated['action_url'] ?? null) ? trim($validated['action_url']) : null;

        $campaign = EmailCampaign::query()->create([
            'created_by' => $request->user()?->id,
            'recipient_scope' => $validated['recipient_scope'],
            'recipient_count' => $recipientCount,
            'subject' => $subject,
            'body' => $body,
            'action_label' => $actionLabel,
            'action_url' => $actionUrl,
            'status' => 'queued',
        ]);

        $queuedCount = 0;
        $failedCount = 0;

        $query
            ->select(['id', 'name', 'email'])
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($campaign, $subject, $body, $actionLabel, $actionUrl, &$queuedCount, &$failedCount): void {
                foreach ($users as $user) {
                    try {
                        SendUserBroadcastEmail::dispatch(
                            campaignId: $campaign->id,
                            recipientEmail: $user->email,
                            recipientName: $user->name ?: 'Pelanggan Wiboost',
                            subjectText: $subject,
                            bodyText: $body,
                            actionLabel: $actionLabel,
                            actionUrl: $actionUrl,
                        )->onQueue('emails');
                        $queuedCount++;
                    } catch (\Throwable $exception) {
                        report($exception);
                        $failedCount++;
                    }
                }
            });

        if ($failedCount > 0) {
            $campaign->update([
                'failed_count' => $failedCount,
                'status' => $queuedCount > 0 ? 'queued' : 'failed',
                'completed_at' => $queuedCount > 0 ? null : now(),
            ]);
        }

        if ($queuedCount === 0) {
            Cache::forget($submissionCacheKey);

            return back()
                ->withInput()
                ->withErrors(['email' => 'Email gagal dimasukkan ke antrean. Periksa konfigurasi email dan antrean server.']);
        }

        $message = "Email berhasil dimasukkan ke antrean untuk {$queuedCount} pengguna dan akan dikirim bertahap.";

        if ($failedCount > 0) {
            $message .= " {$failedCount} pengguna gagal dimasukkan ke antrean.";
        }

        return redirect()->route('admin.emails.index')->with('success', $message);
    }

    private function recipientQuery(string $scope, array $recipientIds = []): Builder
    {
        $customerRoleIds = array_values(array_unique([
            ...User::BUYER_ROLE_IDS,
            User::ROLE_RESELLER,
        ]));

        return User::query()
            ->whereIn('role_id', $customerRoleIds)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->when($scope === 'specific', function (Builder $query) use ($recipientIds): void {
                $query->whereIn('id', $recipientIds);
            })
            ->when($scope === 'buyer', function (Builder $query): void {
                $query->whereIn('role_id', User::BUYER_ROLE_IDS)
                    ->where(function (Builder $membershipQuery): void {
                        $membershipQuery->whereNull('membership_expires_at')
                            ->orWhere('membership_expires_at', '<=', now());
                    });
            })
            ->when($scope === 'member', function (Builder $query): void {
                $query->whereNotNull('membership_expires_at')
                    ->where('membership_expires_at', '>', now());
            })
            ->when($scope === 'reseller', function (Builder $query): void {
                $query->where('role_id', User::ROLE_RESELLER)
                    ->where(function (Builder $membershipQuery): void {
                        $membershipQuery->whereNull('membership_expires_at')
                            ->orWhere('membership_expires_at', '<=', now());
                    });
            });
    }
}
