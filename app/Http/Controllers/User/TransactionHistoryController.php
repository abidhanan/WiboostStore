<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\OrderFulfillmentService;
use App\Services\TransactionPaymentSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransactionHistoryController extends Controller
{
    public function index(TransactionPaymentSyncService $paymentSyncService, OrderFulfillmentService $orderFulfillmentService)
    {
        $pendingTransactions = Transaction::where('user_id', Auth::id())
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->get();

        foreach ($pendingTransactions as $transaction) {
            $paymentSyncService->expireIfPastDue($transaction);
        }

        $processingPremiumTransactions = Transaction::where('user_id', Auth::id())
            ->where('payment_status', 'paid')
            ->where('order_status', 'processing')
            ->whereNull('credential_data')
            ->with('product.category.parent.parent.parent', 'user')
            ->get();

        foreach ($processingPremiumTransactions as $transaction) {
            $orderFulfillmentService->ensurePremiumProcessingCredential($transaction);
        }

        $transactions = Transaction::where('user_id', Auth::id())
                                    ->with('refund', 'product.category.parent.parent.parent')
                                    ->orderBy('created_at', 'desc')
                                    ->paginate(10);

        return view('user.history', compact('transactions'));
    }

    public function storeLoginProof(Request $request, Transaction $transaction)
    {
        abort_unless((int) $transaction->user_id === (int) Auth::id(), 403);

        $credentials = is_array($transaction->credential_data)
            ? $transaction->credential_data
            : (json_decode((string) $transaction->credential_data, true) ?: []);

        abort_unless((bool) data_get($credentials, 'requires_login_proof'), 404);

        $request->validate([
            'login_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,heic,heif', 'max:5120'],
        ], [
            'login_proof.required' => 'Upload bukti login dulu ya.',
            'login_proof.uploaded' => 'Bukti login gagal diunggah. Pilih foto yang lebih kecil atau ambil screenshot baru.',
            'login_proof.mimes' => 'Bukti login harus berupa JPG, PNG, WebP, GIF, HEIC, atau HEIF.',
            'login_proof.max' => 'Bukti login maksimal 5 MB.',
        ]);

        if ($transaction->login_proof_path) {
            Storage::disk('public')->delete($transaction->login_proof_path);
        }

        $path = app(\App\Support\WebpImageOptimizer::class)
            ->storeUploadedFile($request->file('login_proof'), 'login-proofs');

        $transaction->forceFill([
            'login_proof_path' => $path,
            'login_proof_uploaded_at' => now(),
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'url' => url(Storage::url($path)),
                'uploaded_label' => 'Diunggah ' . $transaction->login_proof_uploaded_at
                    ->copy()
                    ->locale('id')
                    ->translatedFormat('d F Y, H:i'),
            ]);
        }

        return back()->with('success', 'Bukti login berhasil diupload.');
    }

    public function destroyLoginProof(Request $request, Transaction $transaction)
    {
        abort_unless((int) $transaction->user_id === (int) Auth::id(), 403);

        $credentials = is_array($transaction->credential_data)
            ? $transaction->credential_data
            : (json_decode((string) $transaction->credential_data, true) ?: []);

        abort_unless((bool) data_get($credentials, 'requires_login_proof'), 404);

        if ($transaction->login_proof_path) {
            Storage::disk('public')->delete($transaction->login_proof_path);
        }

        $transaction->forceFill([
            'login_proof_path' => null,
            'login_proof_uploaded_at' => null,
        ])->save();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return back()->with('success', 'Bukti login berhasil dihapus.');
    }

    public function storeLogoutProof(Request $request, Transaction $transaction)
    {
        abort_unless((int) $transaction->user_id === (int) Auth::id(), 403);
        abort_unless($transaction->requiresLogoutProof(), 404);
        abort_unless($transaction->credential_expired, 404);

        $request->validate([
            'logout_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,heic,heif', 'max:5120'],
        ], [
            'logout_proof.required' => 'Upload bukti logout dulu ya.',
            'logout_proof.uploaded' => 'Bukti logout gagal diunggah. Pilih foto yang lebih kecil atau ambil screenshot baru.',
            'logout_proof.mimes' => 'Bukti logout harus berupa JPG, PNG, WebP, GIF, HEIC, atau HEIF.',
            'logout_proof.max' => 'Bukti logout maksimal 5 MB.',
        ]);

        if ($transaction->logout_proof_path) {
            Storage::disk('public')->delete($transaction->logout_proof_path);
        }

        $path = app(\App\Support\WebpImageOptimizer::class)
            ->storeUploadedFile($request->file('logout_proof'), 'logout-proofs');

        $transaction->forceFill([
            'logout_proof_path' => $path,
            'logout_proof_uploaded_at' => now(),
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'url' => url(Storage::url($path)),
                'uploaded_label' => 'Diunggah ' . $transaction->logout_proof_uploaded_at
                    ->copy()
                    ->locale('id')
                    ->translatedFormat('d F Y, H:i'),
            ]);
        }

        return back()->with('success', 'Bukti logout berhasil diupload.');
    }

    public function destroyLogoutProof(Request $request, Transaction $transaction)
    {
        abort_unless((int) $transaction->user_id === (int) Auth::id(), 403);
        abort_unless($transaction->requiresLogoutProof(), 404);

        if ($transaction->logout_proof_path) {
            Storage::disk('public')->delete($transaction->logout_proof_path);
        }

        $transaction->forceFill([
            'logout_proof_path' => null,
            'logout_proof_uploaded_at' => null,
        ])->save();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return back()->with('success', 'Bukti logout berhasil dihapus.');
    }
}
