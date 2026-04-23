<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
                'input' => ['target_data' => '@wibooststore'],
                'expected_target' => '@wibooststore',
                'expected_label' => 'Username akun / link postingan',
            ],
            [
                'slug' => 'top-up-game',
                'process_type' => 'api',
                'input' => ['game_id' => '123456789'],
                'expected_target' => '123456789',
                'expected_label' => 'ID game',
            ],
            [
                'slug' => 'kuota-murah',
                'process_type' => 'api',
                'input' => ['phone_number' => '081234567890'],
                'expected_target' => '081234567890',
                'expected_label' => 'Nomor handphone',
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
                'provider_source' => $case['process_type'] === 'api' ? 'ordersosmed' : null,
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
            $this->assertSame($case['expected_target'], data_get($transaction->order_input_data, 'fields.0.value'));
        }
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
        $response->assertSee("data-checkout-fields='[{&quot;name&quot;:&quot;game_id&quot;", false);
        $response->assertDontSee('&amp;quot;name&amp;quot;', false);
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
