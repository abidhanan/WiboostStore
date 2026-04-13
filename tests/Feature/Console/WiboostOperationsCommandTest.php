<?php

namespace Tests\Feature\Console;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DigiflazzService;
use App\Services\OrderSosmedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
        ]);

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
                && ($payload['embeds'][0]['title'] ?? null) === 'Laporan maintenance Wiboost';
        });
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

        $product = Product::where('provider_product_id', '1234')->first();

        $this->assertNotNull($product);
        $this->assertSame('ordersosmed', $product->provider_source);
        $this->assertSame(1, $product->category_id);
        $this->assertSame(100, $product->provider_quantity);
        $this->assertSame(2800.0, (float) $product->price);
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

        $product = Product::where('provider_product_id', 'ax-data')->first();

        $this->assertNotNull($product);
        $this->assertSame(3, $product->category_id);
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
