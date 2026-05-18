<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminReport;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Support\WiboostAdminContact;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminReportController extends Controller
{
    public function create(Request $request)
    {
        $categories = collect($this->categories());
        $selectedKey = (string) $request->query('type', '');
        $selectedCategory = $selectedKey !== ''
            ? $categories->firstWhere('key', $selectedKey)
            : null;

        $records = $selectedCategory ? $this->recordsFor($selectedCategory) : collect();
        $adminNumber = WiboostAdminContact::whatsappNumber();

        return view('user.report-admin', [
            'adminNumber' => $adminNumber,
            'categories' => $categories,
            'records' => $records,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    public function store(Request $request)
    {
        $categories = collect($this->categories());

        $validated = $request->validate([
            'category_key' => ['required', 'string', Rule::in($categories->pluck('key')->all())],
            'issue' => 'required|string|min:5|max:2000',
            'record_id' => 'nullable|string|max:100',
            'proof' => 'required|image|max:5120',
        ]);

        $selectedCategory = $categories->firstWhere('key', (string) $validated['category_key'])
            ?? $categories->firstWhere('key', 'umum');
        $records = $this->recordsFor($selectedCategory);

        if (($selectedCategory['needs_record'] ?? false) && $records->isNotEmpty() && blank($validated['record_id'] ?? null)) {
            return back()
                ->withInput()
                ->withErrors(['record_id' => 'Pilih data deposit atau pesanan yang bermasalah dulu ya.']);
        }

        $selectedRecord = $records->firstWhere('id', $validated['record_id'] ?? null);

        if (($selectedCategory['needs_record'] ?? false) && $records->isNotEmpty() && ! $selectedRecord) {
            return back()
                ->withInput()
                ->withErrors(['record_id' => 'Data yang dipilih tidak ditemukan. Pilih ulang dari daftar yang tersedia.']);
        }

        $issue = trim((string) $validated['issue']);
        $recordId = $selectedRecord['id'] ?? null;
        $reportHash = $this->reportHash($selectedCategory, $issue, $recordId);

        $duplicateReport = AdminReport::query()
            ->where('report_hash', $reportHash)
            ->latest()
            ->first();

        if ($duplicateReport) {
            $whatsappUrl = WiboostAdminContact::whatsappUrl($duplicateReport->whatsapp_message);

            if (! $whatsappUrl) {
                return back()->with('error', 'Nomor WhatsApp admin belum diatur.');
            }

            return redirect()->away($whatsappUrl);
        }

        $proofFile = $request->file('proof');
        $proofPath = $proofFile?->store('admin-reports', 'public');
        $proofUrl = $proofPath ? url(Storage::url($proofPath)) : null;

        $message = $this->buildWhatsappMessage($selectedCategory, $issue, $selectedRecord, $proofUrl);

        try {
            AdminReport::create([
                'user_id' => auth()->id(),
                'report_hash' => $reportHash,
                'category_key' => $selectedCategory['key'],
                'category_label' => $selectedCategory['label'],
                'issue' => $issue,
                'record_id' => $recordId,
                'related_invoice' => $selectedRecord['invoice'] ?? null,
                'related_title' => $selectedRecord['product'] ?? $selectedRecord['title'] ?? null,
                'related_snapshot' => $selectedRecord,
                'proof_path' => $proofPath,
                'proof_original_name' => $proofFile?->getClientOriginalName(),
                'whatsapp_message' => $message,
                'status' => 'new',
            ]);
        } catch (QueryException $exception) {
            if ($proofPath) {
                Storage::disk('public')->delete($proofPath);
            }

            $duplicateReport = AdminReport::query()
                ->where('report_hash', $reportHash)
                ->latest()
                ->first();

            if (! $duplicateReport) {
                throw $exception;
            }

            $whatsappUrl = WiboostAdminContact::whatsappUrl($duplicateReport->whatsapp_message);

            if (! $whatsappUrl) {
                return back()->with('error', 'Nomor WhatsApp admin belum diatur.');
            }

            return redirect()->away($whatsappUrl);
        }

        $whatsappUrl = WiboostAdminContact::whatsappUrl($message);

        if (! $whatsappUrl) {
            return back()->with('error', 'Nomor WhatsApp admin belum diatur.');
        }

        return redirect()->away($whatsappUrl);
    }

    protected function reportHash(array $category, string $issue, ?string $recordId): string
    {
        return hash('sha256', implode('|', [
            auth()->id(),
            $category['key'] ?? 'umum',
            $recordId ?: '-',
            Str::lower(preg_replace('/\s+/', ' ', trim($issue))),
            now()->toDateString(),
        ]));
    }

    protected function categories(): array
    {
        return [
            ['key' => 'deposit', 'label' => 'Deposit', 'record_type' => 'deposit', 'needs_record' => true],
            ['key' => 'sosmed', 'label' => 'Suntik Sosmed', 'record_type' => 'suntik-sosmed', 'needs_record' => true],
            ['key' => 'game', 'label' => 'Top Up Game', 'record_type' => 'top-up-game', 'needs_record' => true],
            ['key' => 'data', 'label' => 'Paket Data', 'record_type' => 'paket-data', 'needs_record' => true],
            ['key' => 'premium', 'label' => 'Aplikasi Premium', 'record_type' => 'aplikasi-premium', 'needs_record' => true],
            ['key' => 'umum', 'label' => 'Umum', 'record_type' => 'general', 'needs_record' => false],
        ];
    }

    protected function recordsFor(array $category)
    {
        if (! ($category['needs_record'] ?? false)) {
            return collect();
        }

        if (($category['key'] ?? null) === 'deposit') {
            return Deposit::query()
                ->where('user_id', auth()->id())
                ->where(function ($query) {
                    $query->whereNull('payment_status')
                        ->orWhereNotIn('payment_status', ['unpaid', 'pending']);
                })
                ->latest()
                ->take(30)
                ->get()
                ->map(fn (Deposit $deposit) => [
                    'id' => 'deposit-' . $deposit->id,
                    'invoice' => $deposit->invoice_number,
                    'title' => 'Deposit Rp ' . number_format((float) ($deposit->amount ?? 0), 0, ',', '.'),
                    'meta' => ($deposit->created_at?->format('d M Y, H:i') ?: '-') . ' - ' . strtoupper((string) ($deposit->payment_status ?: '-')),
                    'amount' => 'Rp ' . number_format((float) ($deposit->amount ?? 0), 0, ',', '.'),
                    'paidAmount' => $deposit->paid_amount ? 'Rp ' . number_format((float) $deposit->paid_amount, 0, ',', '.') : '-',
                    'status' => strtoupper((string) ($deposit->payment_status ?: '-')),
                    'method' => strtoupper((string) ((strtolower((string) $deposit->payment_method) === 'tripay') ? 'QRIS' : ($deposit->payment_method ?: 'QRIS'))),
                    'reference' => $deposit->tripay_reference ?: '-',
                    'created' => $deposit->created_at?->format('d M Y, H:i') ?: '-',
                ])
                ->values();
        }

        return Transaction::query()
            ->with('product.category.parent.parent.parent.parent')
            ->where('user_id', auth()->id())
            ->where('payment_status', 'paid')
            ->latest()
            ->take(80)
            ->get()
            ->map(function (Transaction $transaction) {
                $product = $transaction->product;
                $rootCategory = $this->resolveRootCategory($product?->category);

                return [
                    'id' => 'transaction-' . $transaction->id,
                    'categoryKey' => $this->normalizeRootSlug($rootCategory?->slug ?: $rootCategory?->name),
                    'invoice' => $transaction->invoice_number,
                    'title' => Str::limit((string) ($product?->name ?: 'Produk Dihapus'), 64),
                    'meta' => ($transaction->created_at?->format('d M Y, H:i') ?: '-') . ' - ' . strtoupper((string) ($transaction->order_status ?: '-')),
                    'category' => $rootCategory?->name ?: '-',
                    'product' => $product?->name ?: 'Produk Dihapus',
                    'amount' => 'Rp ' . number_format((float) ($transaction->amount ?? 0), 0, ',', '.'),
                    'status' => strtoupper((string) ($transaction->order_status ?: '-')),
                    'payment' => strtoupper((string) (strtolower((string) $transaction->payment_method) === 'tripay' ? 'QRIS' : ($transaction->payment_method ?: '-'))),
                    'target' => $this->reportTarget($transaction),
                    'providerId' => $transaction->provider_order_id ?: '-',
                    'created' => $transaction->created_at?->format('d M Y, H:i') ?: '-',
                ];
            })
            ->filter(fn (array $record) => $record['categoryKey'] === ($category['record_type'] ?? null))
            ->values();
    }

    protected function buildWhatsappMessage(array $category, string $issue, ?array $record, ?string $proofUrl): string
    {
        $lines = [
            'Halo Admin Wiboost, saya ingin melaporkan kendala.',
            '',
            'Jenis Laporan: ' . ($category['label'] ?? 'Umum'),
            'Nama: ' . (auth()->user()->name ?? '-'),
            'Email: ' . (auth()->user()->email ?? '-'),
            '',
            'Kendala:',
            trim($issue),
        ];

        $lines[] = '';
        $lines[] = 'Bukti Kendala: ' . ($proofUrl ?: '-');

        if (($category['key'] ?? null) === 'deposit') {
            $lines[] = '';
            $lines[] = 'Data Deposit:';
            if ($record) {
                array_push(
                    $lines,
                    'Invoice: ' . ($record['invoice'] ?? '-'),
                    'Nominal Masuk Saldo: ' . ($record['amount'] ?? '-'),
                    'Jumlah Dibayar: ' . ($record['paidAmount'] ?? '-'),
                    'Metode: ' . ($record['method'] ?? '-'),
                    'Status: ' . ($record['status'] ?? '-'),
                    'Referensi QRIS: ' . ($record['reference'] ?? '-'),
                    'Waktu: ' . ($record['created'] ?? '-')
                );
            } else {
                $lines[] = '-';
            }
        } elseif ($category['needs_record'] ?? false) {
            $lines[] = '';
            $lines[] = 'Data Pesanan:';
            if ($record) {
                array_push(
                    $lines,
                    'Invoice: ' . ($record['invoice'] ?? '-'),
                    'Kategori: ' . ($record['category'] ?? '-'),
                    'Produk: ' . ($record['product'] ?? '-'),
                    'Total Bayar: ' . ($record['amount'] ?? '-'),
                    'Metode: ' . ($record['payment'] ?? '-'),
                    'Status Pesanan: ' . ($record['status'] ?? '-'),
                    'ID Provider: ' . ($record['providerId'] ?? '-'),
                    'Waktu: ' . ($record['created'] ?? '-'),
                    'Input Pembeli: ' . (($record['target'] ?? '-') ?: '-')
                );
            } else {
                $lines[] = '-';
            }
        }

        array_push(
            $lines,
            '',
            'Mohon dibantu pengecekannya. Terima kasih.'
        );

        return implode("\n", $lines);
    }

    protected function reportTarget(Transaction $transaction): string
    {
        $inputText = trim((string) ($transaction->order_input_text ?: ''));
        $targetData = trim((string) ($transaction->target_data ?: ''));

        if ($inputText !== '') {
            return $inputText;
        }

        if ($targetData === '' || $targetData === '-' || Str::contains(Str::lower($targetData), 'tidak membutuhkan target')) {
            return '-';
        }

        return $targetData;
    }

    protected function normalizeRootSlug($slug): string
    {
        $slug = Str::slug((string) $slug);

        return match ($slug) {
            'kuota-murah' => 'paket-data',
            default => $slug,
        };
    }

    protected function resolveRootCategory($category)
    {
        while ($category?->parent) {
            $category = $category->parent;
        }

        return $category;
    }
}
