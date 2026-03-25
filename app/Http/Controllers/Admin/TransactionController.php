<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // Pastikan memuat relasi product.category agar bisa mengecek slug kategori
        $query = Transaction::with(['user', 'product.category'])->latest();

        // 1. Pencarian
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
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

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'order_status' => 'required|in:pending,processing,success,failed'
        ]);

        $transaction = Transaction::findOrFail($id);
        
        $transaction->update([
            'order_status' => $request->order_status
        ]);

        return back()->with('success', 'Status pesanan ' . $transaction->invoice_number . ' berhasil diperbarui!');
    }

    public function exportPdf(Request $request)
    {
        try {
            $month = $request->input('month', date('m'));
            $year = $request->input('year', date('Y'));

            // Hanya ambil transaksi yang sudah LUNAS ('paid')
            $transactions = Transaction::with(['user', 'product'])
                ->where('payment_status', 'paid')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->latest()
                ->get();

            $totalRevenue = $transactions->sum('amount');

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

    public function reports()
    {
        // Hitung total pendapatan riil (Hanya yang status bayar Lunas & order Sukses)
        $totalRevenue = Transaction::where('payment_status', 'paid')
                                   ->where('order_status', 'success')
                                   ->sum('amount');
        
        // Hitung total semua orderan masuk
        $totalOrders = Transaction::count();

        return view('admin.transactions.reports', compact('totalRevenue', 'totalOrders'));
    }
}