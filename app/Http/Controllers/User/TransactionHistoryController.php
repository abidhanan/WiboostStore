<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\OrderFulfillmentService;
use Illuminate\Support\Facades\Auth;
use Throwable;

class TransactionHistoryController extends Controller
{
    public function index(OrderFulfillmentService $orderFulfillmentService)
    {
        $this->syncRecentProviderOrders($orderFulfillmentService);

        // Mengambil transaksi milik user yang login, diurutkan dari terbaru
        // Menggunakan paginate() agar fungsi links() di view berjalan normal
        $transactions = Transaction::where('user_id', Auth::id())
                                    ->where(function ($query) {
                                        $query->where('payment_method', '!=', 'tripay')
                                            ->orWhereNull('payment_method')
                                            ->orWhereNotIn('payment_status', ['unpaid', 'pending'])
                                            ->orWhereNull('payment_status');
                                    })
                                    ->with('product.category.parent.parent.parent') 
                                    ->orderBy('created_at', 'desc') 
                                    ->paginate(10); 

        return view('user.history', compact('transactions'));
    }

    private function syncRecentProviderOrders(OrderFulfillmentService $orderFulfillmentService): void
    {
        Transaction::query()
            ->with(['product', 'user'])
            ->where('user_id', Auth::id())
            ->where('payment_status', 'paid')
            ->whereIn('order_status', ['pending', 'processing'])
            ->whereHas('product', function ($query) {
                $query->where('process_type', 'api')
                    ->whereIn('provider_source', ['digiflazz', 'ordersosmed']);
            })
            ->latest()
            ->limit(10)
            ->get()
            ->each(function (Transaction $transaction) use ($orderFulfillmentService) {
                try {
                    $orderFulfillmentService->syncProviderStatus($transaction);
                } catch (Throwable $exception) {
                    report($exception);
                }
            });
    }
}
