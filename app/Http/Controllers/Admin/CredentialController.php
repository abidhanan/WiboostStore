<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCredential;
use App\Models\Transaction;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    public function index(Product $product)
    {
        if ($product->process_type !== 'account') {
            return redirect()->route('admin.products.index')->with('error', 'Produk ini tidak membutuhkan manajemen stok akun premium.');
        }

        $credentials = $product->credentials()
            ->with('createdBy')
            ->latest()
            ->get();

        return view('admin.credentials.index', compact('product', 'credentials'));
    }

    public function store(Request $request, Product $product, OrderFulfillmentService $orderFulfillmentService)
    {
        if ($product->process_type !== 'account') {
            return redirect()->route('admin.products.index')->with('error', 'Produk ini tidak membutuhkan manajemen stok akun premium.');
        }

        $request->validate([
            'max_usage' => 'required|integer|min:1',
            'data_1' => 'nullable|string',
            'data_2' => 'nullable|string',
            'data_3' => 'nullable|string',
            'data_4' => 'nullable|string',
            'data_5' => 'nullable|string',
            'notes' => 'nullable|string|max:2000',
            'tutorial_link' => 'nullable|url|max:500',
            'requires_login_proof' => 'nullable|boolean',
        ]);

        $data1 = $request->data_1 ?? '-';
        $maxUsage = (int) $request->max_usage;

        $credential = $product->credentials()->create([
            'created_by_user_id' => $request->user()?->id,
            'data_1' => $data1,
            'data_2' => $request->data_2,
            'data_3' => $request->data_3,
            'data_4' => $request->data_4,
            'data_5' => $request->data_5,
            'notes' => $request->notes,
            'tutorial_link' => $request->tutorial_link,
            'needs_otp' => $request->boolean('needs_otp'),
            'requires_login_proof' => $request->boolean('requires_login_proof'),
            'max_usage' => $maxUsage,
            'current_usage' => 0,
            'is_active' => true,
        ]);

        $pendingTransactions = Transaction::where('product_id', $product->id)
            ->where('payment_status', 'paid')
            ->where('order_status', 'pending')
            ->orderBy('created_at')
            ->get();

        $fulfilledCount = 0;

        foreach ($pendingTransactions as $transaction) {
            $orderFulfillmentService->handlePaidTransaction($transaction->fresh(['product', 'user']));

            if ($transaction->fresh()?->order_status === 'success') {
                $fulfilledCount++;
            }
        }

        $message = 'Stok data berhasil ditambahkan ke gudang.';

        if ($fulfilledCount > 0) {
            $message .= " {$fulfilledCount} pesanan pending otomatis ikut diproses.";
        }

        return back()->with('success', $message);
    }

    public function destroy(ProductCredential $credential)
    {
        $credential->delete();

        return back()->with('success', 'Stok data berhasil dihapus.');
    }
}
