<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        // Mengambil SEMUA transaksi dari semua user, urut dari yang paling baru
        $transactions = Transaction::with(['user', 'product'])
                                   ->latest()
                                   ->get();

        return view('admin.transactions.index', compact('transactions'));
    }

    /**
     * Memperbarui status pesanan secara manual.
     */
    public function updateStatus(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        // Validasi status yang masuk
        $request->validate([
            'status' => 'required|in:success,failed,processing,pending'
        ]);

        $transaction->update([
            'order_status' => $request->status
        ]);

        return back()->with('success', 'Status pesanan ' . $transaction->invoice_number . ' berhasil diperbarui menjadi ' . strtoupper($request->status));
    }

    public function reports()
    {
        // Placeholder untuk fitur laporan ke depannya
        return view('admin.transactions.reports');
    }
}