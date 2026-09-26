<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\DiscordWebhookService;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentCallbackController extends Controller
{
    public function handleTripayCallback(
        Request $request,
        OrderFulfillmentService $orderFulfillmentService,
        DiscordWebhookService $discordWebhookService
    ) {
        $rawPayload = $request->getContent();
        $callbackSignature = (string) $request->header('X-Callback-Signature', '');
        $expectedSignature = hash_hmac('sha256', $rawPayload, (string) config('tripay.private_key'));

        if (! hash_equals(strtolower($expectedSignature), strtolower(trim($callbackSignature)))) {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
        }

        if ((string) $request->header('X-Callback-Event') !== 'payment_status') {
            return response()->json(['success' => false, 'message' => 'Unrecognized callback event'], 400);
        }

        $payload = json_decode($rawPayload, true);

        if (! is_array($payload)) {
            return response()->json(['success' => false, 'message' => 'Invalid payload'], 400);
        }

        $merchantRef = (string) data_get($payload, 'merchant_ref', '');
        $status = strtoupper((string) data_get($payload, 'status', ''));
        $paidAmount = (int) round((float) data_get($payload, 'total_amount', data_get($payload, 'amount', 0)));
        $paymentMethod = (string) (
            data_get($payload, 'payment_method')
            ?: data_get($payload, 'payment_method_code')
            ?: 'tripay'
        );

        if ($this->shouldHandleDepositReference($merchantRef)) {
            $result = DB::transaction(function () use ($merchantRef, $status, $paidAmount, $paymentMethod, $payload) {
                $deposit = Deposit::where('invoice_number', $merchantRef)
                    ->lockForUpdate()
                    ->first();

                if (! $deposit) {
                    return ['found' => false];
                }

                if ($status === 'PAID' && in_array($deposit->payment_status, [null, 'unpaid', 'pending'], true)) {
                    $topupAmount = (float) $deposit->amount;
                    $actualPaidAmount = $paidAmount > 0 ? $paidAmount : $topupAmount;

                    $deposit->update([
                        'payment_status' => 'paid',
                        'payment_method' => $paymentMethod,
                        'paid_amount' => $actualPaidAmount,
                        'tripay_reference' => data_get($payload, 'reference') ?: $deposit->tripay_reference,
                        'payment_response' => array_merge($deposit->payment_response ?? [], [
                            'tripay_callback' => $payload,
                            'callback_synced_at' => now()->toDateTimeString(),
                        ]),
                    ]);

                    $user = User::whereKey($deposit->user_id)
                        ->lockForUpdate()
                        ->first();

                    if ($user) {
                        if ($deposit->isMembershipPayment()) {
                            if (! $deposit->membershipActivated()) {
                                $user->activateMembership((int) data_get($deposit->payment_response, 'membership_days', 30));
                                $deposit->markMembershipActivated();
                            }
                        } elseif (! $deposit->isRefundCredit()) {
                            $walletHistory = WalletHistory::firstOrCreate(
                                [
                                    'invoice_number' => $deposit->invoice_number,
                                    'type' => 'topup',
                                ],
                                [
                                    'user_id' => $deposit->user_id,
                                    'amount' => $topupAmount,
                                    'description' => 'Top Up Saldo via ' . strtoupper($paymentMethod),
                                ]
                            );

                            if ($walletHistory->wasRecentlyCreated) {
                                $user->increment('balance', $topupAmount);
                            }
                        }
                    }

                    return ['found' => true, 'paid' => true, 'deposit_id' => $deposit->id];
                }

                if (in_array($status, ['FAILED', 'EXPIRED', 'REFUND'], true) && in_array($deposit->payment_status, [null, 'unpaid', 'pending'], true)) {
                    $deposit->update([
                        'payment_status' => 'failed',
                        'payment_response' => array_merge($deposit->payment_response ?? [], [
                            'tripay_callback' => $payload,
                            'callback_synced_at' => now()->toDateTimeString(),
                        ]),
                    ]);

                    return ['found' => true, 'paid' => false, 'failed' => true, 'deposit_id' => $deposit->id];
                }

                return ['found' => true, 'paid' => false];
            });

            if (! $result['found']) {
                return response()->json(['success' => false, 'message' => 'Deposit not found'], 404);
            }

            if ($result['paid'] ?? false) {
                $this->sendDepositPaymentDiscordAlert($discordWebhookService, (int) $result['deposit_id'], 'paid');
            } elseif ($result['failed'] ?? false) {
                $this->sendDepositPaymentDiscordAlert($discordWebhookService, (int) $result['deposit_id'], 'failed');
            }

            return response()->json(['success' => true]);
        }

        if ($this->shouldHandleTransactionReference($merchantRef)) {
            $result = DB::transaction(function () use ($merchantRef, $status, $paidAmount, $paymentMethod, $payload) {
                $transaction = Transaction::where('invoice_number', $merchantRef)
                    ->lockForUpdate()
                    ->first();

                if (! $transaction) {
                    return ['found' => false];
                }

                if ($status === 'PAID' && in_array($transaction->payment_status, ['unpaid', 'pending'], true)) {
                    $transaction->update([
                        'payment_status' => 'paid',
                        'payment_method' => $paymentMethod,
                        'paid_amount' => $paidAmount > 0 ? $paidAmount : $transaction->amount,
                        'response_data' => array_merge($transaction->response_data ?? [], [
                            'payment_gateway' => 'tripay',
                            'tripay_reference' => data_get($payload, 'reference'),
                            'tripay_callback' => $payload,
                        ]),
                    ]);

                    return ['found' => true, 'paid' => true, 'transaction_id' => $transaction->id];
                }

                if (in_array($status, ['FAILED', 'EXPIRED', 'REFUND'], true) && in_array($transaction->payment_status, ['unpaid', 'pending'], true)) {
                    $transaction->update([
                        'payment_status' => 'failed',
                        'order_status' => 'failed',
                        'target_notes' => 'Pembayaran QRIS berstatus ' . $status . '.',
                        'response_data' => array_merge($transaction->response_data ?? [], [
                            'payment_gateway' => 'tripay',
                            'tripay_callback' => $payload,
                        ]),
                    ]);
                }

                return ['found' => true, 'paid' => false];
            });

            if (! $result['found']) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            if ($result['paid'] ?? false) {
                $transaction = Transaction::with(['product', 'user'])->find($result['transaction_id']);
                $orderFulfillmentService->handlePaidTransaction($transaction->fresh(['product', 'user']));
            }

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Unknown merchant_ref format'], 400);
    }

    public function handleXoftwareCallback(
        Request $request,
        OrderFulfillmentService $orderFulfillmentService,
        DiscordWebhookService $discordWebhookService
    ) {
        $rawPayload = $request->getContent();
        $callbackSignature = (string) $request->header('X-Signature', '');
        $webhookSecret = (string) config('xoftware.webhook_secret');

        if (! filled($webhookSecret)) {
            return response()->json(['success' => false, 'message' => 'Webhook secret is not configured'], 500);
        }

        $expectedSignature = hash_hmac('sha256', $rawPayload, $webhookSecret);

        if (! hash_equals(strtolower($expectedSignature), strtolower(trim($callbackSignature)))) {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
        }

        $payload = json_decode($rawPayload, true);

        if (! is_array($payload)) {
            return response()->json(['success' => false, 'message' => 'Invalid payload'], 400);
        }

        $callbackData = data_get($payload, 'data');
        $event = is_array($callbackData) ? array_replace($payload, $callbackData) : $payload;

        $merchantRef = (string) (
            data_get($event, 'order_id')
            ?: data_get($event, 'ref_id')
            ?: data_get($event, 'merchant_ref')
        );
        $providerReference = (string) (
            data_get($event, 'transaction_id')
            ?: data_get($event, 'reference')
            ?: $merchantRef
        );
        $status = strtoupper((string) data_get($event, 'status', ''));
        $paymentStatus = strtoupper((string) data_get($event, 'payment_status', ''));
        $paidAmount = (int) round((float) data_get(
            $event,
            'total_amount',
            data_get($event, 'paid_amount', data_get($event, 'amount', 0))
        ));
        $paymentMethod = (string) (
            data_get($event, 'channel_code')
            ?: data_get($event, 'payment_method')
            ?: 'QRIS'
        );

        $isPaid = in_array($status, ['SUCCESS', 'PAID', 'SUCCEEDED', 'SETTLEMENT', 'CAPTURE'], true)
            || in_array($paymentStatus, ['SUCCESS', 'PAID', 'SUCCEEDED', 'SETTLEMENT', 'CAPTURE'], true);
        $isFailed = in_array($status, ['FAILED', 'EXPIRED', 'REFUND', 'CANCELED', 'CANCELLED'], true)
            || in_array($paymentStatus, ['FAILED', 'EXPIRED', 'REFUND', 'CANCELED', 'CANCELLED'], true);

        if ($this->shouldHandleDepositReference($merchantRef)) {
            $result = DB::transaction(function () use ($merchantRef, $providerReference, $isPaid, $isFailed, $paidAmount, $paymentMethod, $payload, $status, $paymentStatus) {
                $deposit = Deposit::where('invoice_number', $merchantRef)
                    ->lockForUpdate()
                    ->first();

                if (! $deposit) {
                    return ['found' => false];
                }

                if ($isPaid && in_array($deposit->payment_status, [null, 'unpaid', 'pending'], true)) {
                    $topupAmount = (float) $deposit->amount;
                    $actualPaidAmount = $paidAmount > 0 ? $paidAmount : $topupAmount;

                    $deposit->update([
                        'payment_status' => 'paid',
                        'payment_method' => $paymentMethod,
                        'paid_amount' => $actualPaidAmount,
                        'tripay_reference' => filled($providerReference) ? $providerReference : $deposit->tripay_reference,
                        'payment_response' => array_merge($deposit->payment_response ?? [], [
                            'payment_gateway' => 'xoftware',
                            'xoftware_reference' => $providerReference,
                            'xoftware_callback' => $payload,
                            'tripay_reference' => $providerReference,
                            'tripay_callback' => $payload,
                            'callback_synced_at' => now()->toDateTimeString(),
                        ]),
                    ]);

                    $user = User::whereKey($deposit->user_id)
                        ->lockForUpdate()
                        ->first();

                    if ($user) {
                        if ($deposit->isMembershipPayment()) {
                            if (! $deposit->membershipActivated()) {
                                $user->activateMembership((int) data_get($deposit->payment_response, 'membership_days', 30));
                                $deposit->markMembershipActivated();
                            }
                        } elseif (! $deposit->isRefundCredit()) {
                            $walletHistory = WalletHistory::firstOrCreate(
                                [
                                    'invoice_number' => $deposit->invoice_number,
                                    'type' => 'topup',
                                ],
                                [
                                    'user_id' => $deposit->user_id,
                                    'amount' => $topupAmount,
                                    'description' => 'Top Up Saldo via ' . strtoupper($paymentMethod),
                                ]
                            );

                            if ($walletHistory->wasRecentlyCreated) {
                                $user->increment('balance', $topupAmount);
                            }
                        }
                    }

                    return ['found' => true, 'paid' => true, 'deposit_id' => $deposit->id];
                }

                if ($isFailed && in_array($deposit->payment_status, [null, 'unpaid', 'pending'], true)) {
                    $deposit->update([
                        'payment_status' => 'failed',
                        'payment_response' => array_merge($deposit->payment_response ?? [], [
                            'payment_gateway' => 'xoftware',
                            'xoftware_callback' => $payload,
                            'callback_synced_at' => now()->toDateTimeString(),
                        ]),
                    ]);

                    return ['found' => true, 'paid' => false, 'failed' => true, 'deposit_id' => $deposit->id];
                }

                return ['found' => true, 'paid' => false];
            });

            if (! $result['found']) {
                return response()->json(['success' => false, 'message' => 'Deposit not found'], 404);
            }

            if ($result['paid'] ?? false) {
                $this->sendDepositPaymentDiscordAlert($discordWebhookService, (int) $result['deposit_id'], 'paid');
            } elseif ($result['failed'] ?? false) {
                $this->sendDepositPaymentDiscordAlert($discordWebhookService, (int) $result['deposit_id'], 'failed');
            }

            return response()->json(['success' => true]);
        }

        if ($this->shouldHandleTransactionReference($merchantRef)) {
            $result = DB::transaction(function () use ($merchantRef, $providerReference, $isPaid, $isFailed, $paidAmount, $paymentMethod, $payload, $status, $paymentStatus) {
                $transaction = Transaction::where('invoice_number', $merchantRef)
                    ->lockForUpdate()
                    ->first();

                if (! $transaction) {
                    return ['found' => false];
                }

                if ($isPaid && in_array($transaction->payment_status, ['unpaid', 'pending'], true)) {
                    $transaction->update([
                        'payment_status' => 'paid',
                        'payment_method' => $paymentMethod,
                        'paid_amount' => $paidAmount > 0 ? $paidAmount : $transaction->amount,
                        'response_data' => array_merge($transaction->response_data ?? [], [
                            'payment_gateway' => 'xoftware',
                            'xoftware_reference' => $providerReference,
                            'xoftware_callback' => $payload,
                            'tripay_reference' => $providerReference,
                            'tripay_callback' => $payload,
                        ]),
                    ]);

                    return ['found' => true, 'paid' => true, 'transaction_id' => $transaction->id];
                }

                if ($isFailed && in_array($transaction->payment_status, ['unpaid', 'pending'], true)) {
                    $failedStatus = $status ?: $paymentStatus ?: 'FAILED';

                    $transaction->update([
                        'payment_status' => 'failed',
                        'order_status' => 'failed',
                        'target_notes' => 'Pembayaran QRIS berstatus ' . $failedStatus . '.',
                        'response_data' => array_merge($transaction->response_data ?? [], [
                            'payment_gateway' => 'xoftware',
                            'xoftware_reference' => $providerReference,
                            'xoftware_callback' => $payload,
                        ]),
                    ]);
                }

                return ['found' => true, 'paid' => false];
            });

            if (! $result['found']) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            $transaction = Transaction::with(['product', 'user'])
                ->where('invoice_number', $merchantRef)
                ->first();

            if ($transaction?->payment_status === 'paid'
                && ! in_array($transaction->order_status, ['success', 'failed'], true)) {
                $orderFulfillmentService->handlePaidTransaction($transaction);
            }

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Unknown merchant_ref format'], 400);
    }

    public function handleNotification(
        Request $request,
        OrderFulfillmentService $orderFulfillmentService,
        DiscordWebhookService $discordWebhookService
    ) {
        $notification = json_decode($request->getContent());

        if (! $notification) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $serverKey = (string) config('midtrans.server_key');
        $signatureKey = hash(
            'sha512',
            $notification->order_id . $notification->status_code . $notification->gross_amount . $serverKey
        );

        if ($signatureKey !== $notification->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transactionStatus = $notification->transaction_status;
        $orderId = $notification->order_id;
        $paidAmount = (int) round((float) $notification->gross_amount);

        if ($this->shouldHandleDepositReference($orderId)) {
            $result = DB::transaction(function () use ($orderId, $transactionStatus, $paidAmount, $notification) {
                $deposit = Deposit::where('invoice_number', $orderId)
                    ->lockForUpdate()
                    ->first();

                if (! $deposit) {
                    return ['found' => false];
                }

                if (in_array($transactionStatus, ['settlement', 'capture'], true) && in_array($deposit->payment_status, ['unpaid', 'pending'], true)) {
                    $topupAmount = (float) $deposit->amount;
                    $actualPaidAmount = $paidAmount > 0 ? $paidAmount : $topupAmount;

                    $deposit->update([
                        'payment_status' => 'paid',
                        'payment_method' => $notification->payment_type,
                        'paid_amount' => $actualPaidAmount,
                    ]);

                    $user = User::whereKey($deposit->user_id)
                        ->lockForUpdate()
                        ->first();

                    if ($user) {
                        if ($deposit->isMembershipPayment()) {
                            if (! $deposit->membershipActivated()) {
                                $user->activateMembership((int) data_get($deposit->payment_response, 'membership_days', 30));
                                $deposit->markMembershipActivated();
                            }
                        } elseif (! $deposit->isRefundCredit()) {
                            $walletHistory = WalletHistory::firstOrCreate(
                                [
                                    'invoice_number' => $deposit->invoice_number,
                                    'type' => 'topup',
                                ],
                                [
                                    'user_id' => $deposit->user_id,
                                    'amount' => $topupAmount,
                                    'description' => 'Top Up Saldo via ' . strtoupper($notification->payment_type),
                                ]
                            );

                            if ($walletHistory->wasRecentlyCreated) {
                                $user->increment('balance', $topupAmount);
                            }
                        }
                    }

                    return ['found' => true, 'paid' => true, 'deposit_id' => $deposit->id];
                }

                if (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true) && in_array($deposit->payment_status, ['unpaid', 'pending'], true)) {
                    $deposit->update([
                        'payment_status' => 'failed',
                        'payment_response' => array_merge($deposit->payment_response ?? [], [
                            'payment_gateway' => 'midtrans',
                            'midtrans_callback' => (array) $notification,
                            'callback_synced_at' => now()->toDateTimeString(),
                        ]),
                    ]);

                    return ['found' => true, 'paid' => false, 'failed' => true, 'deposit_id' => $deposit->id];
                }

                return ['found' => true, 'paid' => false];
            });

            if (! $result['found']) {
                return response()->json(['message' => 'Deposit not found'], 404);
            }

            if ($result['paid'] ?? false) {
                $this->sendDepositPaymentDiscordAlert($discordWebhookService, (int) $result['deposit_id'], 'paid');
            } elseif ($result['failed'] ?? false) {
                $this->sendDepositPaymentDiscordAlert($discordWebhookService, (int) $result['deposit_id'], 'failed');
            }

            return response()->json(['message' => 'Deposit handled successfully']);
        }

        if ($this->shouldHandleTransactionReference($orderId)) {
            $transaction = Transaction::where('invoice_number', $orderId)->first();

            if (! $transaction) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            if (in_array($transactionStatus, ['settlement', 'capture'], true) && in_array($transaction->payment_status, ['unpaid', 'pending'], true)) {
                $transaction->update([
                    'payment_status' => 'paid',
                    'payment_method' => $notification->payment_type,
                    'paid_amount' => $paidAmount,
                ]);

                $orderFulfillmentService->handlePaidTransaction($transaction->fresh(['product', 'user']));
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
                $transaction->update([
                    'payment_status' => 'failed',
                    'order_status' => 'failed',
                    'target_notes' => 'Pembayaran dibatalkan atau kedaluwarsa di Midtrans.',
                ]);
            }

            return response()->json(['message' => 'Transaction callback handled successfully']);
        }

        return response()->json(['message' => 'Unknown Order ID format'], 400);
    }

    protected function shouldHandleDepositReference(string $merchantRef): bool
    {
        if (str_starts_with($merchantRef, 'DEP-')) {
            return true;
        }

        return str_starts_with($merchantRef, 'MEM-')
            && Deposit::where('invoice_number', $merchantRef)->exists();
    }

    protected function shouldHandleTransactionReference(string $merchantRef): bool
    {
        return str_starts_with($merchantRef, 'WIB-')
            || str_starts_with($merchantRef, 'MEM-');
    }

    protected function sendDepositPaymentDiscordAlert(DiscordWebhookService $discordWebhookService, int $depositId, string $status): void
    {
        $deposit = Deposit::with('user')->find($depositId);

        if (! $deposit) {
            return;
        }

        $isPaid = $status === 'paid';
        $isMembership = $deposit->isMembershipPayment();

        $discordWebhookService->sendDepositAlertOnce(
            $deposit,
            $isPaid ? 'paid' : 'failed',
            $isMembership
                ? ($isPaid ? 'Membership berhasil dibayar' : 'Membership gagal dibayar')
                : ($isPaid ? 'Deposit berhasil dibayar' : 'Deposit gagal dibayar'),
            $isMembership
                ? ($isPaid ? 'Membership user sudah otomatis aktif.' : 'Pembayaran membership gagal atau kedaluwarsa.')
                : ($isPaid ? 'Saldo user sudah otomatis ditambahkan.' : 'Pembayaran deposit gagal atau kedaluwarsa.'),
            $isPaid ? 4968121 : 16738680
        );
    }
}
