<?php

namespace Tests\Feature;

use App\Mail\OrderSuccessMail;
use App\Models\Deposit;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentCallbackControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_tripay_transaction_callback_marks_order_as_paid_and_routes_manual_order_to_queue(): void
    {
        config([
            'tripay.private_key' => 'private-key-test',
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
            'amount' => 14000,
            'target_data' => 'username-instagram',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
        ]);

        $payload = $this->tripayPayload('WIB-CALLBACK-001', 15000, 'PAID', 'QRIS');

        $this->postTripayCallback($payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        $transaction->refresh();

        $this->assertSame('paid', $transaction->payment_status);
        $this->assertSame('processing', $transaction->order_status);
        $this->assertSame('QRIS', $transaction->payment_method);
        $this->assertSame(15000.0, (float) $transaction->amount);
        $this->assertStringContainsString('antrean admin', (string) $transaction->target_notes);
    }

    public function test_tripay_deposit_callback_is_idempotent_for_balance_and_wallet_history(): void
    {
        config([
            'tripay.private_key' => 'private-key-test',
        ]);

        $user = User::factory()->create([
            'balance' => 0,
        ]);

        Deposit::create([
            'user_id' => $user->id,
            'invoice_number' => 'DEP-CALLBACK-001',
            'amount' => 45000,
            'payment_status' => 'unpaid',
        ]);

        $payload = $this->tripayPayload('DEP-CALLBACK-001', 50000, 'PAID', 'QRIS');

        $this->postTripayCallback($payload)->assertOk();
        $this->postTripayCallback($payload)->assertOk();

        $user->refresh();

        $this->assertSame(50000.0, (float) $user->balance);
        $this->assertDatabaseHas('deposits', [
            'invoice_number' => 'DEP-CALLBACK-001',
            'payment_status' => 'paid',
            'payment_method' => 'QRIS',
            'amount' => 50000,
        ]);
        $this->assertSame(1, DB::table('wallet_histories')->where('invoice_number', 'DEP-CALLBACK-001')->count());
        $this->assertDatabaseHas('wallet_histories', [
            'invoice_number' => 'DEP-CALLBACK-001',
            'amount' => 50000,
        ]);
    }

    public function test_tripay_callback_rejects_invalid_signature(): void
    {
        config([
            'tripay.private_key' => 'private-key-test',
        ]);

        $payload = $this->tripayPayload('WIB-INVALID-001', 10000, 'PAID', 'QRIS');

        $this->call('POST', route('tripay.callback'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CALLBACK_EVENT' => 'payment_status',
            'HTTP_X_CALLBACK_SIGNATURE' => 'invalid-signature',
        ], json_encode($payload))
            ->assertForbidden()
            ->assertJson(['success' => false, 'message' => 'Invalid signature']);
    }

    public function test_admin_marking_non_manual_order_success_sends_success_email(): void
    {
        Mail::fake();

        $this->seedCategory(1, 'top-up-game');
        DB::table('roles')->insertOrIgnore([
            'id' => 1,
            'name' => 'Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin = User::factory()->create(['role_id' => 1]);
        $buyer = User::factory()->create();

        $product = Product::create([
            'category_id' => 1,
            'provider_id' => 'digiflazz',
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ML86',
            'process_type' => 'api',
            'name' => 'Mobile Legends 86 Diamonds',
            'slug' => 'mobile-legends-86-diamonds',
            'description' => 'Produk game',
            'price' => 12000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $transaction = Transaction::create([
            'invoice_number' => 'WIB-ADMIN-SUCCESS',
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'amount' => 12000,
            'target_data' => '123456789 (1234)',
            'payment_status' => 'paid',
            'order_status' => 'processing',
            'payment_method' => 'wallet',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.transactions.update', $transaction->id), [
                'order_status' => 'success',
            ])
            ->assertRedirect();

        $transaction->refresh();

        $this->assertSame('success', $transaction->order_status);
        Mail::assertSent(OrderSuccessMail::class, fn (OrderSuccessMail $mail) => $mail->transaction->is($transaction));
    }

    protected function tripayPayload(string $merchantRef, int $amount, string $status, string $paymentMethod): array
    {
        return [
            'reference' => 'T' . substr(md5($merchantRef), 0, 10),
            'merchant_ref' => $merchantRef,
            'payment_method' => $paymentMethod,
            'payment_method_code' => $paymentMethod,
            'total_amount' => $amount,
            'status' => $status,
        ];
    }

    protected function postTripayCallback(array $payload)
    {
        $rawPayload = json_encode($payload);

        return $this->call('POST', route('tripay.callback'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CALLBACK_EVENT' => 'payment_status',
            'HTTP_X_CALLBACK_SIGNATURE' => hash_hmac('sha256', $rawPayload, (string) config('tripay.private_key')),
        ], $rawPayload);
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
