<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    /**
     * Menampilkan daftar semua top up / deposit.
     */
    public function index(Request $request)
    {
        $query = Deposit::with('user')->latest();

        // Fitur Pencarian
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }

        $deposits = $query->paginate(20);

        return view('admin.deposits.index', compact('deposits'));
    }

    /**
     * Update status deposit (Berguna untuk Approve transfer manual)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,failed,unpaid'
        ]);

        $deposit = Deposit::findOrFail($id);

        // Jika admin mengubah status menjadi LUNAS, dan sebelumnya belum lunas
        if ($request->payment_status == 'paid' && $deposit->payment_status != 'paid') {
            $deposit->update(['payment_status' => 'paid', 'payment_method' => 'manual_admin']);
            
            // Tambahkan saldo ke user
            $user = User::find($deposit->user_id);
            $user->increment('balance', $deposit->amount);

            return back()->with('success', 'Deposit disetujui! Saldo Rp ' . number_format($deposit->amount, 0, ',', '.') . ' telah ditambahkan ke akun ' . $user->name);
        }

        // Jika admin membatalkan (mengubah jadi failed)
        $deposit->update(['payment_status' => $request->payment_status]);
        return back()->with('success', 'Status deposit berhasil diubah.');
    }
}