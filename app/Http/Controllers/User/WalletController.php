<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\TripayDepositSyncService;
use App\Services\XoftwarePaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function index(TripayDepositSyncService $depositSyncService)
    {
        $depositSyncService->syncForUser((int) Auth::id());

        $deposits = Deposit::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->whereNull('payment_status')
                    ->orWhereNotIn('payment_status', ['unpaid', 'pending']);
            })
            ->latest()
            ->get();

        return view('user.wallet.index', compact('deposits'));
    }

    public function points()
    {
        $user = $this->supportsPointRewardTracking()
            ? $this->syncPointRewardPeriod(Auth::user())
            : Auth::user();
        $rewards = $this->pointRewards();
        $currentRewardIndex = $this->currentRewardIndex(
            $this->supportsPointRewardTracking() ? (int) ($user->point_reward_stage ?? 0) : 0,
            $rewards
        );
        $currentPoints = (int) ($user->points ?? 0);

        return view('user.points.index', compact('rewards', 'currentRewardIndex', 'currentPoints'));
    }

    public function store(Request $request, XoftwarePaymentService $paymentService)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        try {
            app(TripayDepositSyncService::class)->syncForUser((int) Auth::id());

            $amount = (float) $request->amount;
            $lockKey = 'wallet-topup:' . sha1(Auth::id() . '|' . $amount);

            if (! Cache::add($lockKey, true, now()->addSeconds(20))) {
                usleep(500000);
                $deposit = $this->findReusableDeposit($amount);

                if ($deposit && filled($deposit->payment_url)) {
                    $paymentUrl = $deposit->payment_url;

                    return redirect()->away((string) $paymentUrl);
                }

                    return back()->with('error', 'Tagihan QRIS sedang disiapkan. Tunggu sebentar lalu cek ulang halaman dompet.');
            }

            $deposit = $this->findReusableDeposit($amount) ?: Deposit::create([
                'user_id' => Auth::id(),
                'invoice_number' => 'DEP-' . strtoupper(Str::random(10)),
                'amount' => $amount,
                'payment_status' => 'unpaid',
                'payment_method' => 'tripay',
            ]);

            $paymentUrl = $this->resolveDepositPaymentUrl($deposit->fresh('user'), $paymentService);

            return redirect()->away((string) $paymentUrl);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal terhubung ke server pembayaran: ' . $e->getMessage());
        }
    }

    public function pay($invoice_number, XoftwarePaymentService $paymentService)
    {
        $deposit = Deposit::where('invoice_number', $invoice_number)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (! in_array($deposit->payment_status, ['unpaid', 'pending'], true)) {
            return redirect()->route('user.wallet.index')->with('error', 'Tagihan ini sudah lunas atau dibatalkan.');
        }

        try {
            $paymentUrl = $this->resolveDepositPaymentUrl($deposit->fresh('user'), $paymentService);

            return redirect()->away((string) $paymentUrl);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memuat ulang pembayaran: ' . $e->getMessage());
        }
    }

    public function cancelTripayDeposit(Deposit $deposit)
    {
        abort_if((int) $deposit->user_id !== (int) Auth::id(), 403);

        if (in_array($deposit->payment_status, ['unpaid', 'pending'], true)) {
            $deposit->update([
                'payment_status' => 'failed',
            ]);

            return redirect()
                ->route('user.wallet.index');
        }

        return redirect()->route('user.wallet.index');
    }

    public function exchangePoints(Request $request)
    {
        $validated = $request->validate([
            'reward_index' => ['required', 'integer', 'min:0'],
        ]);

        $rewards = $this->pointRewards();
        $requestedIndex = (int) $validated['reward_index'];

        if (! isset($rewards[$requestedIndex])) {
            return back()->with('error', 'Pilihan penukaran poin tidak tersedia.');
        }

        $message = DB::transaction(function () use ($requestedIndex, $rewards) {
            $user = User::query()
                ->lockForUpdate()
                ->findOrFail(Auth::id());

            $supportsPointRewardTracking = $this->supportsPointRewardTracking();
            $user = $supportsPointRewardTracking ? $this->syncPointRewardPeriod($user) : $user;
            $currentRewardIndex = $this->currentRewardIndex(
                $supportsPointRewardTracking ? (int) ($user->point_reward_stage ?? 0) : 0,
                $rewards
            );

            if ($requestedIndex !== $currentRewardIndex) {
                return 'Tahap penukaran ini belum terbuka.';
            }

            $reward = $rewards[$currentRewardIndex];

            if ((int) $user->points < (int) $reward['points']) {
                return 'Poin kamu belum cukup untuk klaim reward ini.';
            }

            $nextStage = min($currentRewardIndex + 1, count($rewards) - 1);

            $updates = [
                'points' => 0,
                'balance' => (float) $user->balance + (float) $reward['amount'],
            ];

            if ($supportsPointRewardTracking) {
                $updates['point_reward_stage'] = $nextStage;
            }

            $user->forceFill($updates)->save();

            WalletHistory::create([
                'user_id' => $user->id,
                'type' => 'topup',
                'amount' => $reward['amount'],
                'description' => 'Klaim penukaran ' . $reward['points'] . ' poin Wiboost',
                'invoice_number' => 'POIN-' . strtoupper(Str::random(8)),
            ]);

            return 'Selamat, ' . $reward['points'] . ' poin berhasil ditukar menjadi saldo Rp ' . number_format($reward['amount'], 0, ',', '.') . '.';
        });

        $isSuccess = str_starts_with($message, 'Selamat');

        return back()->with($isSuccess ? 'success' : 'error', $message);
    }

    protected function pointRewards(): array
    {
        return [
            ['points' => 3, 'amount' => 1000, 'label' => 'Saldo Rp 1.000'],
            ['points' => 5, 'amount' => 3000, 'label' => 'Saldo Rp 3.000'],
            ['points' => 8, 'amount' => 6000, 'label' => 'Saldo Rp 6.000'],
        ];
    }

    protected function currentRewardIndex(int $stage, array $rewards): int
    {
        return max(0, min($stage, max(0, count($rewards) - 1)));
    }

    protected function syncPointRewardPeriod(User $user): User
    {
        if (! $this->supportsPointRewardTracking()) {
            return $user;
        }

        $currentPeriod = now()->format('Y-m');

        if ((string) ($user->point_reward_period ?? '') !== $currentPeriod) {
            $user->forceFill([
                'point_reward_stage' => 0,
                'point_reward_period' => $currentPeriod,
            ])->save();
        }

        return $user;
    }

    protected function supportsPointRewardTracking(): bool
    {
        static $supports = null;

        if ($supports !== null) {
            return $supports;
        }

        return $supports = Schema::hasColumn('users', 'point_reward_stage')
            && Schema::hasColumn('users', 'point_reward_period');
    }

    protected function findReusableDeposit(float $amount): ?Deposit
    {
        $expiresInMinutes = max(1, (int) config('xoftware.expired_minutes', 60));

        return Deposit::where('user_id', Auth::id())
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->where('payment_method', 'tripay')
            ->where('amount', $amount)
            ->where('created_at', '>=', now()->subMinutes($expiresInMinutes))
            ->latest()
            ->first();
    }

    protected function resolveDepositPaymentUrl(Deposit $deposit, XoftwarePaymentService $paymentService): string
    {
        if (filled($deposit->payment_url)) {
            return (string) $deposit->payment_url;
        }

        try {
            $payment = $paymentService->createDepositPayment($deposit, Auth::user());
        } catch (\Throwable $exception) {
            $deposit->update([
                'payment_status' => 'failed',
                'payment_response' => array_merge($deposit->payment_response ?? [], [
                    'payment_gateway' => 'xoftware',
                    'payment_create_failed_at' => now()->toDateTimeString(),
                    'payment_create_error' => $exception->getMessage(),
                ]),
            ]);

            throw $exception;
        }

        $deposit->update([
            'payment_url' => $payment['checkout_url'],
            'tripay_reference' => $payment['reference'] ?? null,
            'payment_response' => array_merge($payment['raw'] ?? [], [
                'payment_gateway' => 'xoftware',
                'xoftware_reference' => $payment['reference'] ?? null,
                'tripay_reference' => $payment['reference'] ?? null,
            ]),
        ]);

        return $payment['checkout_url'];
    }

}
