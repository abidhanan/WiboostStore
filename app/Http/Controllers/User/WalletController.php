<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\WalletHistory;
use App\Services\TripayDepositSyncService;
use App\Services\XoftwarePaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
            $deposit->delete();

            return redirect()
                ->route('user.wallet.index');
        }

        return redirect()->route('user.wallet.index');
    }

    public function exchangePoints()
    {
        $user = Auth::user();

        if ($user->points < 5) {
            return back()->with('error', 'Poin kamu belum cukup. Kumpulkan minimal 5 poin.');
        }

        $user->decrement('points', 5);
        $user->increment('balance', 1000);

        WalletHistory::create([
            'user_id' => $user->id,
            'type' => 'topup',
            'amount' => 1000,
            'description' => 'Bonus penukaran 5 poin loyalty',
            'invoice_number' => 'POIN-' . strtoupper(Str::random(8)),
        ]);

        return back()->with('success', 'Selamat, 5 poin berhasil ditukar menjadi saldo Rp 1.000.');
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

        $payment = $paymentService->createDepositPayment($deposit, Auth::user());

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
