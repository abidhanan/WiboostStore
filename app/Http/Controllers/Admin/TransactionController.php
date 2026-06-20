<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\OrderFulfillmentService;
use App\Services\TransactionPaymentSyncService;
use App\Support\WiboostCatalog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request, TransactionPaymentSyncService $paymentSyncService)
    {
        if ($request->boolean('refresh_api')) {
            return $this->refreshProviderStatusesFromIndex($request);
        }

        foreach (Transaction::whereIn('payment_status', ['unpaid', 'pending'])->get() as $transaction) {
            $paymentSyncService->expireIfPastDue($transaction);
        }

        // Pastikan memuat relasi product.category agar bisa mengecek slug kategori
        $query = Transaction::with(['user', 'product.category.parent.parent'])->latest();
        $query->whereHas('product', fn ($productQuery) => $productQuery->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()));
        // 1. Pencarian
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhere('target_data', 'like', '%' . $request->search . '%')
                  ->orWhere('order_input_data', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // 2. Filter Bulan & Tahun
        $selectedMonth = $request->input('month', date('m'));
        $selectedYear = $request->input('year', date('Y'));

        $query->whereMonth('created_at', $selectedMonth)
              ->whereYear('created_at', $selectedYear);

        // Pagination
        $transactions = $query->paginate(20)->appends($request->all());

        return view('admin.transactions.index', compact('transactions', 'selectedMonth', 'selectedYear'));
    }

    public function updateStatus(Request $request, $id, OrderFulfillmentService $orderFulfillmentService)
    {
        $request->validate([
            'order_status' => 'required|in:pending,processing,success,failed'
        ]);

        $transaction = Transaction::with(['product', 'user'])->findOrFail($id);

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
            $transactions = Transaction::with(['user', 'product'])
                ->where('payment_status', 'paid')
                ->where('order_status', 'success')
                ->whereHas('product', fn ($productQuery) => $productQuery->whereIn('process_type', WiboostCatalog::activeProductProcessTypes()))
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

}
