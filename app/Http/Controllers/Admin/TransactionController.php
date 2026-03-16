<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // Pastikan memuat relasi product.category agar bisa mengecek slug kategori
        $query = Transaction::with(['user', 'product.category'])->latest();

        // 1. Pencarian
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
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
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $transactions = Transaction::with(['user', 'product'])
            ->where('payment_status', 'paid')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->latest()
            ->get();

        $totalRevenue = $transactions->sum('amount');

        Carbon::setLocale('id');
        $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

        $pdf = Pdf::loadView('admin.transactions.pdf', compact('transactions', 'totalRevenue', 'monthName'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('Laporan_Cuan_Wiboost_'.$monthName.'.pdf');
    }

    public function reports()
    {
        $totalRevenue = Transaction::where('payment_status', 'paid')
                                   ->where('order_status', 'success')
                                   ->sum('amount');
        
        $totalOrders = Transaction::count();

        return view('admin.transactions.reports', compact('totalRevenue', 'totalOrders'));
    }
}