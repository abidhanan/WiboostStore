<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Menampilkan daftar semua transaksi dengan fitur pencarian dan pagination.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'product'])->latest();

        // Fitur Pencarian berdasarkan Invoice atau Nama User
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }

        // Gunakan paginate (20 data per halaman) agar tidak memberatkan server
        $transactions = $query->paginate(20);

        return view('admin.transactions.index', compact('transactions'));
    }

    /**
     * Memperbarui status pesanan secara manual via dropdown.
     */
    public function updateStatus(Request $request, $id)
    {
        // Validasi input status
        $request->validate([
            'order_status' => 'required|in:pending,processing,success,failed'
        ]);

        $transaction = Transaction::findOrFail($id);
        
        $transaction->update([
            'order_status' => $request->order_status
        ]);

        return back()->with('success', 'Status pesanan ' . $transaction->invoice_number . ' berhasil diperbarui menjadi ' . strtoupper($request->order_status) . '!');
    }

    /**
     * Export data transaksi bulanan ke format PDF
     */
    public function exportPdf(Request $request)
    {
        // Ambil input bulan dan tahun, default ke bulan dan tahun saat ini
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        // Ambil data transaksi yang LUNAS (paid) pada bulan & tahun tersebut
        $transactions = Transaction::with(['user', 'product'])
            ->where('payment_status', 'paid')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->latest()
            ->get();

        // Hitung total pendapatan
        $totalRevenue = $transactions->sum('amount');

        // Set bahasa Carbon ke Indonesia untuk nama bulan
        Carbon::setLocale('id');
        $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

        // Load view PDF
        $pdf = Pdf::loadView('admin.transactions.pdf', compact('transactions', 'totalRevenue', 'monthName'));
        
        // Atur ukuran kertas
        $pdf->setPaper('A4', 'landscape');

        // Download otomatis
        return $pdf->download('Laporan_Cuan_Wiboost_'.$monthName.'.pdf');
    }

    /**
     * Halaman Laporan (Akan kita kembangkan untuk analitik keuangan)
     */
    public function reports()
    {
        $totalRevenue = Transaction::where('payment_status', 'paid')
                                   ->where('order_status', 'success')
                                   ->sum('amount');
        
        $totalOrders = Transaction::count();

        return view('admin.transactions.reports', compact('totalRevenue', 'totalOrders'));
    }
}