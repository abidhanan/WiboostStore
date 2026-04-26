<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Mail\OrderSuccessMail;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DigiflazzService;
use App\Services\MidtransService;
use App\Services\OrderSosmedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;
use Tests\TestCase;

class UserOrderCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_uses_category_specific_single_input_fields(): void
    {
        $user = User::factory()->create();

        $this->mock(MidtransService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getTransactionSnapToken')
                ->andReturn('snap-token-demo');
        });

        $cases = [
            [
                'slug' => 'suntik-sosmed',
                'process_type' => 'api',
                'provider_source' => 'ordersosmed',
                'input' => ['target_data' => '@wibooststore', 'order_quantity' => 1],
                'expected_target' => '@wibooststore',
                'expected_label' => 'Username akun / link postingan',
                'expected_first_value' => '@wibooststore',
            ],
            [
                'slug' => 'top-up-game',
                'process_type' => 'api',
                'provider_source' => 'digiflazz',
                'input' => ['game_user_id' => '123456789', 'game_zone_id' => '1234'],
                'expected_target' => '123456789 (1234)',
                'expected_label' => 'User ID game',
                'expected_first_value' => '123456789',
            ],
            [
                'slug' => 'kuota-murah',
                'process_type' => 'api',
                'provider_source' => 'digiflazz',
                'input' => ['phone_number' => '081234567890'],
                'expected_target' => '081234567890',
                'expected_label' => 'Nomor handphone',
                'expected_first_value' => '081234567890',
            ],
        ];

        foreach ($cases as $index => $case) {
            $category = Category::create([
                'name' => ucfirst(str_replace('-', ' ', $case['slug'])) . ' ' . $index,
                'slug' => $case['slug'],
                'description' => 'Kategori test',
                'fulfillment_type' => 'manual_action',
            ]);

            $product = Product::create([
                'category_id' => $category->id,
                'provider_id' => null,
                'provider_source' => $case['provider_source'] ?? null,
                'process_type' => $case['process_type'],
                'name' => 'Produk ' . $index,
                'slug' => 'produk-' . $index,
                'description' => 'Produk test',
                'price' => 10000,
                'status' => 'active',
                'is_active' => true,
                'stock_reminder' => 0,
            ]);

            $response = $this->actingAs($user)->post(route('user.checkout.process'), array_merge([
                'product_id' => $product->id,
                'payment_method' => 'manual',
            ], $case['input']));

            $response->assertOk();

            $transaction = Transaction::query()->latest('id')->first();

            $this->assertNotNull($transaction);
            $this->assertSame($case['expected_target'], $transaction->target_data);
            $this->assertSame($case['expected_label'], data_get($transaction->order_input_data, 'fields.0.label'));
            $this->assertSame($case['expected_first_value'], data_get($transaction->order_input_data, 'fields.0.value'));
        }
    }

    public function test_suntik_sosmed_order_page_renders_quantity_service_form(): void
    {
        $user = User::factory()->create();

        $root = Category::create([
            'name' => 'Suntik Sosmed',
            'slug' => 'suntik-sosmed',
            'description' => 'Kategori induk test',
            'fulfillment_type' => 'auto_api',
        ]);

        $category = Category::create([
            'name' => 'Like Indonesia',
            'slug' => 'sosmed-instagram-like-indonesia',
            'description' => 'Kategori leaf test',
            'parent_id' => $root->id,
            'fulfillment_type' => 'auto_api',
        ]);

        Product::create([
            'category_id' => $category->id,
            'provider_id' => 'ordersosmed',
            'provider_source' => 'ordersosmed',
            'provider_product_id' => 'svc-1',
            'provider_quantity' => 100,
            'process_type' => 'api',
            'name' => 'Instagram Like Indonesia',
            'slug' => 'instagram-like-indonesia',
            'description' => "Layanan like test\nMinimum order: 100\nMaximum order: 10000\nAverage time: 1-2 jam",
            'price' => 2800,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('user.order.category', $category->slug));

        $response->assertOk();
        $response->assertSee('Layanan');
        $response->assertSee('Harga / 1000');
        $response->assertSee('Jumlah Pesanan');
        $response->assertSee('Target/Link/Username');
        $response->assertSee('1-2 jam');
        $response->assertSee('Instagram Like Indonesia');
    }

    public function test_suntik_sosmed_checkout_uses_requested_quantity_for_amount_and_provider_order(): void
    {
        $user = User::factory()->create([
            'balance' => 10000,
        ]);

        $this->mock(OrderSosmedService::class, function (MockInterface $mock) {
            $mock->shouldReceive('placeOrder')
                ->once()
                ->with('svc-1', '@wibooststore', 250)
                ->andReturn([
                    'success' => true,
                    'message' => 'Pesanan diterima provider.',
                    'order_status' => 'processing',
                    'raw' => ['order' => 'OS-1'],
                ]);
        });

        $category = Category::create([
            'name' => 'Suntik Sosmed',
            'slug' => 'suntik-sosmed',
            'description' => 'Kategori test',
            'fulfillment_type' => 'auto_api',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'provider_id' => 'ordersosmed',
            'provider_source' => 'ordersosmed',
            'provider_product_id' => 'svc-1',
            'provider_quantity' => 100,
            'process_type' => 'api',
            'name' => 'Instagram Like Indonesia',
            'slug' => 'instagram-like-indonesia',
            'description' => "Layanan like test\nMinimum order: 100\nMaximum order: 10000",
            'price' => 2800,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('user.checkout.process'), [
            'product_id' => $product->id,
            'payment_method' => 'wallet',
            'target_data' => '@wibooststore',
            'order_quantity' => 250,
        ]);

        $response->assertRedirect(route('user.history'));

        $transaction = Transaction::query()->latest('id')->first();

        $this->assertSame(7000.0, (float) $transaction->amount);
        $this->assertSame('processing', $transaction->order_status);
        $this->assertSame(250, $transaction->provider_order_quantity);
        $this->assertSame(3000.0, (float) $user->fresh()->balance);
        $this->assertSame('Jumlah Pesanan', data_get($transaction->order_input_data, 'fields.1.label'));
        $this->assertSame('250', data_get($transaction->order_input_data, 'fields.1.value'));
    }

    public function test_premium_checkout_email_requirement_can_be_enabled_per_product(): void
    {
        $user = User::factory()->create();

        $this->mock(MidtransService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getTransactionSnapToken')
                ->andReturn('snap-token-demo');
        });

        $category = Category::create([
            'name' => 'Aplikasi Premium',
            'slug' => 'aplikasi-premium',
            'description' => 'Kategori test',
            'fulfillment_type' => 'stock_based',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'provider_id' => null,
            'process_type' => 'account',
            'name' => 'Premium Test',
            'slug' => 'premium-test',
            'description' => 'Produk premium',
            'price' => 25000,
            'status' => 'active',
            'is_active' => true,
            'requires_buyer_email' => true,
            'stock_reminder' => 0,
        ]);

        $invalidResponse = $this->from(route('user.order.category', $category->slug))
            ->actingAs($user)
            ->post(route('user.checkout.process'), [
                'product_id' => $product->id,
                'payment_method' => 'manual',
            ]);

        $invalidResponse->assertRedirect(route('user.order.category', $category->slug));
        $invalidResponse->assertSessionHasErrors('app_email');

        $validResponse = $this->actingAs($user)->post(route('user.checkout.process'), [
            'product_id' => $product->id,
            'payment_method' => 'manual',
            'app_email' => 'khusus.aplikasi@gmail.com',
        ]);

        $validResponse->assertOk();

        $transaction = Transaction::query()->latest('id')->first();

        $this->assertSame('khusus.aplikasi@gmail.com', $transaction->target_data);
        $this->assertSame('Email khusus aplikasi', data_get($transaction->order_input_data, 'fields.0.label'));
    }

    public function test_premium_checkout_email_requirement_can_be_disabled_per_product(): void
    {
        $user = User::factory()->create();

        $this->mock(MidtransService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getTransactionSnapToken')
                ->andReturn('snap-token-demo');
        });

        $category = Category::create([
            'name' => 'Aplikasi Premium',
            'slug' => 'aplikasi-premium',
            'description' => 'Kategori test',
            'fulfillment_type' => 'stock_based',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'provider_id' => null,
            'process_type' => 'account',
            'name' => 'Premium Tanpa Email',
            'slug' => 'premium-tanpa-email',
            'description' => 'Produk premium tanpa email buyer',
            'price' => 25000,
            'status' => 'active',
            'is_active' => true,
            'requires_buyer_email' => false,
            'stock_reminder' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('user.checkout.process'), [
            'product_id' => $product->id,
            'payment_method' => 'manual',
        ]);

        $response->assertOk();

        $transaction = Transaction::query()->latest('id')->first();

        $this->assertSame('- (Tidak membutuhkan target tambahan)', $transaction->target_data);
        $this->assertNull($transaction->order_input_data);
    }

    public function test_order_page_renders_checkout_fields_payload_without_double_encoding(): void
    {
        $user = User::factory()->create();

        $parentCategory = Category::create([
            'name' => 'Top Up Game',
            'slug' => 'top-up-game',
            'description' => 'Kategori induk test',
            'fulfillment_type' => 'auto_api',
        ]);

        $category = Category::create([
            'name' => 'Free Fire',
            'slug' => 'game-free-fire',
            'description' => 'Kategori sub test',
            'parent_id' => $parentCategory->id,
            'fulfillment_type' => 'auto_api',
        ]);

        Product::create([
            'category_id' => $category->id,
            'provider_id' => 'digiflazz',
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'FF1',
            'process_type' => 'api',
            'name' => 'Diamond Free Fire Test',
            'slug' => 'diamond-free-fire-test',
            'description' => 'Produk game test',
            'price' => 12000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('user.order.category', $category->slug));

        $response->assertOk();
        $response->assertSee("data-checkout-fields='[{&quot;name&quot;:&quot;game_user_id&quot;", false);
        $response->assertSee('&quot;name&quot;:&quot;game_zone_id&quot;', false);
        $response->assertDontSee('&amp;quot;name&amp;quot;', false);
    }

    public function test_top_up_game_wallet_checkout_sends_user_id_and_zone_id_to_digiflazz(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'balance' => 20000,
        ]);

        $this->mock(DigiflazzService::class, function (MockInterface $mock) {
            $mock->shouldReceive('placeOrder')
                ->once()
                ->with('ML86', '1234567891234', \Mockery::type('string'))
                ->andReturn([
                    'success' => true,
                    'message' => 'Transaksi sukses',
                    'order_status' => 'success',
                    'raw' => ['data' => ['status' => 'Sukses']],
                ]);
        });

        $category = Category::create([
            'name' => 'Top Up Game',
            'slug' => 'top-up-game',
            'description' => 'Kategori test',
            'fulfillment_type' => 'auto_api',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'provider_id' => 'digiflazz',
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ML86',
            'process_type' => 'api',
            'name' => 'Mobile Legends 86 Diamonds',
            'slug' => 'mobile-legends-86-diamonds',
            'description' => 'Produk game test',
            'price' => 12000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('user.checkout.process'), [
            'product_id' => $product->id,
            'payment_method' => 'wallet',
            'game_user_id' => '123456789',
            'game_zone_id' => '1234',
        ]);

        $response->assertRedirect(route('user.history'));

        $transaction = Transaction::query()->latest('id')->first();

        $this->assertSame('123456789 (1234)', $transaction->target_data);
        $this->assertSame('1234567891234', $transaction->provider_customer_no);
        $this->assertSame('success', $transaction->order_status);
        $this->assertSame(8000.0, (float) $user->fresh()->balance);

        Mail::assertSent(OrderSuccessMail::class, fn (OrderSuccessMail $mail) => $mail->transaction->is($transaction));
    }

    public function test_failed_api_wallet_checkout_refunds_balance_once(): void
    {
        $user = User::factory()->create([
            'balance' => 20000,
        ]);

        $this->mock(DigiflazzService::class, function (MockInterface $mock) {
            $mock->shouldReceive('placeOrder')
                ->once()
                ->andReturn([
                    'success' => false,
                    'message' => 'Produk sedang gangguan provider.',
                    'order_status' => 'failed',
                    'raw' => ['data' => ['status' => 'Gagal']],
                ]);
        });

        $category = Category::create([
            'name' => 'Top Up Game',
            'slug' => 'top-up-game',
            'description' => 'Kategori test',
            'fulfillment_type' => 'auto_api',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'provider_id' => 'digiflazz',
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ML86',
            'process_type' => 'api',
            'name' => 'Mobile Legends 86 Diamonds',
            'slug' => 'mobile-legends-86-diamonds-refund',
            'description' => 'Produk game test',
            'price' => 12000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('user.checkout.process'), [
            'product_id' => $product->id,
            'payment_method' => 'wallet',
            'game_user_id' => '123456789',
            'game_zone_id' => '1234',
        ]);

        $response->assertRedirect(route('user.history'));

        $transaction = Transaction::query()->latest('id')->first();

        $this->assertSame('failed', $transaction->order_status);
        $this->assertSame('Produk sedang gangguan provider.', $transaction->target_notes);
        $this->assertSame(20000.0, (float) $user->fresh()->balance);
        $this->assertDatabaseHas('wallet_histories', [
            'invoice_number' => $transaction->invoice_number,
            'type' => 'purchase',
            'amount' => 12000,
        ]);
        $this->assertDatabaseHas('wallet_histories', [
            'invoice_number' => $transaction->invoice_number,
            'type' => 'refund',
            'amount' => 12000,
        ]);

        app(\App\Services\OrderFulfillmentService::class)->markAsFailed($transaction->fresh(['product', 'user']), 'Retry gagal');

        $this->assertSame(20000.0, (float) $user->fresh()->balance);
        $this->assertSame(1, \App\Models\WalletHistory::where('invoice_number', $transaction->invoice_number)->where('type', 'refund')->count());
    }

    public function test_nomor_luar_checkout_allows_empty_input(): void
    {
        $user = User::factory()->create();

        $this->mock(MidtransService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getTransactionSnapToken')
                ->andReturn('snap-token-demo');
        });

        $category = Category::create([
            'name' => 'Nomor Luar',
            'slug' => 'nomor-luar',
            'description' => 'Kategori test',
            'fulfillment_type' => 'stock_based',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'provider_id' => null,
            'process_type' => 'number',
            'name' => 'Nomor Luar Test',
            'slug' => 'nomor-luar-test',
            'description' => 'Produk nomor luar',
            'price' => 15000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('user.checkout.process'), [
            'product_id' => $product->id,
            'payment_method' => 'manual',
        ]);

        $response->assertOk();

        $transaction = Transaction::query()->latest('id')->first();

        $this->assertSame('- (Tidak membutuhkan target tambahan)', $transaction->target_data);
        $this->assertNull($transaction->order_input_data);
    }

    public function test_buzzer_checkout_requires_link_and_comment_and_stores_both_fields(): void
    {
        $user = User::factory()->create();

        $this->mock(MidtransService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getTransactionSnapToken')
                ->andReturn('snap-token-demo');
        });

        $category = Category::create([
            'name' => 'Buzzer',
            'slug' => 'buzzer',
            'description' => 'Kategori test',
            'fulfillment_type' => 'manual_action',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'provider_id' => null,
            'process_type' => 'manual',
            'name' => 'Buzzer Test',
            'slug' => 'buzzer-test',
            'description' => 'Produk buzzer',
            'price' => 50000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $invalidResponse = $this->from(route('user.order.category', $category->slug))
            ->actingAs($user)
            ->post(route('user.checkout.process'), [
                'product_id' => $product->id,
                'payment_method' => 'manual',
                'campaign_link' => 'https://maps.app.goo.gl/test-buzzer',
            ]);

        $invalidResponse->assertRedirect(route('user.order.category', $category->slug));
        $invalidResponse->assertSessionHasErrors('comment_brief');

        $validResponse = $this->actingAs($user)->post(route('user.checkout.process'), [
            'product_id' => $product->id,
            'payment_method' => 'manual',
            'campaign_link' => 'https://maps.app.goo.gl/test-buzzer',
            'comment_brief' => 'Komentar positif, natural, dan menonjolkan pelayanan cepat.',
        ]);

        $validResponse->assertOk();

        $transaction = Transaction::query()->latest('id')->first();

        $this->assertSame('https://maps.app.goo.gl/test-buzzer', $transaction->target_data);
        $this->assertCount(2, $transaction->order_input_data['fields']);
        $this->assertSame('Link postingan / link Google Maps', data_get($transaction->order_input_data, 'fields.0.label'));
        $this->assertSame('Deskripsi komentar buzzer', data_get($transaction->order_input_data, 'fields.1.label'));
    }
}
