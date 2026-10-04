<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCredential;
use App\Models\Transaction;
use App\Services\OrderFulfillmentService;
use App\Support\WiboostAdminContact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CredentialController extends Controller
{
    public function index(Request $request, Product $product)
    {
        if ($product->process_type !== 'account') {
            return redirect()->route('admin.products.index')->with('error', 'Produk ini tidak membutuhkan manajemen stok akun premium.');
        }

        $credentials = $product->credentials()
            ->with('createdBy')
            ->latest()
            ->get();
        $adminOtpAdmins = WiboostAdminContact::otpAdmins();
        $editingCredentialId = (int) ($request->query('edit')
            ?: $request->session()->getOldInput('editing_credential_id'));
        $editingCredential = $editingCredentialId > 0
            ? $credentials->firstWhere('id', $editingCredentialId)
            : null;

        return response()
            ->view('admin.credentials.index', compact('product', 'credentials', 'adminOtpAdmins', 'editingCredential'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache');
    }

    public function store(Request $request, Product $product, OrderFulfillmentService $orderFulfillmentService)
    {
        if ($product->process_type !== 'account') {
            return redirect()->route('admin.products.index')->with('error', 'Produk ini tidak membutuhkan manajemen stok akun premium.');
        }

        $adminOtpAdmins = WiboostAdminContact::otpAdmins();
        $adminOtpWhatsapps = collect($adminOtpAdmins)->pluck('number')->all();

        $request->validate([
            'max_usage' => 'required|integer|min:1',
            'data_1' => 'nullable|string',
            'data_2' => 'nullable|string',
            'data_3' => 'nullable|string',
            'data_4' => 'nullable|string',
            'data_5' => 'nullable|string',
            'notes' => 'nullable|string|max:2000',
            'tutorial_link' => 'nullable|url|max:500',
            'needs_otp' => 'nullable|boolean',
            'otp_admin_whatsapp' => [
                'nullable',
                Rule::requiredIf(fn () => $request->boolean('needs_otp')),
                Rule::in($adminOtpWhatsapps),
            ],
            'requires_login_proof' => 'nullable|boolean',
            'requires_logout_proof' => 'nullable|boolean',
            'duration_value' => 'nullable|required_if:requires_logout_proof,1|integer|min:1',
            'duration_unit' => 'nullable|required_with:duration_value|in:day,week,month,year',
        ], [
            'otp_admin_whatsapp.required' => 'Pilih nomor Admin OTP yang akan menerima permintaan pelanggan.',
            'otp_admin_whatsapp.in' => 'Nomor Admin OTP yang dipilih tidak lagi tersedia. Pilih nomor lain.',
            'duration_value.required_if' => 'Durasi wajib diisi apabila Unggah Bukti Keluar diaktifkan.',
        ]);

        $data1 = $request->data_1 ?? '-';
        $maxUsage = (int) $request->max_usage;
        $requiresLogoutProof = $request->boolean('requires_logout_proof');
        $durationValue = $requiresLogoutProof && $request->filled('duration_value')
            ? (int) $request->duration_value
            : null;
        $selectedOtpAdmin = collect($adminOtpAdmins)->firstWhere('number', $request->input('otp_admin_whatsapp'));

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
            'otp_admin_whatsapp' => $request->boolean('needs_otp')
                ? $request->input('otp_admin_whatsapp')
                : null,
            'otp_admin_name' => $request->boolean('needs_otp')
                ? ($selectedOtpAdmin['name'] ?? null)
                : null,
            'requires_login_proof' => $request->boolean('requires_login_proof'),
            'requires_logout_proof' => $requiresLogoutProof,
            'max_usage' => $maxUsage,
            'duration_value' => $durationValue,
            'duration_unit' => $durationValue ? ($request->duration_unit ?: 'day') : null,
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

        return redirect()
            ->route('admin.credentials.index', array_filter([
                'product' => $product->id,
                'created' => $credential->id,
                'return_to' => $request->input('return_to'),
            ]))
            ->with('success', $message)
            ->setStatusCode(303);
    }

    public function destroy(ProductCredential $credential)
    {
        $credential->delete();

        return back()->with('success', 'Stok data berhasil dihapus.');
    }

    public function update(Request $request, ProductCredential $credential)
    {
        $credential->loadMissing('product');

        if ($credential->product?->process_type !== 'account') {
            return redirect()->route('admin.products.index')->with('error', 'Produk ini tidak membutuhkan manajemen stok akun premium.');
        }

        $adminOtpAdmins = WiboostAdminContact::otpAdmins();
        $adminOtpWhatsapps = collect($adminOtpAdmins)->pluck('number')->all();

        $request->validateWithBag('editCredential', [
            'editing_credential_id' => ['required', Rule::in([(string) $credential->id, $credential->id])],
            'max_usage' => ['required', 'integer', 'min:' . max(1, (int) $credential->current_usage)],
            'data_1' => ['nullable', 'string'],
            'data_2' => ['nullable', 'string'],
            'data_3' => ['nullable', 'string'],
            'data_4' => ['nullable', 'string'],
            'data_5' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'tutorial_link' => ['nullable', 'url', 'max:500'],
            'needs_otp' => ['nullable', 'boolean'],
            'otp_admin_whatsapp' => [
                'nullable',
                Rule::requiredIf(fn () => $request->boolean('needs_otp')),
                Rule::in($adminOtpWhatsapps),
            ],
            'requires_login_proof' => ['nullable', 'boolean'],
            'requires_logout_proof' => ['nullable', 'boolean'],
            'duration_value' => ['nullable', 'required_if:requires_logout_proof,1', 'integer', 'min:1'],
            'duration_unit' => ['nullable', 'required_with:duration_value', Rule::in(['day', 'week', 'month', 'year'])],
        ], [
            'max_usage.min' => 'Batas penggunaan tidak boleh lebih kecil dari jumlah yang sudah terpakai.',
            'otp_admin_whatsapp.required' => 'Pilih nomor Admin OTP yang akan menerima permintaan pelanggan.',
            'otp_admin_whatsapp.in' => 'Nomor Admin OTP yang dipilih tidak lagi tersedia. Pilih nomor lain.',
            'duration_value.required_if' => 'Durasi wajib diisi apabila Unggah Bukti Keluar diaktifkan.',
        ]);

        $requiresLogoutProof = $request->boolean('requires_logout_proof');
        $durationValue = $requiresLogoutProof && $request->filled('duration_value')
            ? (int) $request->input('duration_value')
            : null;
        $selectedOtpAdmin = collect($adminOtpAdmins)->firstWhere('number', $request->input('otp_admin_whatsapp'));

        $credential->update([
            'data_1' => $request->filled('data_1') ? $request->input('data_1') : '-',
            'data_2' => $request->input('data_2'),
            'data_3' => $request->input('data_3'),
            'data_4' => $request->input('data_4'),
            'data_5' => $request->input('data_5'),
            'notes' => $request->input('notes'),
            'tutorial_link' => $request->input('tutorial_link'),
            'needs_otp' => $request->boolean('needs_otp'),
            'otp_admin_whatsapp' => $request->boolean('needs_otp')
                ? $request->input('otp_admin_whatsapp')
                : null,
            'otp_admin_name' => $request->boolean('needs_otp')
                ? ($selectedOtpAdmin['name'] ?? null)
                : null,
            'requires_login_proof' => $request->boolean('requires_login_proof'),
            'requires_logout_proof' => $requiresLogoutProof,
            'max_usage' => (int) $request->input('max_usage'),
            'duration_value' => $durationValue,
            'duration_unit' => $durationValue ? ($request->input('duration_unit') ?: 'day') : null,
        ]);

        return redirect()
            ->route('admin.credentials.index', array_filter([
                'product' => $credential->product_id,
                'updated' => $credential->id,
                'return_to' => $request->input('return_to'),
            ]))
            ->with('success', 'Stok data berhasil diperbarui.')
            ->setStatusCode(303);
    }
}
