<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

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