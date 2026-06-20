<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\TransactionPaymentSyncService;
use Illuminate\Support\Facades\Auth;

class TransactionHistoryController extends Controller
{
    public function index(TransactionPaymentSyncService $paymentSyncService)
    {
        $pendingTransactions = Transaction::where('user_id', Auth::id())
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->get();

        foreach ($pendingTransactions as $transaction) {
            $paymentSyncService->expireIfPastDue($transaction);
        }

        $transactions = Transaction::where('user_id', Auth::id())
                                    ->with('product.category.parent.parent.parent')
                                    ->orderBy('created_at', 'desc')
                                    ->paginate(10);

        return view('user.history', compact('transactions'));
    }
}
