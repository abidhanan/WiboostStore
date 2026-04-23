<?php

namespace Tests\Feature\Console;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DigiflazzService;
use App\Services\OrderSosmedGuestCatalogService;
use App\Services\OrderSosmedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class WiboostOperationsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_refund_command_refunds_stale_paid_pending_transactions(): void
    {
        $this->seedCategory(1, 'default-category');

        $user = User::factory()->create([
            'balance' => 5000,
        ]);

        $product = Product::create([
            'category_id' => 1,
            'provider_id' => null,
            'process_type' => 'manual',
            'name' => 'Jasa Manual',
            'slug' => 'jasa-manual',
            'description' => 'Produk manual',
            'price' => 10000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $transaction = Transaction::create([
            'invoice_number' => 'WIB-REFUND-001',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 10000,
            'target_data' => 'username-target',
            'payment_status' => 'paid',
            'order_status' => 'pending',
        ]);

        $transaction->forceFill([
            'updated_at' => now()->subHours(25),
        ])->save();

        $this->artisan('wiboost:auto-refund')
            ->assertExitCode(0);

        $transaction->refresh();
        $user->refresh();

        $this->assertSame('failed', $transaction->order_status);
        $this->assertStringContainsString('Saldo telah dikembalikan', (string) $transaction->target_notes);
        $this->assertSame(15000.0, (float) $user->balance);

        $this->assertDatabaseHas('wallet_histories', [
            'invoice_number' => 'WIB-REFUND-001',
            'type' => 'refund',
            'user_id' => $user->id,
        ]);
    }

    public function test_maintenance_report_command_sends_summary_to_discord_when_enabled(): void
    {
        $this->seedCategory(1, 'default-category');

        config([
            'services.discord.webhook_url' => 'https://discord.test/webhook',
            'services.digiflazz.username' => 'demo-user',
            'services.digiflazz.key' => 'demo-key',
            'services.ordersosmed.api_url' => 'https://ordersosmed.test/api',
            'services.ordersosmed.api_id' => 'demo-id',
            'services.ordersosmed.api_key' => 'demo-key',
            'midtrans.server_key' => 'midtrans-server',
            'midtrans.client_key' => 'midtrans-client',
            'wiboost.public_url' => 'https://demo.ngrok-free.app',
        ]);

        $this->mock(DigiflazzService::class, function (MockInterface $mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('getBalance')
                ->once()
                ->andReturn([
                    'success' => true,
                    'raw' => [
                        'data' => [
                            'deposit' => 150000,
                        ],
                    ],
                ]);
        });

        $this->mock(OrderSosmedService::class, function (MockInterface $mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('getProfile')
                ->once()
                ->andReturn([
                    'success' => true,
                    'data' => [
                        'balance' => 200000,
                    ],
                ]);
        });

        Http::fake([
            'https://discord.test/*' => Http::response(['ok' => true], 204),
        ]);

        Product::create([
            'category_id' => 1,
            'provider_id' => null,
            'process_type' => 'account',
            'name' => 'Netflix Shared',
            'slug' => 'netflix-shared',
            'description' => 'Akun premium',
            'price' => 50000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 1,
        ]);

        $this->artisan('wiboost:maintenance-report --force')
            ->assertExitCode(0);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $request->url() === 'https://discord.test/webhook'
                && ($payload['embeds'][0]['title'] ?? null) === 'Laporan maintenance Wiboost'
                && str_contains(($payload['embeds'][0]['fields'][6]['value'] ?? ''), 'Midtrans');
        });
    }

    public function test_ordersosmed_service_uses_api_1_service_endpoint_with_secret_key(): void
    {
        config([
            'services.ordersosmed.api_url' => 'https://ordersosmed.id/api-1',
            'services.ordersosmed.api_id' => 'demo-id',
            'services.ordersosmed.api_key' => 'demo-key',
            'services.ordersosmed.secret_key' => 'demo-secret',
        ]);

        Http::fake([
            'https://ordersosmed.id/api-1' => Http::response([
                'message' => 'Not found',
            ], 404),
            'https://ordersosmed.id/api-1/service' => Http::response([
                'data' => [[
                    'id' => 1234,
                    'name' => 'Instagram Followers Indonesia',
                ]],
            ], 200),
        ]);

        $response = app(OrderSosmedService::class)->getServices();

        $this->assertTrue($response['success']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://ordersosmed.id/api-1/service'
                && ($request['api_id'] ?? null) === 'demo-id'
                && ($request['api_key'] ?? null) === 'demo-key'
                && ($request['secret_key'] ?? null) === 'demo-secret';
        });
    }

    public function test_ordersosmed_service_uses_api_1_profile_endpoint_with_secret_key(): void
    {
        config([
            'services.ordersosmed.api_url' => 'https://ordersosmed.id/api-1',
            'services.ordersosmed.api_id' => 'demo-id',
            'services.ordersosmed.api_key' => 'demo-key',
            'services.ordersosmed.secret_key' => 'demo-secret',
        ]);

        Http::fake([
            'https://ordersosmed.id/api-1' => Http::response([
                'message' => 'Not found',
            ], 404),
            'https://ordersosmed.id/api-1/profile' => Http::response([
                'data' => [
                    'balance' => 200000,
                    'username' => 'demo-user',
                ],
            ], 200),
        ]);

        $response = app(OrderSosmedService::class)->getProfile();

        $this->assertTrue($response['success']);
        $this->assertSame(200000, $response['data']['balance']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://ordersosmed.id/api-1/profile'
                && ($request['api_id'] ?? null) === 'demo-id'
                && ($request['api_key'] ?? null) === 'demo-key'
                && ($request['secret_key'] ?? null) === 'demo-secret';
        });
    }

    public function test_ordersosmed_service_normalizes_legacy_domain_and_falls_back_to_working_api_path(): void
    {
        config([
            'services.ordersosmed.api_url' => 'https://ordersosmed.com/api/v2',
            'services.ordersosmed.api_id' => 'demo-id',
            'services.ordersosmed.api_key' => 'demo-key',
        ]);

        Http::fake([
            'https://ordersosmed.id/api-1/service' => Http::response([
                'message' => 'Not found',
            ], 404),
            'https://ordersosmed.id/api/v2' => Http::response([
                'message' => 'Not found',
            ], 404),
            'https://ordersosmed.id/api' => Http::response([
                'data' => [[
                    'id' => 1234,
                    'name' => 'Instagram Followers Indonesia',
                ]],
            ], 200),
        ]);

        $response = app(OrderSosmedService::class)->getServices();

        $this->assertTrue($response['success']);

        Http::assertSent(fn ($request) => $request->url() === 'https://ordersosmed.id/api/v2');
        Http::assertSent(fn ($request) => $request->url() === 'https://ordersosmed.id/api');
    }

    public function test_sync_digiflazz_command_preserves_existing_slug_and_falls_back_to_default_category(): void
    {
        $this->seedCategory(1, 'default-category');

        $existingProduct = Product::create([
            'category_id' => 1,
            'provider_id' => 'digiflazz',
            'provider_source' => 'digiflazz',
            'process_type' => 'api',
            'name' => 'Produk Lama',
            'slug' => 'slug-lama-produk',
            'description' => 'Produk lama',
            'price' => 9000,
            'provider_product_id' => 'ML-86',
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $this->mock(DigiflazzService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPriceList')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'OK',
                    'raw' => [
                        'data' => [[
                            'buyer_sku_code' => 'ML-86',
                            'product_name' => 'Mobile Legends 86 Diamonds',
                            'desc' => 'Top up ML',
                            'price' => 10000,
                            'buyer_product_status' => true,
                            'seller_product_status' => true,
                            'brand' => 'Mobile Legends',
                            'category' => 'Games',
                        ]],
                    ],
                ]);
        });

        $this->artisan('sync:digiflazz')
            ->assertExitCode(0);

        $existingProduct->refresh();

        $this->assertSame('slug-lama-produk', $existingProduct->slug);
        $this->assertSame(1, $existingProduct->category_id);
        $this->assertSame('digiflazz', $existingProduct->provider_source);
        $this->assertSame('Mobile Legends 86 Diamonds', $existingProduct->name);
        $this->assertSame(11000.0, (float) $existingProduct->price);
    }

    public function test_sync_ordersosmed_command_imports_services_into_products(): void
    {
        $this->seedCategory(1, 'suntik-sosmed');

        $this->mock(OrderSosmedService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getServices')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'OK',
                    'data' => [[
                        'id' => 1234,
                        'name' => 'Instagram Followers Indonesia',
                        'category' => 'Instagram',
                        'rate' => 25000,
                        'min' => 100,
                        'max' => 10000,
                        'description' => 'Fast refill',
                    ]],
                ]);
        });

        $this->artisan('sync:ordersosmed')
            ->assertExitCode(0);

        $product = Product::query()
            ->where('provider_source', 'ordersosmed')
            ->where('provider_product_id', '1234')
            ->first();

        $this->assertNotNull($product);
        $this->assertSame('ordersosmed', $product->provider_source);
        $this->assertSame(1, $product->category_id);
        $this->assertSame(100, $product->provider_quantity);
        $this->assertSame(2800.0, (float) $product->price);
    }

    public function test_ordersosmed_guest_catalog_service_parses_public_ajax_rows(): void
    {
        config([
            'services.ordersosmed.api_url' => 'https://ordersosmed.id/api/v2',
        ]);

        Http::fake([
            'https://ordersosmed.id/page/services_guest?type=prepaid' => Http::response(
                '<input type="hidden" name="csrf_token" value="abc123abc123abc123abc123abc123abc123abc123abc123abc123abc123abcd">' .
                '<select id="table-category"><option value="1">Pulsa</option></select>',
                200
            ),
            'https://ordersosmed.id/ajax/services?type=prepaid' => Http::response([
                'tbody' => '
                    <tr>
                        <td>406</td>
                        <td>Tri Data 100 MB 30 Hari</td>
                        <td>Rp 845</td>
                        <td>Rp 845</td>
                        <td><a href="javascript:;">Lihat Detail</a></td>
                    </tr>
                ',
                'tinfo' => 'Menampilkan 1 sampai 1 dari 1 data.',
            ], 200),
        ]);

        $response = app(OrderSosmedGuestCatalogService::class)->getServices(['prepaid'], 1000);

        $this->assertTrue($response['success']);
        $this->assertCount(1, $response['data']);
        $this->assertSame('406', $response['data'][0]['id']);
        $this->assertSame('prepaid', $response['data'][0]['_ordersosmed_catalog_type']);
        $this->assertSame(845.0, (float) $response['data'][0]['price']);
    }

    public function test_sync_digiflazz_command_maps_utility_products_to_kuota_category_when_slug_exists(): void
    {
        $this->seedCategory(1, 'default-category');
        $this->seedCategory(3, 'kuota-murah');

        $this->mock(DigiflazzService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPriceList')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'OK',
                    'raw' => [
                        'data' => [[
                            'buyer_sku_code' => 'ax-data',
                            'product_name' => 'Axis Data 2 GB',
                            'desc' => 'Paket data Axis',
                            'price' => 10000,
                            'buyer_product_status' => true,
                            'seller_product_status' => true,
                            'brand' => 'AXIS',
                            'category' => 'Data',
                        ]],
                    ],
                ]);
        });

        $this->artisan('sync:digiflazz')
            ->assertExitCode(0);

        $product = Product::query()
            ->where('provider_source', 'digiflazz')
            ->where('provider_product_id', 'ax-data')
            ->first();

        $this->assertNotNull($product);
        $this->assertSame(3, $product->category_id);
    }

    public function test_sync_digiflazz_command_skips_unsupported_products_outside_game_and_quota_catalog(): void
    {
        $this->seedCategory(1, 'default-category');
        $this->seedCategory(2, 'top-up-game');
        $this->seedCategory(3, 'kuota-murah');

        $this->mock(DigiflazzService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPriceList')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'OK',
                    'raw' => [
                        'data' => [[
                            'buyer_sku_code' => 'pln100',
                            'product_name' => 'PLN 100.000',
                            'desc' => 'Masukkan nomor meter',
                            'price' => 100000,
                            'buyer_product_status' => true,
                            'seller_product_status' => true,
                            'brand' => 'PLN',
                            'category' => 'PLN',
                        ]],
                    ],
                ]);
        });

        $this->artisan('sync:digiflazz')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'pln100',
        ]);
    }

    public function test_sync_ordersosmed_command_falls_back_to_public_catalog_and_marks_sosmed_products_manual(): void
    {
        $this->seedCategory(1, 'suntik-sosmed');

        $this->mock(OrderSosmedService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getServices')
                ->once()
                ->andReturn([
                    'success' => false,
                    'message' => '404',
                    'data' => [],
                ]);
        });

        $this->mock(OrderSosmedGuestCatalogService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getServices')
                ->once()
                ->with(['sosmed'])
                ->andReturn([
                    'success' => true,
                    'message' => 'OK',
                    'data' => [[
                        'id' => 406,
                        'name' => 'Instagram Followers Lokal',
                        'category' => 'Instagram',
                        'rate' => 25000,
                        'min' => 100,
                        'max' => 5000,
                        'description' => 'Kategori OrderSosmed: Instagram',
                        '_ordersosmed_catalog_type' => 'sosmed',
                        '_ordersosmed_pricing_mode' => 'per_1000',
                    ]],
                ]);
        });

        $this->artisan('sync:ordersosmed')
            ->assertExitCode(0);

        $product = Product::query()
            ->where('provider_source', 'ordersosmed')
            ->where('provider_product_id', 'sosmed:406')
            ->first();

        $this->assertNotNull($product);
        $this->assertSame('manual', $product->process_type);
        $this->assertSame(1, $product->category_id);
        $this->assertSame('Username akun / link postingan', $product->target_label);
        $this->assertSame(2800.0, (float) $product->price);
    }

    public function test_sync_ordersosmed_command_upgrades_matching_fallback_product_to_api_mode(): void
    {
        $this->seedCategory(1, 'suntik-sosmed');

        $fallbackProduct = Product::create([
            'category_id' => 1,
            'provider_id' => 'ordersosmed',
            'provider_source' => 'ordersosmed',
            'process_type' => 'manual',
            'name' => 'Instagram Followers Indonesia',
            'slug' => 'instagram-followers-indonesia',
            'description' => 'Fallback guest catalog',
            'price' => 2800,
            'provider_product_id' => 'sosmed:1234',
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $this->mock(OrderSosmedService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getServices')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'OK',
                    'data' => [[
                        'id' => 1234,
                        'name' => 'Instagram Followers Indonesia',
                        'category' => 'Instagram',
                        'rate' => 25000,
                        'min' => 100,
                        'max' => 10000,
                        'description' => 'Fast refill',
                    ]],
                ]);
        });

        $this->artisan('sync:ordersosmed')
            ->assertExitCode(0);

        $fallbackProduct->refresh();

        $this->assertSame('api', $fallbackProduct->process_type);
        $this->assertSame('1234', $fallbackProduct->provider_product_id);
        $this->assertSame(1, Product::where('provider_source', 'ordersosmed')->count());
    }

    public function test_sync_ordersosmed_command_skips_buzzer_services(): void
    {
        $this->seedCategory(1, 'suntik-sosmed');

        $this->mock(OrderSosmedService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getServices')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'OK',
                    'data' => [[
                        'id' => 9090,
                        'name' => 'Buzzer Twitter Campaign',
                        'category' => 'Buzzer',
                        'rate' => 100000,
                        'min' => 1,
                        'max' => 1,
                    ]],
                ]);
        });

        $this->artisan('sync:ordersosmed')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '9090',
        ]);
    }

    public function test_setup_catalog_command_creates_subcategories_with_logos_and_assigns_products(): void
    {
        Storage::fake('public');

        $this->seedCategory(1, 'suntik-sosmed', 'Suntik Sosmed', 'auto_api');
        $this->seedCategory(2, 'top-up-game', 'Top Up Game', 'auto_api');
        $this->seedCategory(3, 'kuota-murah', 'Kuota Murah', 'auto_api');
        $this->seedCategory(4, 'aplikasi-premium', 'Aplikasi Premium', 'stock_based');
        $this->seedCategory(5, 'nomor-luar', 'Nomor Luar', 'stock_based');
        $this->seedCategory(6, 'buzzer', 'Buzzer', 'manual_action');

        $ordersosmedProduct = Product::create([
            'category_id' => 1,
            'provider_id' => 'ordersosmed',
            'provider_source' => 'ordersosmed',
            'process_type' => 'api',
            'name' => 'Instagram Followers Indonesia',
            'slug' => 'instagram-followers-indonesia',
            'description' => 'Kategori provider: Instagram',
            'price' => 2800,
            'provider_product_id' => '1234',
            'provider_quantity' => 100,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $digiflazzProduct = Product::create([
            'category_id' => 2,
            'provider_id' => 'digiflazz',
            'provider_source' => 'digiflazz',
            'process_type' => 'api',
            'name' => 'Free Fire 70 Diamond',
            'slug' => 'free-fire-70-diamond',
            'description' => 'Top up FF',
            'price' => 12000,
            'provider_product_id' => 'ff70',
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $this->artisan('wiboost:setup-catalog')
            ->assertExitCode(0);

        $ordersosmedLeafCategory = Category::where('slug', 'sosmed-instagram-followers-indonesia')->first();
        $freeFireCategory = Category::where('slug', 'game-free-fire')->first();
        $premiumCategory = Category::where('slug', 'premium-netflix')->first();

        $this->assertNotNull($ordersosmedLeafCategory);
        $this->assertNotNull($freeFireCategory);
        $this->assertNotNull($premiumCategory);
        Storage::disk('public')->assertExists($ordersosmedLeafCategory->image);
        Storage::disk('public')->assertExists($freeFireCategory->image);

        $ordersosmedProduct->refresh();
        $digiflazzProduct->refresh();

        $this->assertSame($ordersosmedLeafCategory->id, $ordersosmedProduct->category_id);
        $this->assertSame($freeFireCategory->id, $digiflazzProduct->category_id);

        $this->assertDatabaseHas('products', [
            'category_id' => $premiumCategory->id,
            'name' => 'Netflix Premium Sharing',
            'process_type' => 'account',
            'is_active' => false,
        ]);
    }

    public function test_sync_ordersosmed_command_does_not_overwrite_products_from_other_providers(): void
    {
        $this->seedCategory(1, 'suntik-sosmed');

        $digiflazzProduct = Product::create([
            'category_id' => 1,
            'provider_id' => 'digiflazz',
            'provider_source' => 'digiflazz',
            'process_type' => 'api',
            'name' => 'Produk Digiflazz',
            'slug' => 'produk-digiflazz',
            'description' => 'Produk lama',
            'price' => 10000,
            'provider_product_id' => '1234',
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $this->mock(OrderSosmedService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getServices')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'OK',
                    'data' => [[
                        'id' => 1234,
                        'name' => 'Instagram Followers Indonesia',
                        'category' => 'Instagram',
                        'rate' => 25000,
                        'min' => 100,
                        'max' => 10000,
                    ]],
                ]);
        });

        $this->artisan('sync:ordersosmed')
            ->assertExitCode(0);

        $digiflazzProduct->refresh();

        $this->assertSame('Produk Digiflazz', $digiflazzProduct->name);
        $this->assertSame(2, Product::where('provider_product_id', '1234')->count());
        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '1234',
            'name' => 'Instagram Followers Indonesia',
        ]);
    }

    public function test_sync_digiflazz_command_does_not_overwrite_products_from_other_providers(): void
    {
        $this->seedCategory(1, 'default-category');

        $orderSosmedProduct = Product::create([
            'category_id' => 1,
            'provider_id' => 'ordersosmed',
            'provider_source' => 'ordersosmed',
            'process_type' => 'api',
            'name' => 'Produk OrderSosmed',
            'slug' => 'produk-ordersosmed',
            'description' => 'Produk lama',
            'price' => 10000,
            'provider_product_id' => 'shared-code',
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $this->mock(DigiflazzService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPriceList')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'OK',
                    'raw' => [
                        'data' => [[
                            'buyer_sku_code' => 'shared-code',
                            'product_name' => 'Mobile Legends 86 Diamonds',
                            'desc' => 'Top up ML',
                            'price' => 10000,
                            'buyer_product_status' => true,
                            'seller_product_status' => true,
                            'brand' => 'Mobile Legends',
                            'category' => 'Games',
                        ]],
                    ],
                ]);
        });

        $this->artisan('sync:digiflazz')
            ->assertExitCode(0);

        $orderSosmedProduct->refresh();

        $this->assertSame('Produk OrderSosmed', $orderSosmedProduct->name);
        $this->assertSame(2, Product::where('provider_product_id', 'shared-code')->count());
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'shared-code',
            'name' => 'Mobile Legends 86 Diamonds',
        ]);
    }

    protected function seedCategory(int $id, string $slug, ?string $name = null, string $fulfillmentType = 'manual_action'): void
    {
        DB::table('categories')->insert([
            'id' => $id,
            'name' => $name ?? ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'fulfillment_type' => $fulfillmentType,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
