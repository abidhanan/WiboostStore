<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\XoftwarePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class QrisOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_qris_order_history_uses_qris_total_and_continue_button(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        $transaction = Transaction::create([
            'invoice_number' => 'WIB-HISTORY-PENDING',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 10000,
            'target_data' => 'target',
            'payment_method' => 'QRISREALTIME',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'snap_token' => 'https://payment.test/qris/WIB-HISTORY-PENDING',
            'response_data' => [
                'xoftware_create_response' => [
                    'data' => [
                        'admin_fee' => 161,
                        'total_amount' => 10161,
                        'expires_at' => now()->addHour()->toIso8601String(),
                    ],
                ],
            ],
        ]);

        $this->actingAs($user)
            ->get(route('user.history'))
            ->assertOk()
            ->assertSee($transaction->invoice_number)
            ->assertSee('Menunggu Bayar')
            ->assertSee('QRIS')
            ->assertSee('Rp 10.161')
            ->assertSee('Lanjut Bayar')
            ->assertDontSee('Bayar via QRIS')
            ->assertDontSee('Nanti Saja');
    }

    public function test_expired_qris_order_is_automatically_marked_cancelled(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        $transaction = Transaction::create([
            'invoice_number' => 'WIB-QRIS-EXPIRED',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 10000,
            'target_data' => 'target',
            'payment_method' => 'QRISREALTIME',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'response_data' => [
                'xoftware_create_response' => [
                    'data' => [
                        'expires_at' => now()->subMinute()->toIso8601String(),
                    ],
                ],
            ],
        ]);

        $this->actingAs($user)
            ->get(route('user.history'))
            ->assertOk()
            ->assertSee($transaction->invoice_number)
            ->assertSee('Batal')
            ->assertDontSee('Lanjut Bayar');

        $transaction->refresh();

        $this->assertSame('failed', $transaction->payment_status);
        $this->assertSame('failed', $transaction->order_status);
        $this->assertSame('expired', data_get($transaction->response_data, 'payment_cancel_reason'));

        $this->actingAs($user)
            ->getJson(route('user.qris.status', $transaction->invoice_number))
            ->assertOk()
            ->assertJson([
                'status' => 'failed',
                'label' => 'Batal',
                'redirect_url' => null,
            ]);
    }

    public function test_admin_transaction_page_uses_qris_amount_method_and_payment_status_labels(): void
    {
        DB::table('roles')->insertOrIgnore([
            'id' => 1,
            'name' => 'Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin = User::factory()->create(['role_id' => 1]);
        $buyer = User::factory()->create();
        $product = $this->createProduct();

        Transaction::create([
            'invoice_number' => 'WIB-ADMIN-QRIS-WAITING',
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'amount' => 10000,
            'target_data' => 'target',
            'payment_method' => 'tripay',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'response_data' => [
                'xoftware_create_response' => [
                    'data' => [
                        'total_amount' => 10161,
                        'expires_at' => now()->addHour()->toIso8601String(),
                    ],
                ],
            ],
        ]);

        Transaction::create([
            'invoice_number' => 'WIB-ADMIN-QRIS-SPACED',
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'amount' => 20000,
            'target_data' => 'target',
            'payment_method' => 'QRIS Real Time',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'response_data' => [
                'xoftware_create_response' => [
                    'data' => [
                        'wiboost_total_payable' => 20291,
                        'wiboost_buyer_admin_fee' => 291,
                        'expires_at' => now()->addHour()->toIso8601String(),
                    ],
                ],
            ],
        ]);

        Transaction::create([
            'invoice_number' => 'WIB-DASHBOARD-QRIS-PAID',
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'amount' => 30000,
            'paid_amount' => 30411,
            'target_data' => 'target',
            'payment_method' => 'QRISREALTIME',
            'payment_status' => 'paid',
            'order_status' => 'processing',
        ]);

        $expired = Transaction::create([
            'invoice_number' => 'WIB-ADMIN-QRIS-EXPIRED',
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'amount' => 12000,
            'target_data' => 'target',
            'payment_method' => 'QRISREALTIME',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'response_data' => [
                'xoftware_create_response' => [
                    'data' => [
                        'expires_at' => now()->subMinute()->toIso8601String(),
                    ],
                ],
            ],
        ]);

        $this->assertTrue($expired->qris_expires_at->isPast());

        $response = $this->actingAs($admin)
            ->get(route('admin.transactions.index'));

        $expired->refresh();

        $this->assertSame('failed', $expired->payment_status);
        $this->assertTrue($expired->cancelled_due_to_qris_expiry);

        $response
            ->assertOk()
            ->assertSee('WIB-ADMIN-QRIS-WAITING')
            ->assertSee('Rp 10.161')
            ->assertSee('QRIS')
            ->assertSee('Menunggu')
            ->assertSee('WIB-ADMIN-QRIS-SPACED')
            ->assertSee('Rp 20.291')
            ->assertSee('WIB-ADMIN-QRIS-EXPIRED')
            ->assertSee('Batal');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('WIB-DASHBOARD-QRIS-PAID')
            ->assertSee('Rp 30.411')
            ->assertSee('QRIS');
    }

    public function test_paid_qris_page_shows_success_badge_and_status_redirect(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        $transaction = Transaction::create([
            'invoice_number' => 'WIB-QRIS-SUCCESS',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 10000,
            'paid_amount' => 10500,
            'target_data' => 'target',
            'payment_method' => 'QRIS',
            'payment_status' => 'paid',
            'order_status' => 'processing',
            'response_data' => [
                'xoftware_create_response' => [
                    'data' => [
                        'qris_text' => '000201010212TESTQRIS',
                    ],
                ],
            ],
        ]);

        $this->actingAs($user)
            ->get(route('user.qris.show', $transaction->invoice_number))
            ->assertOk()
            ->assertSee('data-status="success"', false)
            ->assertSee('Sukses')
            ->assertSee('QRIS Berlaku Sampai')
            ->assertSee('id="qris-expiry-countdown"', false)
            ->assertSee('data-qris-refresh', false)
            ->assertDontSee('Memeriksa...')
            ->assertDontSee('Menunggu pembayaran');

        $this->actingAs($user)
            ->getJson(route('user.qris.status', $transaction->invoice_number))
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'label' => 'Sukses',
                'redirect_url' => route('user.history', ['auto_open' => $transaction->id]),
            ]);
    }

    public function test_gateway_creation_failure_remains_recorded_as_failed_order(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        $this->mock(XoftwarePaymentService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createTransactionPayment')
                ->once()
                ->andThrow(new RuntimeException('metadata wajib berisi minimal 1 item'));
        });

        $this->from(route('user.order.category', $product->category->slug))
            ->actingAs($user)
            ->post(route('user.checkout.process'), [
                'product_id' => $product->id,
                'payment_method' => 'tripay',
            ])
            ->assertRedirect(route('user.order.category', $product->category->slug))
            ->assertSessionHas('error');

        $transaction = Transaction::query()->latest('id')->firstOrFail();

        $this->assertSame('failed', $transaction->payment_status);
        $this->assertSame('failed', $transaction->order_status);
        $this->assertStringContainsString('metadata wajib', (string) $transaction->target_notes);

        $this->actingAs($user)
            ->get(route('user.history'))
            ->assertOk()
            ->assertSee($transaction->invoice_number);
    }

    private function createProduct(): Product
    {
        $category = Category::create([
            'name' => 'Aplikasi Premium',
            'slug' => 'aplikasi-premium-' . uniqid(),
            'description' => 'Kategori test',
            'fulfillment_type' => 'stock_based',
        ]);

        return Product::create([
            'category_id' => $category->id,
            'provider_id' => null,
            'process_type' => 'account',
            'name' => 'Produk QRIS Test',
            'slug' => 'produk-qris-' . uniqid(),
            'description' => 'Produk test',
            'price' => 10000,
            'status' => 'active',
            'is_active' => true,
            'requires_buyer_email' => false,
            'stock_reminder' => 0,
        ]);
    }
}
