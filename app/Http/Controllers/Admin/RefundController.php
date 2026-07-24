<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Support\WiboostCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $selectedMonth = $request->query('month', date('m'));
        $selectedYear = $request->query('year', date('Y'));

        if (! preg_match('/^(0[1-9]|1[0-2])$/', (string) $selectedMonth)) {
            $selectedMonth = date('m');
        }

        if (! preg_match('/^\d{4}$/', (string) $selectedYear)) {
            $selectedYear = date('Y');
        }

        $refunds = Refund::query()
            ->with(['transaction.user', 'transaction.product.category.parent.parent.parent'])
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('transaction', function ($transactionQuery) use ($search) {
                        $transactionQuery->where('invoice_number', 'like', "%{$search}%")
                            ->orWhere('target_data', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhere('whatsapp', 'like', "%{$search}%");
                            })
                            ->orWhereHas('product', function ($productQuery) use ($search) {
                                $productQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('provider_product_id', 'like', "%{$search}%");
                            });
                    });
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('admin.refunds.index', compact('refunds', 'search', 'selectedMonth', 'selectedYear'));
    }

    public function create(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $transactions = Transaction::query()
            ->with(['user', 'product.category.parent.parent.parent'])
            ->where('payment_status', 'paid')
            ->where(function ($query) {
                $query->where('invoice_number', 'like', 'MEM-%')
                    ->orWhere('target_data', 'like', '%Membership Wiboost%')
                    ->orWhere('response_data', 'like', '%membership%')
                    ->orWhereHas('product', fn ($productQuery) => $productQuery->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()));
            })
            ->whereNull('refunded_at')
            ->doesntHave('refund')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('target_data', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('whatsapp', 'like', "%{$search}%");
                        })
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('provider_product_id', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('admin.refunds.create', compact('transactions', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => ['required', 'exists:transactions,id', 'unique:refunds,transaction_id'],
        ], [], [
            'transaction_id' => 'transaksi',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $transaction = Transaction::query()
                ->with('user')
                ->lockForUpdate()
                ->findOrFail($validated['transaction_id']);

            if ($transaction->refunded_at !== null || $transaction->refund()->exists()) {
                throw ValidationException::withMessages([
                    'transaction_id' => 'Transaksi ini sudah direfund.',
                ]);
            }

            $this->applyManualRefund(new Refund(), $transaction, $request->user()?->id);
        });

        return $this->redirectToReturnTo($request, 'admin.refunds.index')
            ->with('success', 'Refund berhasil ditambahkan.');
    }

    public function edit(Refund $refund)
    {
        $refund->load(['transaction.user', 'transaction.product.category.parent.parent.parent']);

        $transactions = $this->refundableTransactionsQuery()
            ->where(function ($query) use ($refund) {
                $query->where(function ($candidateQuery) {
                    $candidateQuery->whereNull('refunded_at')
                        ->doesntHave('refund');
                })->orWhereKey($refund->transaction_id);
            })
            ->latest()
            ->limit(250)
            ->get();

        return view('admin.refunds.edit', compact('refund', 'transactions'));
    }

    public function update(Request $request, Refund $refund)
    {
        $validated = $request->validate([
            'transaction_id' => ['required', 'exists:transactions,id', 'unique:refunds,transaction_id,' . $refund->id],
        ], [], [
            'transaction_id' => 'transaksi',
        ]);

        DB::transaction(function () use ($request, $refund, $validated) {
            $lockedRefund = Refund::query()
                ->with('transaction.user')
                ->lockForUpdate()
                ->findOrFail($refund->id);

            if ((int) $lockedRefund->transaction_id === (int) $validated['transaction_id']) {
                return;
            }

            $this->reverseManualRefund($lockedRefund);

            $transaction = Transaction::query()
                ->with('user')
                ->lockForUpdate()
                ->findOrFail($validated['transaction_id']);

            if ($transaction->refunded_at !== null || $transaction->refund()->exists()) {
                throw ValidationException::withMessages([
                    'transaction_id' => 'Transaksi ini sudah direfund.',
                ]);
            }

            $this->applyManualRefund($lockedRefund, $transaction, $request->user()?->id);
        });

        return $this->redirectToReturnTo($request, 'admin.refunds.index')
            ->with('success', 'Refund berhasil diperbarui.');
    }

    public function destroy(Refund $refund)
    {
        DB::transaction(function () use ($refund) {
            $lockedRefund = Refund::query()
                ->with('transaction.user')
                ->lockForUpdate()
                ->findOrFail($refund->id);

            $this->reverseManualRefund($lockedRefund);
            $lockedRefund->delete();
        });

        return redirect()
            ->route('admin.refunds.index')
            ->with('success', 'Refund berhasil dihapus.');
    }

    protected function refundableTransactionsQuery()
    {
        return Transaction::query()
            ->with(['user', 'product.category.parent.parent.parent'])
            ->where('payment_status', 'paid')
            ->where(function ($query) {
                $query->where('invoice_number', 'like', 'MEM-%')
                    ->orWhere('target_data', 'like', '%Membership Wiboost%')
                    ->orWhere('response_data', 'like', '%membership%')
                    ->orWhereHas('product', fn ($productQuery) => $productQuery->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()));
            });
    }

    protected function applyManualRefund(Refund $refund, Transaction $transaction, ?int $adminId): void
    {
        $amount = $this->refundAmount($transaction);
        $metadata = [
            'manual_refund_amount' => $amount,
            'had_refunded_at' => $transaction->refunded_at !== null,
            'previous_order_status' => $transaction->order_status,
        ];

        $refund->forceFill([
            'transaction_id' => $transaction->id,
            'admin_id' => $adminId,
            'proof_path' => $refund->proof_path ?: '',
            'notes' => json_encode($metadata),
        ])->save();

        if ($amount > 0 && $transaction->user_id) {
            $user = User::query()
                ->lockForUpdate()
                ->find($transaction->user_id);

            if ($user) {
                $user->increment('balance', $amount);

                WalletHistory::updateOrCreate(
                    [
                        'invoice_number' => $this->refundInvoiceNumber($transaction),
                        'type' => 'refund',
                    ],
                    [
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'description' => 'Refund transaksi ' . $transaction->invoice_number,
                    ]
                );
            }
        }

        if ($transaction->refunded_at === null) {
            $transaction->forceFill(['refunded_at' => now()])->save();
        }
    }

    protected function reverseManualRefund(Refund $refund): void
    {
        $transaction = $refund->transaction;

        if (! $transaction) {
            return;
        }

        $metadata = $this->refundMetadata($refund);
        $history = WalletHistory::query()
            ->where('invoice_number', $this->refundInvoiceNumber($transaction))
            ->where('type', 'refund')
            ->first();

        if ($history && $transaction->user_id) {
            $user = User::query()
                ->lockForUpdate()
                ->find($transaction->user_id);

            if ($user) {
                $user->decrement('balance', (float) $history->amount);
            }

            $history->delete();
        }

        $updates = [];

        if (! (bool) ($metadata['had_refunded_at'] ?? false)) {
            $updates['refunded_at'] = null;
        }

        if (array_key_exists('previous_order_status', $metadata)) {
            $updates['order_status'] = $metadata['previous_order_status'];
        } elseif ($transaction->payment_status === 'paid') {
            $updates['order_status'] = 'success';
        }

        if ($updates !== []) {
            $transaction->forceFill($updates)->save();
        }
    }

    protected function refundAmount(Transaction $transaction): int
    {
        return max(0, (int) $transaction->display_total_payable);
    }

    protected function refundInvoiceNumber(Transaction $transaction): string
    {
        return 'REFUND-' . $transaction->invoice_number;
    }

    protected function refundMetadata(Refund $refund): array
    {
        $metadata = json_decode((string) $refund->notes, true);

        return is_array($metadata) ? $metadata : [];
    }
}
