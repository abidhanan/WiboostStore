<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminReport;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Support\WiboostAdminContact;
use App\Support\WiboostOrderDetailData;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cookie;
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
            'record_id' => 'required|string|max:100',
            'proof' => 'required|file|mimes:jpg,jpeg,png,webp,gif,heic,heif|max:12288',
        ], [
            'category_key.required' => 'Pilih jenis kendala terlebih dahulu.',
            'issue.required' => 'Tulis kendala terlebih dahulu.',
            'record_id.required' => 'Pilih riwayat pesanan terlebih dahulu.',
            'proof.required' => 'Upload bukti kendala terlebih dahulu.',
            'proof.uploaded' => 'Bukti kendala gagal diupload. Coba pilih gambar yang lebih kecil atau screenshot baru.',
            'proof.mimes' => 'Bukti kendala harus berupa gambar JPG, PNG, WEBP, GIF, HEIC, atau HEIF.',
            'proof.max' => 'Bukti kendala maksimal 12 MB.',
        ]);

        $selectedCategory = $categories->firstWhere('key', (string) $validated['category_key'])
            ?? $categories->firstWhere('key', 'umum');
        $records = $this->recordsFor($selectedCategory);

        $selectedRecord = $records->firstWhere('id', $validated['record_id'] ?? null);

        if (! $selectedRecord) {
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
            $whatsappUrl = $this->whatsappUrlForDuplicateReport($duplicateReport, $selectedCategory, $issue, $selectedRecord);

            if (! $whatsappUrl) {
                return back()->with('error', 'Nomor WhatsApp admin belum diatur.');
            }

            return $this->redirectToWhatsapp($whatsappUrl);
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

            $whatsappUrl = $this->whatsappUrlForDuplicateReport($duplicateReport, $selectedCategory, $issue, $selectedRecord);

            if (! $whatsappUrl) {
                return back()->with('error', 'Nomor WhatsApp admin belum diatur.');
            }

            return $this->redirectToWhatsapp($whatsappUrl);
        }

        $whatsappUrl = WiboostAdminContact::whatsappUrl($message);

        if (! $whatsappUrl) {
            return back()->with('error', 'Nomor WhatsApp admin belum diatur.');
        }

        return $this->redirectToWhatsapp($whatsappUrl);
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
            ['key' => 'umum', 'label' => 'Umum', 'record_type' => 'general', 'needs_record' => true],
        ];
    }

    protected function recordsFor(array $category)
    {
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
                ->map(function (Deposit $deposit) {
                    $created = $this->formatReportDate($deposit->created_at);

                    return [
                        'id' => 'deposit-' . $deposit->id,
                        'invoice' => $deposit->invoice_number,
                        'title' => 'Deposit Rp ' . number_format((float) ($deposit->amount ?? 0), 0, ',', '.'),
                        'meta' => $created,
                        'amount' => 'Rp ' . number_format((float) ($deposit->amount ?? 0), 0, ',', '.'),
                        'paidAmount' => $deposit->paid_amount ? 'Rp ' . number_format((float) $deposit->paid_amount, 0, ',', '.') : '-',
                        'status' => strtoupper((string) ($deposit->payment_status ?: '-')),
                        'method' => $deposit->display_payment_method,
                        'reference' => $deposit->tripay_reference ?: '-',
                        'created' => $created,
                    ];
                })
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
                $detail = WiboostOrderDetailData::forTransaction($transaction);

                return [
                    'id' => 'transaction-' . $transaction->id,
                    'categoryKey' => $this->normalizeRootSlug($rootCategory?->slug ?: $rootCategory?->name),
                    'invoice' => $transaction->invoice_number,
                    'title' => $detail['product'],
                    'meta' => strtoupper($detail['time'] . ' - ' . $detail['payment'] . ' - ' . $detail['status']),
                    'category' => $detail['category'],
                    'product' => $detail['product'],
                    'amount' => $detail['total'],
                    'status' => $detail['status'],
                    'payment' => $detail['payment'],
                    'target' => $detail['input_text'],
                    'providerId' => $detail['provider_id'],
                    'created' => $detail['time'],
                    'accountLines' => $this->accountLinesForTransaction($transaction),
                    'loginProofUrl' => $transaction->login_proof_path ? url(Storage::url($transaction->login_proof_path)) : null,
                ];
            })
            ->filter(fn (array $record) => ($category['record_type'] ?? null) === 'general'
                || $record['categoryKey'] === ($category['record_type'] ?? null))
            ->values();
    }

    protected function buildWhatsappMessage(array $category, string $issue, ?array $record, ?string $proofUrl): string
    {
        $lines = [
            'Halo Admin Wiboost, saya ingin melaporkan kendala.',
            '',
        ];

        $this->appendFilledLine($lines, 'Jenis Laporan', $category['label'] ?? 'Umum');
        $this->appendFilledLine($lines, 'Nama', auth()->user()->name ?? null);
        $this->appendFilledLine($lines, 'Email', auth()->user()->email ?? null);
        $this->appendFilledLine($lines, 'WhatsApp', auth()->user()->whatsapp ?? null);

        $lines[] = '';
        $lines[] = 'Kendala:';
        $lines[] = trim($issue);

        if ($this->hasFilledValue($proofUrl)) {
            $lines[] = '';
            $lines[] = 'Bukti Kendala:';
            $lines[] = trim((string) $proofUrl);
        }

        if (($category['key'] ?? null) === 'deposit') {
            if ($record) {
                $depositLines = [];
                $this->appendFilledLine($depositLines, 'Invoice', $record['invoice'] ?? null);
                $this->appendFilledLine($depositLines, 'Nominal Masuk Saldo', $record['amount'] ?? null);
                $this->appendFilledLine($depositLines, 'Jumlah Dibayar', $record['paidAmount'] ?? null);
                $this->appendFilledLine($depositLines, 'Metode', $record['method'] ?? null);
                $this->appendFilledLine($depositLines, 'Referensi QRIS', $record['reference'] ?? null);
                $this->appendFilledLine($depositLines, 'Waktu', $record['created'] ?? null);

                if ($depositLines !== []) {
                    $lines[] = '';
                    $lines[] = 'Data Deposit:';
                    array_push($lines, ...$depositLines);
                }
            }
        } elseif ($category['needs_record'] ?? false) {
            if ($record) {
                $orderLines = [];
                $this->appendFilledLine($orderLines, 'Invoice', $record['invoice'] ?? null);
                $this->appendFilledLine($orderLines, 'Kategori', $record['category'] ?? null);
                $this->appendFilledLine($orderLines, 'Produk', $record['product'] ?? null);
                $this->appendFilledLine($orderLines, 'Total Bayar', $record['amount'] ?? null);
                $this->appendFilledLine($orderLines, 'Metode', $record['payment'] ?? null);
                $this->appendFilledLine($orderLines, 'Status Pesanan', $record['status'] ?? null);
                $this->appendFilledLine($orderLines, 'ID Provider', $record['providerId'] ?? null);
                $this->appendFilledLine($orderLines, 'Waktu', $record['created'] ?? null);

                if ($orderLines !== []) {
                    $lines[] = '';
                    if ($this->hasFilledValue($record['loginProofUrl'] ?? null)) {
                        $lines[] = 'Bukti Login:';
                        $lines[] = trim((string) $record['loginProofUrl']);
                        $lines[] = '';
                    }

                    $lines[] = 'Data Pesanan:';
                    array_push($lines, ...$orderLines);
                }

                if (! empty($record['accountLines']) && is_array($record['accountLines'])) {
                    $lines[] = '';
                    $lines[] = 'Detail Akun: ';
                    array_push($lines, ...$record['accountLines']);
                }

                $this->appendInputBuyerLines($lines, $record['target'] ?? '-');
            }
        }

        array_push(
            $lines,
            '',
            'Mohon dibantu pengecekannya. Terima kasih.'
        );

        return implode("\n", $lines);
    }

    protected function accountLinesForTransaction(Transaction $transaction): array
    {
        $credentials = is_array($transaction->credential_data)
            ? $transaction->credential_data
            : (json_decode((string) $transaction->credential_data, true) ?: []);

        $lines = [];

        foreach ([
            'email' => 'Email',
            'password' => 'Password',
            'profile' => 'Profil',
            'pin' => 'PIN',
            'link' => 'Link Akses',
            'tutorial_link' => 'Link Tutorial',
        ] as $key => $label) {
            if ($this->hasFilledValue($credentials[$key] ?? null)) {
                $lines[] = $label . ': ' . trim((string) $credentials[$key]);
            }
        }

        return $lines;
    }

    protected function appendFilledLine(array &$lines, string $label, mixed $value): void
    {
        if (! $this->hasFilledValue($value)) {
            return;
        }

        $lines[] = $label . ': ' . trim((string) $value);
    }

    protected function hasFilledValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_array($value)) {
            return collect($value)->contains(fn ($item) => $this->hasFilledValue($item));
        }

        $value = trim((string) $value);
        $emptyValues = [
            '',
            '-',
            "\xE2\x80\x94",
            'n/a',
            'na',
            'null',
            'undefined',
            '[]',
            '{}',
            'tidak ada',
            'belum ada',
            'belum tersedia',
        ];

        return ! in_array(Str::lower($value), $emptyValues, true);
    }

    protected function whatsappUrlForDuplicateReport(AdminReport $report, array $category, string $issue, ?array $record): ?string
    {
        $proofUrl = $report->proof_url;

        if ($this->hasFilledValue($proofUrl) && ! Str::startsWith((string) $proofUrl, ['http://', 'https://'])) {
            $proofUrl = url($proofUrl);
        }

        $message = $this->buildWhatsappMessage($category, $issue, $record, $proofUrl);

        if ($report->whatsapp_message !== $message) {
            $report->update(['whatsapp_message' => $message]);
        }

        return WiboostAdminContact::whatsappUrl($message);
    }

    protected function redirectToWhatsapp(string $whatsappUrl)
    {
        return redirect()
            ->away($whatsappUrl)
            ->withCookie(Cookie::make('wiboost_after_admin_report', 'dashboard', 10, '/', null, false, false, false, 'Lax'));
    }

    protected function appendInputBuyerLines(array &$lines, ?string $target): void
    {
        $target = trim((string) $target);

        if ($target === '' || $target === '-') {
            return;
        }

        $inputLines = [];

        foreach (preg_split('/\R/', $target) ?: [] as $line) {
            $line = trim((string) $line);

            $lineValue = Str::contains($line, ':') ? Str::after($line, ':') : $line;

            if ($this->hasFilledValue($lineValue)) {
                $inputLines[] = $line;
            }
        }

        if ($inputLines === []) {
            return;
        }

        $lines[] = '';
        $lines[] = 'Input Pembeli:';
        array_push($lines, ...$inputLines);
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

    protected function formatReportDate($date): string
    {
        if (! $date) {
            return '-';
        }

        $months = [
            1 => 'JAN',
            2 => 'FEB',
            3 => 'MAR',
            4 => 'APR',
            5 => 'MEI',
            6 => 'JUN',
            7 => 'JUL',
            8 => 'AGU',
            9 => 'SEP',
            10 => 'OKT',
            11 => 'NOV',
            12 => 'DES',
        ];

        return $date->format('d') . ' '
            . ($months[(int) $date->format('n')] ?? Str::upper($date->format('M'))) . ' '
            . $date->format('Y, H:i');
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
