<?php

namespace Tests\Feature\Console;

use App\Models\Category;
use App\Mail\OrderSuccessMail;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DigiflazzService;
use App\Services\OrderSosmedGuestCatalogService;
use App\Services\OrderSosmedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class WiboostOperationsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_refund_command_refunds_stale_paid_pending_api_transactions(): void
    {
        $this->seedCategory(1, 'default-category');

        $user = User::factory()->create([
            'balance' => 5000,
        ]);

        $product = Product::create([
            'category_id' => 1,
            'provider_id' => 'digiflazz',
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ML86',
            'process_type' => 'api',
            'name' => 'Mobile Legends 86 Diamonds',
            'slug' => 'mobile-legends-86-diamonds-auto-refund',
            'description' => 'Produk API',
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

    public function test_auto_refund_command_skips_manual_and_stock_transactions(): void
    {
        $this->seedCategory(1, 'default-category');

        $user = User::factory()->create([
            'balance' => 5000,
        ]);

        $manualProduct = Product::create([
            'category_id' => 1,
            'provider_id' => null,
            'process_type' => 'manual',
            'name' => 'Jasa Manual',
            'slug' => 'jasa-manual-no-refund',
            'description' => 'Produk manual',
            'price' => 10000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $stockProduct = Product::create([
            'category_id' => 1,
            'provider_id' => null,
            'process_type' => 'account',
            'name' => 'Akun Premium',
            'slug' => 'akun-premium-no-refund',
            'description' => 'Produk stok',
            'price' => 15000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        foreach ([$manualProduct, $stockProduct] as $index => $product) {
            $transaction = Transaction::create([
                'invoice_number' => 'WIB-NO-REFUND-00' . ($index + 1),
                'user_id' => $user->id,
                'product_id' => $product->id,
                'amount' => $product->price,
                'target_data' => 'target',
                'payment_status' => 'paid',
                'order_status' => 'pending',
            ]);

            $transaction->forceFill([
                'updated_at' => now()->subHours(25),
            ])->save();
        }

        $this->artisan('wiboost:auto-refund')
            ->assertExitCode(0);

        $this->assertSame(5000.0, (float) $user->fresh()->balance);
        $this->assertSame(0, \App\Models\WalletHistory::where('type', 'refund')->count());
        $this->assertSame(2, Transaction::where('order_status', 'pending')->count());
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
            'tripay.api_key' => 'tripay-api',
            'tripay.private_key' => 'tripay-private',
            'tripay.merchant_code' => 'T1234',
            'wiboost.public_url' => 'https://demo.wiboost.test',
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
                && str_contains(($payload['embeds'][0]['fields'][6]['value'] ?? ''), 'Tripay');
        });
    }

    public function test_check_provider_orders_marks_digiflazz_processing_order_success(): void
    {
        Mail::fake();
        $this->seedCategory(1, 'top-up-game');

        $user = User::factory()->create();
        $product = Product::create([
            'category_id' => 1,
            'provider_id' => 'digiflazz',
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ML86',
            'process_type' => 'api',
            'name' => 'Mobile Legends 86 Diamonds',
            'slug' => 'mobile-legends-86-diamonds-status',
            'description' => 'Produk game',
            'price' => 12000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $transaction = Transaction::create([
            'invoice_number' => 'WIB-CHECK-DIGI',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 12000,
            'target_data' => '123456789 (1234)',
            'order_input_data' => ['fields' => [
                ['name' => 'game_user_id', 'label' => 'User ID game', 'value' => '123456789'],
                ['name' => 'game_zone_id', 'label' => 'Zone ID / Server ID', 'value' => '1234'],
            ]],
            'payment_status' => 'paid',
            'order_status' => 'processing',
            'payment_method' => 'wallet',
        ]);

        $this->mock(DigiflazzService::class, function (MockInterface $mock) {
            $mock->shouldReceive('checkOrderStatus')
                ->once()
                ->with('ML86', '1234567891234', 'WIB-CHECK-DIGI')
                ->andReturn([
                    'success' => true,
                    'message' => 'Transaksi sukses',
                    'provider_order_id' => 'SN-1',
                    'order_status' => 'success',
                    'raw' => ['data' => ['status' => 'Sukses']],
                ]);
        });

        $this->artisan('wiboost:check-provider-orders')
            ->assertExitCode(0);

        $this->assertSame('success', $transaction->fresh()->order_status);
        Mail::assertSent(OrderSuccessMail::class);
    }

    public function test_check_provider_orders_refunds_failed_ordersosmed_processing_order(): void
    {
        $this->seedCategory(1, 'suntik-sosmed');

        $user = User::factory()->create(['balance' => 0]);
        $product = Product::create([
            'category_id' => 1,
            'provider_id' => 'ordersosmed',
            'provider_source' => 'ordersosmed',
            'provider_product_id' => 'svc-1',
            'provider_quantity' => 100,
            'process_type' => 'api',
            'name' => 'Instagram Like Indonesia',
            'slug' => 'instagram-like-indonesia-status',
            'description' => 'Produk sosmed',
            'price' => 10000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $transaction = Transaction::create([
            'invoice_number' => 'WIB-CHECK-OS',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 10000,
            'target_data' => '@target',
            'response_data' => ['provider_order_id' => 'OS-1'],
            'payment_status' => 'paid',
            'order_status' => 'processing',
            'payment_method' => 'wallet',
        ]);

        $this->mock(OrderSosmedService::class, function (MockInterface $mock) {
            $mock->shouldReceive('checkOrderStatus')
                ->once()
                ->with('OS-1')
                ->andReturn([
                    'success' => false,
                    'message' => 'Order canceled provider.',
                    'provider_order_id' => 'OS-1',
                    'order_status' => 'failed',
                    'raw' => ['status' => 'Canceled'],
                ]);
        });

        $this->artisan('wiboost:check-provider-orders')
            ->assertExitCode(0);

        $this->assertSame('failed', $transaction->fresh()->order_status);
        $this->assertSame(10000.0, (float) $user->fresh()->balance);
        $this->assertDatabaseHas('wallet_histories', [
            'invoice_number' => 'WIB-CHECK-OS',
            'type' => 'refund',
        ]);
    }

    public function test_readiness_check_passes_when_production_requirements_are_configured(): void
    {
        config([
            'app.env' => 'production',
            'app.debug' => false,
            'wiboost.public_url' => 'https://wiboost.example',
            'tripay.api_key' => 'tripay-api',
            'tripay.private_key' => 'tripay-private',
            'tripay.merchant_code' => 'T1234',
            'mail.default' => 'smtp',
            'mail.from.address' => 'support@wiboost.example',
            'mail.mailers.smtp.host' => 'smtp.example',
            'services.digiflazz.username' => 'digiflazz-user',
            'services.digiflazz.key' => 'digiflazz-key',
            'services.ordersosmed.api_url' => 'https://ordersosmed.id/api-1',
            'services.ordersosmed.api_id' => 'os-id',
            'services.ordersosmed.api_key' => 'os-key',
            'services.discord.webhook_url' => 'https://discord.test/webhook',
            'queue.default' => 'database',
        ]);

        $this->artisan('wiboost:readiness-check --strict')
            ->assertExitCode(0);
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

    public function test_sync_digiflazz_update_existing_only_preserves_mapping_and_skips_new_skus(): void
    {
        $this->seedCategory(1, 'top-up-game');
        $this->seedCategory(2, 'mobile-legends', 'Mobile Legends', parentId: 1);

        $product = Product::create([
            'category_id' => 2,
            'provider_id' => 'digiflazz',
            'provider_source' => 'digiflazz',
            'process_type' => 'api',
            'name' => 'Nama Lama',
            'slug' => 'nama-lama',
            'description' => 'Deskripsi lama',
            'price' => 9000,
            'provider_product_id' => 'ML-86',
            'status' => 'active',
            'is_active' => true,
            'image' => 'products/logo.png',
            'stock_reminder' => 0,
        ]);

        $this->mock(DigiflazzService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPriceList')
                ->twice()
                ->andReturn([
                    'success' => true,
                    'raw' => ['data' => [
                        [
                            'buyer_sku_code' => 'ML-86',
                            'product_name' => 'Mobile Legends 86 Diamonds',
                            'desc' => 'Deskripsi baru',
                            'price' => 10000,
                            'buyer_product_status' => false,
                            'seller_product_status' => true,
                        ],
                        [
                            'buyer_sku_code' => 'NEW-SKU',
                            'product_name' => 'Produk Baru',
                            'desc' => 'Belum dipetakan',
                            'price' => 20000,
                            'buyer_product_status' => true,
                            'seller_product_status' => true,
                        ],
                    ]],
                ]);
        });

        $this->artisan('sync:digiflazz', ['--update-existing-only' => true])
            ->assertExitCode(0);

        $product->refresh();
        $this->assertSame('Nama Lama', $product->name);
        $this->assertSame(9000.0, (float) $product->price);

        $this->artisan('sync:digiflazz', ['--update-existing-only' => true, '--execute' => true])
            ->assertExitCode(0);

        $product->refresh();
        $this->assertSame(2, $product->category_id);
        $this->assertSame('nama-lama', $product->slug);
        $this->assertSame('products/logo.png', $product->image);
        $this->assertSame('ML-86', $product->provider_product_id);
        $this->assertSame('Mobile Legends 86 Diamonds', $product->name);
        $this->assertSame('Deskripsi baru', $product->description);
        $this->assertSame(10000.0, (float) $product->price);
        $this->assertFalse($product->is_active);
        $this->assertSame('inactive', $product->status);
        $this->assertDatabaseMissing('products', ['provider_product_id' => 'NEW-SKU']);
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
        $this->assertSame(10000.0, (float) $existingProduct->price);
        $this->assertSame(10500.0, (float) $existingProduct->selling_price);
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
                        'avg_time' => '6 jam 20 menit',
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
        $this->assertSame(2500.0, (float) $product->price);
        $this->assertSame(3750.0, (float) $product->selling_price);
        $this->assertStringContainsString('Waktu rata-rata: 6 jam 20 menit', $product->description);
        $this->assertSame('Belum ada order sukses', $product->ordersosmed_average_time);
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

    public function test_sync_digiflazz_command_can_match_products_to_existing_local_game_and_quota_categories(): void
    {
        $this->seedCategory(1, 'kuota-murah', 'Kuota Murah');
        $this->seedCategory(2, 'top-up-game', 'Top Up Game');
        $this->seedCategory(3, 'suntik-sosmed', 'Suntik Sosmed');
        $this->seedCategory(4, 'axis', 'Axis', parentId: 1);
        $this->seedCategory(5, 'axis-mini', 'Mini', parentId: 4);
        $this->seedCategory(6, 'axis-owsem', 'Owsem', parentId: 4);
        $this->seedCategory(7, 'axis-umum', 'Umum', parentId: 4);
        $this->seedCategory(8, 'by-u', 'by.U', parentId: 1);
        $this->seedCategory(9, 'by-u-super-kaget', 'Super Kaget', parentId: 8);
        $this->seedCategory(10, 'indosat', 'Indosat', parentId: 1);
        $this->seedCategory(11, 'indosat-freedom-combo', 'Freedom Combo', parentId: 10);
        $this->seedCategory(12, 'smartfren', 'Smartfren', parentId: 1);
        $this->seedCategory(13, 'smartfren-unlimited', 'Unlimited', parentId: 12);
        $this->seedCategory(14, 'telkomsel', 'Telkomsel', parentId: 1);
        $this->seedCategory(15, 'telkomsel-combo-sakti', 'Combo Sakti', parentId: 14);
        $this->seedCategory(16, 'tri', 'Tri', parentId: 1);
        $this->seedCategory(17, 'tri-always-on', 'Always On', parentId: 16);
        $this->seedCategory(18, 'xl', 'XL', parentId: 1);
        $this->seedCategory(19, 'xl-xtra-combo', 'Xtra Combo', parentId: 18);
        $this->seedCategory(20, 'cod-mobile', 'COD Mobile', parentId: 2);
        $this->seedCategory(21, 'fc-mobile', 'FC Mobile', parentId: 2);
        $this->seedCategory(22, 'free-fire', 'Free Fire', parentId: 2);
        $this->seedCategory(23, 'free-fire-membership', 'Membership', parentId: 22);
        $this->seedCategory(24, 'free-fire-max', 'Free Fire Max', parentId: 2);
        $this->seedCategory(25, 'free-fire-max-umum', 'Umum', parentId: 24);
        $this->seedCategory(26, 'genshin-impact', 'Genshin Impact', parentId: 2);
        $this->seedCategory(27, 'genshin-impact-indonesia', 'Indonesia', parentId: 26);
        $this->seedCategory(28, 'genshin-impact-membership', 'Membership', parentId: 26);
        $this->seedCategory(43, 'genshin-impact-malaysia', 'Malaysia', parentId: 26);
        $this->seedCategory(29, 'honor-of-kings', 'Honor of Kings', parentId: 2);
        $this->seedCategory(30, 'honor-of-kings-kombinasi', 'Kombinasi', parentId: 29);
        $this->seedCategory(31, 'lol-pc', 'LoL PC', parentId: 2);
        $this->seedCategory(32, 'lol-pc-malaysia', 'Malaysia', parentId: 31);
        $this->seedCategory(47, 'lol-pc-umum', 'Umum', parentId: 31);
        $this->seedCategory(33, 'mobile-legends', 'Mobile Legends', parentId: 2);
        $this->seedCategory(34, 'mobile-legends-malaysia', 'Malaysia', parentId: 33);
        $this->seedCategory(35, 'mobile-legends-membership', 'Membership', parentId: 33);
        $this->seedCategory(36, 'pubg-mobile', 'PUBG Mobile', parentId: 2);
        $this->seedCategory(37, 'pubg-mobile-global', 'Global', parentId: 36);
        $this->seedCategory(38, 'valorant', 'Valorant', parentId: 2);
        $this->seedCategory(39, 'valorant-malaysia', 'Malaysia', parentId: 38);
        $this->seedCategory(40, 'sosmed-mobile-legends', 'Mobile Legends', parentId: 3);
        $this->seedCategory(41, 'mobile-legends-umum', 'Umum', parentId: 33);
        $this->seedCategory(42, 'mobile-legends-indonesia', 'Indonesia', parentId: 33);

        Product::create([
            'category_id' => 14,
            'provider_id' => 'digiflazz',
            'provider_source' => 'digiflazz',
            'process_type' => 'api',
            'name' => 'Telkomsel Pulsa Lama',
            'slug' => 'telkomsel-pulsa-lama',
            'description' => 'Produk lama yang tidak boleh ikut kategori kuota.',
            'price' => 12000,
            'provider_product_id' => 'tsel-pulsa',
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
                        'data' => [
                            [
                                'buyer_sku_code' => 'axis-mini',
                                'product_name' => 'Axis Mini 2 GB',
                                'desc' => 'Paket data Axis Mini',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Axis',
                                'category' => 'Data',
                            ],
                            [
                                'buyer_sku_code' => 'axis-owsem',
                                'product_name' => 'Axis Owsem 8 GB',
                                'desc' => 'Paket data Axis Owsem',
                                'price' => 9000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Axis',
                                'category' => 'Data',
                            ],
                            [
                                'buyer_sku_code' => 'byu-super-kaget',
                                'product_name' => 'by.U Super Kaget 10 GB',
                                'desc' => 'Paket internet by.U',
                                'price' => 11000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'by.U',
                                'category' => 'Data',
                            ],
                            [
                                'buyer_sku_code' => 'indosat-combo',
                                'product_name' => 'Indosat Freedom Combo 15 GB',
                                'desc' => 'Paket data Indosat',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'IM3',
                                'category' => 'Data',
                            ],
                            [
                                'buyer_sku_code' => 'smartfren-unlimited',
                                'product_name' => 'Smartfren Unlimited Harian',
                                'desc' => 'Paket internet Smartfren',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Smartfren',
                                'category' => 'Data',
                            ],
                            [
                                'buyer_sku_code' => 'tsel-combo-sakti',
                                'product_name' => 'Telkomsel Combo Sakti 17 GB',
                                'desc' => 'Paket data Telkomsel',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Telkomsel',
                                'category' => 'Data',
                            ],
                            [
                                'buyer_sku_code' => 'tri-aon',
                                'product_name' => 'Tri Always On 6 GB',
                                'desc' => 'Paket data Tri',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Tri',
                                'category' => 'Data',
                            ],
                            [
                                'buyer_sku_code' => 'xl-xtra-combo',
                                'product_name' => 'XL Xtra Combo 12 GB',
                                'desc' => 'Paket data XL',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'XL',
                                'category' => 'Data',
                            ],
                            [
                                'buyer_sku_code' => 'codm',
                                'product_name' => 'COD Mobile 80 CP',
                                'desc' => 'Top up game',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'COD Mobile',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'fc-mobile',
                                'product_name' => 'FC Mobile 40 Points',
                                'desc' => 'Top up game',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'FC Mobile',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'ff-member',
                                'product_name' => 'Free Fire Membership Mingguan',
                                'desc' => 'Weekly membership',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Free Fire',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'ffmax',
                                'product_name' => 'Free Fire Max 70 Diamond',
                                'desc' => 'Top up game',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Free Fire Max',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'genshin-id',
                                'product_name' => 'Genshin Impact Indonesia 60 Genesis Crystal',
                                'desc' => 'Top up game',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Genshin Impact',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'genesis-my',
                                'product_name' => 'Genesis Crystal MY 60',
                                'desc' => 'Top up game',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Games',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'genshin-welkin',
                                'product_name' => 'Genshin Impact Blessing Welkin Moon',
                                'desc' => 'Membership',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Genshin Impact',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'hok-kombinasi',
                                'product_name' => 'Honor of Kings Kombinasi Token',
                                'desc' => 'Top up game',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Honor of Kings',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'lol-pc-my',
                                'product_name' => 'LoL PC Malaysia RP',
                                'desc' => 'Top up game',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'LoL PC',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'riot-rp',
                                'product_name' => 'Riot Points 575 RP',
                                'desc' => 'League of Legends PC',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Riot Points',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'riot-rp-my',
                                'product_name' => 'Riot Points MY 575 RP',
                                'desc' => 'League of Legends PC',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Riot Points',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'ml-umum',
                                'product_name' => 'Mobile Legends 86 Diamonds',
                                'desc' => 'Masukkan User ID dan Zone ID',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Mobile Legends',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'ml-desc-only',
                                'product_name' => '86 Diamonds',
                                'desc' => 'Top up Mobile Legends',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Games',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'ml-id',
                                'product_name' => 'Mobile Legends Indonesia 86 Diamonds',
                                'desc' => 'Masukkan User ID dan Zone ID',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Mobile Legends',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'ml-my',
                                'product_name' => 'Mobile Legends Malaysia 86 Diamonds',
                                'desc' => 'Top up game',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Mobile Legends',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'ml-pass',
                                'product_name' => 'Mobile Legends Weekly Diamond Pass',
                                'desc' => 'Membership',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Mobile Legends',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'pubg-global',
                                'product_name' => 'PUBG Mobile Global UC',
                                'desc' => 'Top up game',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'PUBG Mobile',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'valorant-my',
                                'product_name' => 'Valorant Malaysia VP',
                                'desc' => 'Top up game',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Valorant',
                                'category' => 'Games',
                            ],
                            [
                                'buyer_sku_code' => 'tsel-pulsa',
                                'product_name' => 'Telkomsel Pulsa 10.000',
                                'desc' => 'Pulsa reguler',
                                'price' => 10000,
                                'buyer_product_status' => true,
                                'seller_product_status' => true,
                                'brand' => 'Telkomsel',
                                'category' => 'Pulsa',
                            ],
                        ],
                    ],
                ]);
        });

        $this->artisan('sync:digiflazz', [
            '--match-local-categories' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'axis-mini',
            'category_id' => 5,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'axis-owsem',
            'category_id' => 6,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'byu-super-kaget',
            'category_id' => 9,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'indosat-combo',
            'category_id' => 11,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'smartfren-unlimited',
            'category_id' => 13,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'tsel-combo-sakti',
            'category_id' => 15,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'tri-aon',
            'category_id' => 17,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'xl-xtra-combo',
            'category_id' => 19,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'codm',
            'category_id' => 20,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'fc-mobile',
            'category_id' => 21,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ff-member',
            'category_id' => 23,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ffmax',
            'category_id' => 25,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'genshin-id',
            'category_id' => 27,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'genesis-my',
            'category_id' => 43,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'genshin-welkin',
            'category_id' => 28,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'hok-kombinasi',
            'category_id' => 30,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'lol-pc-my',
            'category_id' => 32,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'riot-rp',
            'category_id' => 47,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'riot-rp-my',
            'category_id' => 32,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ml-umum',
            'category_id' => 41,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ml-desc-only',
            'category_id' => 41,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ml-id',
            'category_id' => 42,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ml-my',
            'category_id' => 34,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ml-pass',
            'category_id' => 35,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'pubg-global',
            'category_id' => 37,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'valorant-my',
            'category_id' => 39,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'tsel-pulsa',
            'category_id' => 14,
            'status' => 'inactive',
            'is_active' => false,
        ]);
        $this->assertDatabaseMissing('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ml-my',
            'category_id' => 40,
        ]);
    }

    public function test_sync_ordersosmed_command_stops_when_private_api_is_unavailable(): void
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

        $this->artisan('sync:ordersosmed')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('products', [
            'provider_source' => 'ordersosmed',
        ]);
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

    public function test_sync_ordersosmed_command_can_import_selected_provider_categories_only(): void
    {
        $this->seedCategory(1, 'social-media', 'Social Media');
        $this->seedCategory(2, 'sosmed-2', 'SOSMED 2');
        $this->seedCategory(3, 'sosmed-3', 'SOSMED 3');
        $this->seedCategory(4, 'sosmed-4', 'SOSMED 4');
        $this->seedCategory(5, 'sosmed-5', 'SOSMED 5');

        $this->mock(OrderSosmedService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getServices')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'OK',
                    'data' => [
                        [
                            'id' => 2001,
                            'category_id' => 2,
                            'category_name' => 'Instagram Like',
                            'service_name' => 'Instagram Like Indonesia',
                            'price' => 10000,
                            'min' => 100,
                            'max' => 10000,
                        ],
                        [
                            'id' => 3001,
                            'category_id' => 3,
                            'category_name' => 'Instagram Followers',
                            'service_name' => 'Instagram Followers Indonesia',
                            'price' => 25000,
                            'min' => 100,
                            'max' => 10000,
                        ],
                        [
                            'id' => 4001,
                            'category_id' => 4,
                            'category_name' => 'Instagram Comments',
                            'service_name' => 'Instagram Comments Indonesia',
                            'price' => 30000,
                            'min' => 10,
                            'max' => 1000,
                        ],
                        [
                            'id' => 5001,
                            'category_id' => 5,
                            'category_name' => 'TikTok Views',
                            'service_name' => 'TikTok Views Indonesia',
                            'price' => 5000,
                            'min' => 100,
                            'max' => 100000,
                        ],
                    ],
                ]);
        });

        $this->artisan('sync:ordersosmed', [
            '--provider-category-ids' => '2,3,5',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '2001',
            'category_id' => 2,
            'price' => 1000,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '3001',
            'category_id' => 3,
            'price' => 2500,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '5001',
            'category_id' => 5,
            'price' => 500,
        ]);
        $this->assertDatabaseMissing('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '4001',
        ]);
    }

    public function test_sync_ordersosmed_command_can_force_selected_provider_categories_into_target_category(): void
    {
        $this->seedCategory(1, 'social-media', 'Social Media');
        $this->seedCategory(2, 'sosmed-2', 'SOSMED 2');

        $this->mock(OrderSosmedService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getServices')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'OK',
                    'data' => [
                        [
                            'id' => 6735,
                            'category_id' => 6735,
                            'category_name' => 'Instagram Likes [Worldwide] No Garansi',
                            'service_name' => 'Instagram Like Indonesia',
                            'price' => 10000,
                            'min' => 100,
                            'max' => 10000,
                        ],
                        [
                            'id' => 6276,
                            'category_id' => 6276,
                            'category_name' => 'TikTok View',
                            'service_name' => 'TikTok Views Indonesia',
                            'price' => 5000,
                            'min' => 100,
                            'max' => 100000,
                        ],
                    ],
                ]);
        });

        $this->artisan('sync:ordersosmed', [
            '--provider-category-ids' => '6735',
            '--target-category' => 'SOSMED 2',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '6735',
            'category_id' => 2,
        ]);
        $this->assertDatabaseMissing('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '6276',
        ]);
    }

    public function test_sync_ordersosmed_command_can_match_services_to_existing_local_categories(): void
    {
        $this->seedCategory(1, 'suntik-sosmed', 'Suntik Sosmed');
        $this->seedCategory(2, 'instagram', 'Instagram', parentId: 1);
        $this->seedCategory(3, 'instagram-followers', 'Followers', parentId: 2);
        $this->seedCategory(4, 'instagram-followers-global', 'Global', parentId: 3);
        $this->seedCategory(5, 'instagram-followers-indonesia', 'Indonesia', parentId: 3);
        $this->seedCategory(6, 'instagram-like', 'Like', parentId: 2);
        $this->seedCategory(7, 'instagram-like-global', 'Global', parentId: 6);
        $this->seedCategory(8, 'instagram-like-indonesia', 'Indonesia', parentId: 6);
        $this->seedCategory(9, 'instagram-views', 'Views', parentId: 2);
        $this->seedCategory(10, 'instagram-lainnya', 'Lainnya', parentId: 2);
        $this->seedCategory(11, 'tiktok', 'TikTok', parentId: 1);
        $this->seedCategory(12, 'tiktok-views', 'Views', parentId: 11);
        $this->seedCategory(13, 'youtube', 'YouTube', parentId: 1);
        $this->seedCategory(14, 'youtube-subscribers', 'Subscribers', parentId: 13);

        Product::create([
            'category_id' => 10,
            'provider_id' => 'ordersosmed',
            'provider_source' => 'ordersosmed',
            'provider_product_id' => 'old-mixed',
            'provider_quantity' => 100,
            'process_type' => 'api',
            'name' => 'Telegram Reaction Lama Salah Kategori',
            'slug' => 'telegram-reaction-lama-salah-kategori',
            'description' => 'Produk lama dari sync fuzzy sebelumnya.',
            'price' => 10000,
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
                    'data' => [
                        [
                            'id' => 9001,
                            'category_id' => 6767,
                            'category_name' => 'Instagram [INDIA]',
                            'service_name' => 'Instagram Followers Indonesia',
                            'price' => 10000,
                            'min' => 100,
                            'max' => 10000,
                        ],
                        [
                            'id' => 9002,
                            'category_id' => 6766,
                            'category_name' => 'Instagram [USA]',
                            'service_name' => 'Instagram Followers USA',
                            'price' => 5000,
                            'min' => 100,
                            'max' => 100000,
                        ],
                        [
                            'id' => 9003,
                            'category_id' => 6735,
                            'category_name' => 'Instagram Likes [Worldwide] No Garansi',
                            'service_name' => 'Instagram Like Indonesia',
                            'price' => 15000,
                            'min' => 100,
                            'max' => 10000,
                        ],
                        [
                            'id' => 9004,
                            'category_id' => 15398,
                            'category_name' => 'Instagram - Reactions + Views',
                            'service_name' => 'Instagram Likes + Views',
                            'price' => 8000,
                            'min' => 100,
                            'max' => 10000,
                        ],
                        [
                            'id' => 9009,
                            'category_id' => 6735,
                            'category_name' => 'Instagram Likes [Worldwide] No Garansi',
                            'service_name' => 'Instagram Like Global',
                            'description' => 'Catatan provider: jangan isi komentar, cukup link postingan publik.',
                            'price' => 15000,
                            'min' => 100,
                            'max' => 10000,
                        ],
                        [
                            'id' => 9005,
                            'category_id' => 6276,
                            'category_name' => 'TikTok View',
                            'service_name' => 'TikTok Views Indonesia',
                            'price' => 5000,
                            'min' => 100,
                            'max' => 100000,
                        ],
                        [
                            'id' => 9006,
                            'category_id' => 6803,
                            'category_name' => 'YouTube Views - Worldwide',
                            'service_name' => 'YouTube Subscribers',
                            'price' => 20000,
                            'min' => 100,
                            'max' => 100000,
                        ],
                        [
                            'id' => 9008,
                            'category_id' => 6750,
                            'category_name' => 'Instagram Comments',
                            'service_name' => 'Instagram Comments Indonesia',
                            'price' => 12000,
                            'min' => 10,
                            'max' => 1000,
                        ],
                        [
                            'id' => 9007,
                            'category_id' => 6311,
                            'category_name' => 'Facebook Page Likes & Page Followers',
                            'service_name' => 'Facebook Page Likes',
                            'price' => 15000,
                            'min' => 100,
                            'max' => 10000,
                        ],
                    ],
                ]);
        });

        $this->artisan('sync:ordersosmed', [
            '--match-local-categories' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '9001',
            'category_id' => 5,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '9002',
            'category_id' => 4,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '9003',
            'category_id' => 8,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '9004',
            'category_id' => 10,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '9009',
            'category_id' => 7,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '9005',
            'category_id' => 12,
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '9006',
            'category_id' => 14,
        ]);
        $this->assertDatabaseMissing('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '9007',
        ]);
        $this->assertDatabaseMissing('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '9008',
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => 'old-mixed',
            'status' => 'inactive',
            'is_active' => false,
        ]);
    }

    public function test_remove_ordersosmed_comment_services_command_deletes_only_comment_services(): void
    {
        $this->seedCategory(1, 'suntik-sosmed');

        Product::create([
            'category_id' => 1,
            'provider_id' => 'ordersosmed',
            'provider_source' => 'ordersosmed',
            'provider_product_id' => 'comment-1',
            'process_type' => 'api',
            'name' => 'Instagram Comments Indonesia',
            'slug' => 'instagram-comments-indonesia',
            'description' => 'Custom komentar',
            'price' => 10000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        Product::create([
            'category_id' => 1,
            'provider_id' => 'ordersosmed',
            'provider_source' => 'ordersosmed',
            'provider_product_id' => 'like-1',
            'process_type' => 'api',
            'name' => 'Instagram Like Indonesia',
            'slug' => 'instagram-like-indonesia-cleanup',
            'description' => 'Like biasa',
            'price' => 8000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        Product::create([
            'category_id' => 1,
            'provider_id' => 'digiflazz',
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'comment-digi',
            'process_type' => 'api',
            'name' => 'Game Comment Pack',
            'slug' => 'game-comment-pack',
            'description' => 'Produk bukan OrderSosmed',
            'price' => 7000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $this->artisan('wiboost:remove-ordersosmed-comment-services', [
            '--execute' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseMissing('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => 'comment-1',
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'ordersosmed',
            'provider_product_id' => 'like-1',
        ]);
        $this->assertDatabaseHas('products', [
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'comment-digi',
        ]);
    }

    public function test_ordersosmed_list_categories_command_shows_provider_category_ids(): void
    {
        $this->mock(OrderSosmedService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getServices')
                ->once()
                ->andReturn([
                    'success' => true,
                    'message' => 'OK',
                    'data' => [
                        [
                            'id' => 1,
                            'category_id' => 6735,
                            'category_name' => 'Instagram Likes [Worldwide] No Garansi',
                            'service_name' => 'Instagram Like Indonesia',
                        ],
                        [
                            'id' => 2,
                            'category_id' => 6735,
                            'category_name' => 'Instagram Likes [Worldwide] No Garansi',
                            'service_name' => 'Instagram Like Luar Negeri',
                        ],
                        [
                            'id' => 3,
                            'category_id' => 6276,
                            'category_name' => 'TikTok View',
                            'service_name' => 'TikTok Views Indonesia',
                        ],
                    ],
                ]);
        });

        $this->artisan('ordersosmed:list-categories', [
            '--search' => 'instagram',
            '--limit' => '0',
        ])
            ->expectsOutputToContain('6735')
            ->assertExitCode(0);
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

    protected function seedCategory(int $id, string $slug, ?string $name = null, string $fulfillmentType = 'manual_action', ?int $parentId = null): void
    {
        DB::table('categories')->insert([
            'id' => $id,
            'parent_id' => $parentId,
            'name' => $name ?? ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'fulfillment_type' => $fulfillmentType,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
