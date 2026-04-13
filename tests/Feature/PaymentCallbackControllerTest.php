<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymentCallbackControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_midtrans_transaction_callback_marks_order_as_paid_and_routes_manual_order_to_queue(): void
    {
        config([
            'midtrans.server_key' => 'server-key-test',
        ]);

        $this->seedCategory(1, 'default-category');

        $user = User::factory()->create();

        $product = Product::create([
            'category_id' => 1,
            'provider_id' => null,
            'process_type' => 'manual',
            'name' => 'Boosting Manual',
            'slug' => 'boosting-manual',
            'description' => 'Layanan manual',
            'price' => 15000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $transaction = Transaction::create([
            'invoice_number' => 'WIB-CALLBACK-001',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 15000,
            'target_data' => 'username-instagram',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
        ]);

        $payload = $this->midtransPayload('WIB-CALLBACK-001', '15000.00', 'settlement', 'qris');

        $this->postJson(route('midtrans.callback'), $payload)
            ->assertOk()
            ->assertJson(['message' => 'Transaction callback handled successfully']);

        $transaction->refresh();

        $this->assertSame('paid', $transaction->payment_status);
        $this->assertSame('processing', $transaction->order_status);
        $this->assertSame('qris', $transaction->payment_method);
        $this->assertStringContainsString('antrean admin', (string) $transaction->target_notes);
    }

    public function test_midtrans_deposit_callback_is_idempotent_for_balance_and_wallet_history(): void
    {
        config([
            'midtrans.server_key' => 'server-key-test',
        ]);

        $user = User::factory()->create([
            'balance' => 0,
        ]);

        Deposit::create([
            'user_id' => $user->id,
            'invoice_number' => 'DEP-CALLBACK-001',
            'amount' => 50000,
            'payment_status' => 'unpaid',
        ]);

        $payload = $this->midtransPayload('DEP-CALLBACK-001', '50000.00', 'settlement', 'qris');

        $this->postJson(route('midtrans.callback'), $payload)->assertOk();
        $this->postJson(route('midtrans.callback'), $payload)->assertOk();

        $user->refresh();

        $this->assertSame(50000.0, (float) $user->balance);
        $this->assertDatabaseHas('deposits', [
            'invoice_number' => 'DEP-CALLBACK-001',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
        ]);
        $this->assertSame(1, DB::table('wallet_histories')->where('invoice_number', 'DEP-CALLBACK-001')->count());
    }

    public function test_midtrans_callback_rejects_invalid_signature(): void
    {
        config([
            'midtrans.server_key' => 'server-key-test',
        ]);

        $payload = $this->midtransPayload('WIB-INVALID-001', '10000.00', 'settlement', 'qris');
        $payload['signature_key'] = 'invalid-signature';

        $this->postJson(route('midtrans.callback'), $payload)
            ->assertForbidden()
            ->assertJson(['message' => 'Invalid signature']);
    }

    protected function midtransPayload(string $orderId, string $grossAmount, string $transactionStatus, string $paymentType): array
    {
        $statusCode = '200';

        return [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => $transactionStatus,
            'payment_type' => $paymentType,
            'signature_key' => hash('sha512', $orderId . $statusCode . $grossAmount . config('midtrans.server_key')),
        ];
    }

    protected function seedCategory(int $id, string $slug, ?string $name = null): void
    {
        DB::table('categories')->insert([
            'id' => $id,
            'name' => $name ?? ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'fulfillment_type' => 'manual_action',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
