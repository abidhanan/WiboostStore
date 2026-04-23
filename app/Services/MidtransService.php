<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = (string) config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function getTransactionSnapToken(Transaction $transaction, ?User $user = null): string
    {
        $user ??= $transaction->user;

        return Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $transaction->invoice_number,
                'gross_amount' => (int) round((float) $transaction->amount),
            ],
            'customer_details' => [
                'first_name' => $user?->name ?? 'Pelanggan Wiboost',
                'email' => $user?->email,
            ],
            'item_details' => [
                [
                    'id' => (string) $transaction->product_id,
                    'price' => (int) round((float) $transaction->amount),
                    'quantity' => 1,
                    'name' => $transaction->product->name,
                ],
            ],
            'callbacks' => [
                'finish' => $this->publicUrl('/user/history?source=midtrans'),
                'pending' => $this->publicUrl('/user/history?source=midtrans'),
                'error' => $this->publicUrl('/user/history?source=midtrans'),
            ],
        ]);
    }

    public function getDepositSnapToken(Deposit $deposit, ?User $user = null): string
    {
        $user ??= $deposit->user;

        return Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $deposit->invoice_number,
                'gross_amount' => (int) round((float) $deposit->amount),
            ],
            'item_details' => [
                [
                    'id' => 'TOPUP-WIBOOST',
                    'price' => (int) round((float) $deposit->amount),
                    'quantity' => 1,
                    'name' => 'Top Up Saldo Wiboost',
                ],
            ],
            'customer_details' => [
                'first_name' => $user?->name ?? 'Pelanggan Wiboost',
                'email' => $user?->email,
            ],
            'callbacks' => [
                'finish' => $this->publicUrl('/user/wallet?source=midtrans'),
                'pending' => $this->publicUrl('/user/wallet?source=midtrans'),
                'error' => $this->publicUrl('/user/wallet?source=midtrans'),
            ],
        ]);
    }

    protected function publicUrl(string $path): string
    {
        $baseUrl = rtrim((string) config('wiboost.public_url', config('app.url')), '/');

        return $baseUrl . '/' . ltrim($path, '/');
    }
}
