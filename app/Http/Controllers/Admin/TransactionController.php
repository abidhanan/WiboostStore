<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use App\Services\OrderFulfillmentService;
use App\Services\TransactionPaymentSyncService;
use App\Support\WiboostCatalog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request, TransactionPaymentSyncService $paymentSyncService)
    {
        if ($request->boolean('refresh_api')) {
            return $this->refreshProviderStatusesFromIndex($request);
        }

        $this->syncMembershipDepositsToTransactions();

        foreach (Transaction::whereIn('payment_status', ['unpaid', 'pending'])->get() as $transaction) {
            $paymentSyncService->expireIfPastDue($transaction);
        }

        // Pastikan memuat relasi product.category agar bisa mengecek slug kategori
        $query = Transaction::with(['refund', 'user', 'product.category.parent.parent.parent'])->latest();
        $query->where(function ($transactionQuery) {
            $transactionQuery->where('invoice_number', 'like', 'MEM-%')
                ->orWhere('target_data', 'like', '%Membership Wiboost%')
                ->orWhere('response_data', 'like', '%membership%')
                ->orWhereHas('product', fn ($productQuery) => $productQuery->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()));
        });
        $transactionFilter = (string) $request->query('transaction_filter', 'all');
        $allowedTransactionFilters = ['all', 'pending', 'processing', 'success', 'failed'];

        if (! in_array($transactionFilter, $allowedTransactionFilters, true)) {
            $transactionFilter = 'all';
        }

        // 1. Pencarian
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhere('target_data', 'like', '%' . $request->search . '%')
                  ->orWhere('order_input_data', 'like', '%' . $request->search . '%')
                  ->orWhere('credential_data', 'like', '%' . $request->search . '%')
                  ->orWhere('response_data', 'like', '%' . $request->search . '%')
                  ->orWhere('target_notes', 'like', '%' . $request->search . '%')
                  ->orWhere('login_proof_path', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                          ->orWhere('email', 'like', '%' . $request->search . '%')
                          ->orWhere('whatsapp', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('product', function($productQuery) use ($request) {
                      $productQuery->where('name', 'like', '%' . $request->search . '%')
                          ->orWhere('provider_product_id', 'like', '%' . $request->search . '%')
                          ->orWhere('provider_id', 'like', '%' . $request->search . '%')
                          ->orWhere('provider_source', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // 2. Filter Bulan & Tahun
        $selectedMonth = $request->input('month', date('m'));
        $selectedYear = $request->input('year', date('Y'));

        $query->whereMonth('created_at', $selectedMonth)
              ->whereYear('created_at', $selectedYear);

        if ($transactionFilter !== 'all') {
            $query->where('order_status', $transactionFilter);
        }

        // Pagination
        $transactions = $query->paginate(20)->appends($request->all());

        return view('admin.transactions.index', compact('transactions', 'selectedMonth', 'selectedYear', 'transactionFilter'));
    }

    protected function syncMembershipDepositsToTransactions(): void
    {
        if (! Schema::hasTable('deposits') || ! Schema::hasTable('transactions')) {
            return;
        }

        Deposit::query()
            ->where(function ($query) {
                $query->where('invoice_number', 'like', 'MEM-%')
                    ->orWhere('payment_response', 'like', '%membership%');
            })
            ->whereNotIn('invoice_number', Transaction::query()->select('invoice_number'))
            ->oldest('id')
            ->chunkById(100, function ($deposits) {
                foreach ($deposits as $deposit) {
                    $paymentStatus = match ((string) $deposit->payment_status) {
                        'paid' => 'paid',
                        'failed' => 'failed',
                        default => 'unpaid',
                    };
                    $orderStatus = match ($paymentStatus) {
                        'paid' => 'success',
                        'failed' => 'failed',
                        default => 'pending',
                    };
                    $paidAmount = (float) ($deposit->paid_amount ?: 0);

                    $transaction = Transaction::firstOrCreate(
                        ['invoice_number' => $deposit->invoice_number],
                        [
                            'user_id' => $deposit->user_id,
                            'product_id' => null,
                            'product_name_snapshot' => 'Membership Wiboost 30 Hari',
                            'amount' => (float) $deposit->amount,
                            'paid_amount' => $paymentStatus === 'paid'
                                ? ($paidAmount > 0 ? $paidAmount : $deposit->tripay_total_payable)
                                : null,
                            'target_data' => 'Membership Wiboost 30 Hari',
                            'order_input_data' => [
                                'fields' => [
                                    [
                                        'name' => 'membership_period',
                                        'label' => 'Masa Membership',
                                        'value' => data_get($deposit->payment_response, 'membership_days', 30) . ' Hari',
                                        'target_summary' => true,
                                    ],
                                ],
                            ],
                            'target_notes' => $paymentStatus === 'paid'
                                ? 'Membership Wiboost 30 hari berhasil aktif.'
                                : 'Pembayaran membership Wiboost 30 hari.',
                            'response_data' => array_merge($deposit->payment_response ?? [], [
                                'purpose' => 'membership',
                                'membership_days' => (int) data_get($deposit->payment_response, 'membership_days', 30),
                                'source_deposit_id' => $deposit->id,
                            ]),
                            'payment_status' => $paymentStatus,
                            'order_status' => $orderStatus,
                            'payment_method' => $deposit->payment_method ?: 'tripay',
                            'snap_token' => $deposit->payment_url,
                        ]
                    );

                    if ($transaction->wasRecentlyCreated) {
                        $transaction->forceFill([
                            'created_at' => $deposit->created_at,
                            'updated_at' => $deposit->updated_at,
                        ])->saveQuietly();
                    }
                }
            });
    }

    public function updateStatus(Request $request, $id, OrderFulfillmentService $orderFulfillmentService)
    {
        abort_unless(in_array((int) $request->user()?->role_id, [User::ROLE_SUPER_ADMIN, User::ROLE_WAREHOUSE], true), 403, 'Hanya role gudang dan super admin yang bisa mengubah status pesanan.');

        $request->validate([
            'order_status' => 'required|in:pending,processing,success,failed'
        ]);

        $transaction = Transaction::with(['refund', 'product', 'user'])->findOrFail($id);

        if ($request->order_status === 'success') {
            $orderFulfillmentService->markOrderSuccessful($transaction);
        } elseif ($request->order_status === 'failed') {
            $orderFulfillmentService->markAsFailed(
                $transaction,
                'Pesanan ditandai gagal oleh admin. Saldo otomatis dikembalikan jika pembayaran sudah lunas.'
            );
        } else {
            $transaction->update([
                'order_status' => $request->order_status,
            ]);
        }

        return back()->with('success', 'Status pesanan ' . $transaction->invoice_number . ' berhasil diperbarui!');
    }

    public function refreshProviderStatuses()
    {
        try {
            $message = $this->runProviderStatusRefresh(afterResponse: true);

            return back()->with('success', $message);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal refresh status pesanan API: ' . $e->getMessage());
        }
    }

    private function refreshProviderStatusesFromIndex(Request $request)
    {
        $query = $request->query();
        unset($query['refresh_api']);

        try {
            $message = $this->runProviderStatusRefresh(afterResponse: true);

            return redirect()
                ->route('admin.transactions.index', $query)
                ->with('success', $message);
        } catch (Exception $e) {
            return redirect()
                ->route('admin.transactions.index', $query)
                ->with('error', 'Gagal refresh status pesanan API: ' . $e->getMessage());
        }
    }

    private function runProviderStatusRefresh(bool $afterResponse = false): string
    {
        if ($afterResponse) {
            app()->terminating(function () {
                Artisan::call('wiboost:check-provider-orders', [
                    '--limit' => 200,
                ]);
            });

            return 'Refresh status API sedang diproses. Silakan segarkan lagi sebentar lagi.';
        }

        Artisan::call('wiboost:check-provider-orders', [
            '--limit' => 200,
        ]);

        $output = trim(Artisan::output());
        $message = 'Status pesanan API berhasil dicek ulang.';

        if ($output !== '') {
            $message .= ' ' . Str::limit($output, 220);
        }

        return $message;
    }

    public function exportPdf(Request $request)
    {
        try {
            $month = $request->input('month', date('m'));
            $year = $request->input('year', date('Y'));

            // Laporan pendapatan hanya menghitung transaksi yang benar-benar sukses.
            $transactions = Transaction::with(['refund', 'user', 'product'])
                ->where('payment_status', 'paid')
                ->where('order_status', 'success')
                ->whereNull('refunded_at')
                ->doesntHave('refund')
                ->where(function ($transactionQuery) {
                    $transactionQuery->where('invoice_number', 'like', 'MEM-%')
                        ->orWhereHas('product', fn ($productQuery) => $productQuery->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()));
                })
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->latest()
                ->get();

            $totalRevenue = $transactions->sum('display_total_payable');

            // Setup bahasa ke Indonesia untuk nama bulan
            Carbon::setLocale('id');
            $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

            // Load view untuk PDF (Pastikan file resources/views/admin/transactions/pdf.blade.php ADA)
            $pdf = Pdf::loadView('admin.transactions.pdf', compact('transactions', 'totalRevenue', 'monthName'));
            
            // Setel ukuran kertas (A4 Landscape agar muat banyak kolom)
            $pdf->setPaper('A4', 'landscape');

            // Format nama file agar rapi
            $fileName = 'Laporan_Cuan_Wiboost_' . str_replace(' ', '_', $monthName) . '.pdf';

            // UBAH KE STREAM: Ini akan membuka PDF di tab browser, BUKAN mendownload diam-diam
            return $pdf->stream($fileName);

        } catch (Exception $e) {
            // HARD STOP: Jika error, paksa munculkan teks error di layar putih
            dd('HALO ADMIN, ADA ERROR CETAK PDF: ' . $e->getMessage() . ' | DI BARIS: ' . $e->getLine());
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $month = $request->input('month', date('m'));
            $year = $request->input('year', date('Y'));

            $transactions = Transaction::with(['refund', 'user', 'product'])
                ->where('payment_status', 'paid')
                ->where('order_status', 'success')
                ->whereNull('refunded_at')
                ->doesntHave('refund')
                ->where(function ($transactionQuery) {
                    $transactionQuery->where('invoice_number', 'like', 'MEM-%')
                        ->orWhereHas('product', fn ($productQuery) => $productQuery->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()));
                })
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->latest()
                ->get();

            $totalRevenue = $transactions->sum('display_total_payable');

            Carbon::setLocale('id');
            $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
            $fileName = 'Laporan_Wiboost_' . str_replace(' ', '_', $monthName) . '.xlsx';
            $workbook = $this->buildTransactionWorkbook($transactions, $totalRevenue, $monthName);

            return response($workbook)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                ->header('Content-Length', strlen($workbook))
                ->header('Cache-Control', 'max-age=0');
        } catch (Exception $e) {
            dd('HALO ADMIN, ADA ERROR CETAK EXCEL: ' . $e->getMessage() . ' | DI BARIS: ' . $e->getLine());
        }
    }

    private function buildTransactionWorkbook($transactions, $totalRevenue, string $monthName): string
    {
        $rows = [];
        $rows[] = [1, [
            ['A', 'Wiboost Store - Laporan Transaksi', 'text', 1],
        ]];
        $rows[] = [2, [
            ['A', 'Periode: ' . $monthName, 'text', 5],
        ]];
        $rows[] = [4, [
            ['A', 'No', 'text', 2],
            ['B', 'Waktu Transaksi', 'text', 2],
            ['C', 'Invoice', 'text', 2],
            ['D', 'Nama Pelanggan', 'text', 2],
            ['E', 'Layanan / Produk', 'text', 2],
            ['F', 'Total Pendapatan', 'text', 2],
        ]];

        $rowNumber = 5;
        foreach ($transactions as $index => $trx) {
            $rows[] = [$rowNumber, [
                ['A', $index + 1, 'number', 0],
                ['B', $trx->created_at?->format('d/m/Y H:i') ?? '-', 'text', 0],
                ['C', $trx->invoice_number ?? '-', 'text', 0],
                ['D', $trx->user->name ?? 'Guest', 'text', 0],
                ['E', $trx->display_product_name, 'text', 0],
                ['F', (float) ($trx->display_total_payable ?? 0), 'number', 3],
            ]];
            $rowNumber++;
        }

        if ($transactions->isEmpty()) {
            $rows[] = [$rowNumber, [
                ['A', 'Tidak ada transaksi sukses pada periode ini.', 'text', 0],
            ]];
            $rowNumber++;
        }

        $rows[] = [$rowNumber, [
            ['A', 'TOTAL PENDAPATAN', 'text', 4],
            ['F', (float) $totalRevenue, 'number', 4],
        ]];

        $sheetRows = collect($rows)->map(function ($row) {
            [$number, $cells] = $row;
            $cellXml = collect($cells)->map(fn ($cell) => $this->xlsxCell($cell[0] . $number, $cell[1], $cell[2], $cell[3]))->implode('');

            return '<row r="' . $number . '">' . $cellXml . '</row>';
        })->implode('');

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<cols>'
            . '<col min="1" max="1" width="8" customWidth="1"/>'
            . '<col min="2" max="2" width="20" customWidth="1"/>'
            . '<col min="3" max="3" width="24" customWidth="1"/>'
            . '<col min="4" max="4" width="28" customWidth="1"/>'
            . '<col min="5" max="5" width="58" customWidth="1"/>'
            . '<col min="6" max="6" width="20" customWidth="1"/>'
            . '</cols>'
            . '<sheetData>' . $sheetRows . '</sheetData>'
            . '<mergeCells count="3"><mergeCell ref="A1:F1"/><mergeCell ref="A2:F2"/><mergeCell ref="A' . $rowNumber . ':E' . $rowNumber . '"/></mergeCells>'
            . '</worksheet>';

        return $this->zipStored([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>',
            'docProps/app.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>Wiboost Store</Application></Properties>',
            'docProps/core.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:title>Laporan Transaksi Wiboost</dc:title><dc:creator>Wiboost Store</dc:creator><cp:lastModifiedBy>Wiboost Store</cp:lastModifiedBy></cp:coreProperties>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Transaksi" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => $this->xlsxStyles(),
            'xl/worksheets/sheet1.xml' => $sheet,
        ]);
    }

    private function xlsxCell(string $cell, mixed $value, string $type = 'text', int $style = 0): string
    {
        $styleAttribute = $style > 0 ? ' s="' . $style . '"' : '';

        if ($type === 'number') {
            return '<c r="' . $cell . '"' . $styleAttribute . '><v>' . (float) $value . '</v></c>';
        }

        return '<c r="' . $cell . '" t="inlineStr"' . $styleAttribute . '><is><t>' . $this->xmlSafe($value) . '</t></is></c>';
    }

    private function xlsxStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="&quot;Rp&quot; #,##0"/></numFmts>'
            . '<fonts count="3"><font><sz val="11"/><name val="Arial"/></font><font><b/><sz val="18"/><color rgb="FF5A76C8"/><name val="Arial"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Arial"/></font></fonts>'
            . '<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF5A76C8"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFE6FFF7"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFBDE0FE"/></left><right style="thin"><color rgb="FFBDE0FE"/></right><top style="thin"><color rgb="FFBDE0FE"/></top><bottom style="thin"><color rgb="FFBDE0FE"/></bottom><diagonal/></border></borders>'
            . '<cellXfs count="6">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment vertical="top" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"><alignment vertical="top"/></xf>'
            . '<xf numFmtId="164" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1"><alignment vertical="top"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment vertical="center"/></xf>'
            . '</cellXfs>'
            . '</styleSheet>';
    }

    private function zipStored(array $files): string
    {
        $localFiles = '';
        $centralDirectory = '';
        $offset = 0;
        $time = 0;
        $date = 0;

        foreach ($files as $name => $content) {
            $nameLength = strlen($name);
            $size = strlen($content);
            $crc = crc32($content);
            if ($crc < 0) {
                $crc += 4294967296;
            }

            $localHeader = pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, $time, $date, $crc, $size, $size, $nameLength, 0) . $name;
            $localFiles .= $localHeader . $content;

            $centralDirectory .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, $time, $date, $crc, $size, $size, $nameLength, 0, 0, 0, 0, 0, $offset) . $name;
            $offset += strlen($localHeader) + $size;
        }

        $entryCount = count($files);
        $centralSize = strlen($centralDirectory);
        $end = pack('VvvvvVVv', 0x06054b50, 0, 0, $entryCount, $entryCount, $centralSize, $offset, 0);

        return $localFiles . $centralDirectory . $end;
    }

    private function xmlSafe(mixed $value): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $value) ?? '';

        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

}
