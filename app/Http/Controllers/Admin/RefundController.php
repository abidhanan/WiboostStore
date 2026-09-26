<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Refund;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\DiscordWebhookService;
use App\Support\SimpleXlsx;
use App\Support\WiboostCatalog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $refundFilter = (string) $request->query('refund_filter', 'all');
        $selectedMonth = $request->query('month', now()->format('m'));
        $selectedYear = $request->query('year', now()->format('Y'));
        $allowedRefundFilters = ['all', '25', '50', '75', '100'];

        if (! in_array($refundFilter, $allowedRefundFilters, true)) {
            $refundFilter = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', (string) $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', (string) $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $refunds = Refund::query()
            ->with(['transaction.user', 'transaction.product.category.parent.parent.parent'])
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->when($refundFilter !== 'all', function ($query) use ($refundFilter) {
                $query->where(function ($filterQuery) use ($refundFilter) {
                    $filterQuery->where('notes', 'like', '%"manual_refund_percent":' . $refundFilter . '%')
                        ->orWhere('notes', 'like', '%"manual_refund_percent":"' . $refundFilter . '"%');
                });
            })
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

        return view('admin.refunds.index', compact('refunds', 'search', 'refundFilter', 'selectedMonth', 'selectedYear'));
    }

    public function exportExcel(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $refundFilter = (string) $request->query('refund_filter', 'all');
        $selectedMonth = (string) $request->query('month', now()->format('m'));
        $selectedYear = (string) $request->query('year', now()->format('Y'));
        $allowedRefundFilters = ['all', '25', '50', '75', '100'];

        if (! in_array($refundFilter, $allowedRefundFilters, true)) {
            $refundFilter = 'all';
        }

        if (! preg_match('/^(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = now()->format('m');
        }

        if (! preg_match('/^\d{4}$/', $selectedYear)) {
            $selectedYear = now()->format('Y');
        }

        $refunds = Refund::query()
            ->with(['transaction.user', 'transaction.product.category.parent.parent.parent'])
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->when($refundFilter !== 'all', function ($query) use ($refundFilter) {
                $query->where(function ($filterQuery) use ($refundFilter) {
                    $filterQuery->where('notes', 'like', '%"manual_refund_percent":' . $refundFilter . '%')
                        ->orWhere('notes', 'like', '%"manual_refund_percent":"' . $refundFilter . '"%');
                });
            })
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
            ->get();

        Carbon::setLocale('id');
        $monthName = Carbon::createFromDate((int) $selectedYear, (int) $selectedMonth, 1)->translatedFormat('F Y');
        $totalTransaction = $refunds->sum(fn (Refund $refund) => (float) ($refund->transaction?->display_total_payable ?? 0));
        $totalRefund = $refunds->sum(fn (Refund $refund) => (float) $refund->manual_refund_amount);
        $rows = $refunds->map(function (Refund $refund, int $index) {
            $transaction = $refund->transaction;

            return [
                $index + 1,
                $refund->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-',
                $transaction?->invoice_number ?? '-',
                $transaction?->user?->name ?? 'Guest',
                $transaction?->user?->email ?? '-',
                $transaction?->display_product_name ?? 'Produk Wiboost',
                (float) ($transaction?->display_total_payable ?? 0),
                (float) $refund->manual_refund_amount,
                $refund->manual_refund_percent . '%',
            ];
        })->all();

        return SimpleXlsx::download(
            'Wiboost Store - Laporan Refund',
            'Refund',
            'laporan-refund-' . str_replace(' ', '-', strtolower($monthName)) . '.xlsx',
            'Periode: ' . $monthName,
            ['No', 'Waktu Refund', 'Invoice', 'Nama', 'Email', 'Produk', 'Harga', 'Nominal Refund', 'Persen'],
            $rows,
            [8, 20, 24, 28, 34, 58, 18, 20, 14],
            [1],
            [7, 8],
            ['', '', '', '', '', 'TOTAL', (float) $totalTransaction, (float) $totalRefund, '']
        );
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

    public function store(Request $request, DiscordWebhookService $discordWebhookService)
    {
        $validated = $request->validate([
            'transaction_id' => ['required', 'exists:transactions,id', 'unique:refunds,transaction_id'],
            'refund_percent' => ['nullable', 'integer', 'in:25,50,75,100'],
        ], [], [
            'transaction_id' => 'transaksi',
            'refund_percent' => 'persentase refund',
        ]);

        $refundPercent = (int) ($validated['refund_percent'] ?? 100);

        $refund = DB::transaction(function () use ($request, $validated, $refundPercent) {
            $transaction = Transaction::query()
                ->with('user')
                ->lockForUpdate()
                ->findOrFail($validated['transaction_id']);

            if ($transaction->refunded_at !== null || $transaction->refund()->exists()) {
                throw ValidationException::withMessages([
                    'transaction_id' => 'Transaksi ini sudah direfund.',
                ]);
            }

            $refund = new Refund();

            $this->applyManualRefund($refund, $transaction, $request->user()?->id, $refundPercent);

            return $refund->fresh(['transaction.user', 'transaction.product']);
        });

        if ($refund) {
            $this->sendRefundDiscordAlert($discordWebhookService, $refund);
        }

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

            // Pertahankan persentase refund yang sudah ditetapkan saat penggantian transaksi.
            $existingPercent = (int) ($this->refundMetadata($lockedRefund)['manual_refund_percent'] ?? 100);

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

            $this->applyManualRefund($lockedRefund, $transaction, $request->user()?->id, $existingPercent);
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

    protected function applyManualRefund(Refund $refund, Transaction $transaction, ?int $adminId, int $percent = 100): void
    {
        $percent = in_array($percent, [25, 50, 75, 100], true) ? $percent : 100;
        $amount = $this->refundAmount($transaction, $percent);
        $metadata = [
            'manual_refund_amount' => $amount,
            'manual_refund_percent' => $percent,
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
                $refundInvoiceNumber = $this->refundInvoiceNumber($transaction);
                $history = WalletHistory::query()
                    ->where('invoice_number', $refundInvoiceNumber)
                    ->where('type', 'refund')
                    ->lockForUpdate()
                    ->first();
                $previousAmount = $history ? (float) $history->amount : 0;
                $balanceDelta = $amount - $previousAmount;

                if ($history) {
                    $history->forceFill([
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'description' => 'Refund transaksi ' . $transaction->invoice_number,
                    ])->save();
                } else {
                    $history = WalletHistory::create([
                        'invoice_number' => $refundInvoiceNumber,
                        'type' => 'refund',
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'description' => 'Refund transaksi ' . $transaction->invoice_number,
                    ]);
                }

                if ($balanceDelta > 0) {
                    $user->increment('balance', $balanceDelta);
                } elseif ($balanceDelta < 0) {
                    $user->decrement('balance', abs($balanceDelta));
                }

                $this->syncRefundDeposit($transaction, $user, $amount, $history);
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

        Deposit::query()
            ->where('invoice_number', $this->refundInvoiceNumber($transaction))
            ->where('payment_method', 'refund')
            ->delete();

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

    protected function refundAmount(Transaction $transaction, int $percent = 100): int
    {
        $percent = in_array($percent, [25, 50, 75, 100], true) ? $percent : 100;

        return max(0, (int) round(((float) $transaction->display_total_payable) * $percent / 100));
    }

    protected function sendRefundDiscordAlert(DiscordWebhookService $discordWebhookService, Refund $refund): void
    {
        $transaction = $refund->transaction;

        if (! $transaction) {
            return;
        }

        $metadata = $this->refundMetadata($refund);
        $refundedAmount = (int) ($metadata['manual_refund_amount'] ?? $this->refundAmount($transaction));
        $refundedPercent = (int) ($metadata['manual_refund_percent'] ?? 100);

        $discordWebhookService->sendSystemAlert(
            'Refund manual ditambahkan',
            'Saldo user sudah dikembalikan dari halaman refund admin.',
            [
                ['name' => 'Invoice', 'value' => (string) $transaction->invoice_number, 'inline' => true],
                ['name' => 'User', 'value' => (string) ($transaction->user->name ?? 'Guest'), 'inline' => true],
                ['name' => 'Produk', 'value' => (string) ($transaction->display_product_name ?? '-'), 'inline' => true],
                ['name' => 'Nominal Refund', 'value' => 'Rp ' . number_format($refundedAmount, 0, ',', '.') . ' (' . $refundedPercent . '%)', 'inline' => true],
            ],
            16433050,
            'refunds'
        );
    }

    protected function refundInvoiceNumber(Transaction $transaction): string
    {
        return 'REFUND-' . $transaction->invoice_number;
    }

    protected function syncRefundDeposit(Transaction $transaction, User $user, int $amount, WalletHistory $history): void
    {
        Deposit::updateOrCreate(
            ['invoice_number' => $this->refundInvoiceNumber($transaction)],
            [
                'user_id' => $user->id,
                'amount' => $amount,
                'paid_amount' => $amount,
                'payment_status' => 'refund',
                'payment_method' => 'refund',
                'payment_response' => [
                    'purpose' => 'refund_wallet',
                    'wallet_history_id' => $history->id,
                    'source_invoice_number' => $transaction->invoice_number,
                    'description' => $history->description,
                ],
            ]
        );
    }

    protected function refundMetadata(Refund $refund): array
    {
        $metadata = json_decode((string) $refund->notes, true);

        return is_array($metadata) ? $metadata : [];
    }
}
