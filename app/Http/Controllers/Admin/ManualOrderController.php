<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\Request;

class ManualOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'product'])
            ->whereHas('product', function ($productQuery) {
                $productQuery->where('process_type', 'manual');
            })
            ->latest();

        if ($request->filled('search')) {
            $query->where(function ($innerQuery) use ($request) {
                $innerQuery->where('invoice_number', 'like', '%' . $request->search . '%')
                    ->orWhere('target_data', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($request) {
                        $userQuery->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $manualOrders = $query->paginate(15)->appends($request->all());

        return view('admin.manual-orders.index', compact('manualOrders'));
    }

    public function markAsComplete(Request $request, $id, OrderFulfillmentService $orderFulfillmentService)
    {
        $request->validate([
            'target_notes' => 'nullable|string|max:1000',
        ]);

        $transaction = Transaction::with(['product', 'user'])->findOrFail($id);

        if ($transaction->product?->process_type !== 'manual') {
            return back()->with('error', 'Pesanan ini bukan tipe manual.');
        }

        $orderFulfillmentService->markManualOrderCompleted($transaction, $request->target_notes);

        return back()->with('success', 'Pesanan manual berhasil ditandai selesai.');
    }
}
