<?php

namespace Tests\Unit;

use App\Services\XoftwarePaymentService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class XoftwarePaymentServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.url' => 'https://wiboostore.test',
            'wiboost.public_url' => 'https://wiboostore.test',
            'xoftware.base_url' => 'https://xoftware.test',
            'xoftware.api_key' => 'test-api-key',
            'xoftware.signature_secret' => 'test-api-key',
            'xoftware.merchant_id' => 134,
            'xoftware.default_channel' => 'QRIS',
            'xoftware.fee_direction' => 'merchant',
            'xoftware.expired_minutes' => 60,
        ]);
    }

    public function test_it_sends_at_least_one_valid_metadata_product(): void
    {
        $sentPayload = [];

        Http::fake(function (Request $request) use (&$sentPayload) {
            $sentPayload = json_decode($request->body(), true, flags: JSON_THROW_ON_ERROR);

            return Http::response($this->successfulResponse(), 201);
        });

        $service = $this->testableService();
        $result = $service->createForTest([
            'customer' => [
                'id' => '10',
                'name' => 'Pelanggan',
            ],
            'products' => [[
                'product_code' => 'SKU-10',
                'product_name' => 'Produk Uji',
                'product_url' => 'https://wiboostore.test/user/history',
            ]],
        ]);

        $this->assertSame('SKU-10', $sentPayload['metadata']['products'][0]['product_code']);
        $this->assertSame('Produk Uji', $sentPayload['metadata']['products'][0]['product_name']);
        $this->assertSame(
            'https://wiboostore.test/images/wiboost-logo.png',
            $sentPayload['metadata']['products'][0]['product_thumbnail']
        );
        $this->assertCount(1, $sentPayload['metadata']['products']);
        $this->assertSame('https://xoftware.test/pay/TRX-1', $result['checkout_url']);
        $this->assertSame('TRX-1', data_get($result, 'raw.data.reference'));
    }

    public function test_qris_realtime_adds_half_estimated_fee_to_buyer_bill(): void
    {
        config([
            'xoftware.default_channel' => 'QRISREALTIME',
            'xoftware.fee_direction' => 'merchant',
            'xoftware.buyer_fee_share' => 0.5,
            'xoftware.qris_realtime_fee_percent' => 0.007,
            'xoftware.qris_realtime_fee_fixed' => 250,
        ]);

        $sentPayload = [];

        Http::fake(function (Request $request) use (&$sentPayload) {
            $sentPayload = json_decode($request->body(), true, flags: JSON_THROW_ON_ERROR);

            return Http::response([
                'transaction_id' => 'TRX-REALTIME-1',
                'ref_id' => 'WIB-TEST-1',
                'channel_code' => 'QRISREALTIME',
                'amount' => $sentPayload['amount'],
                'status' => 'PENDING',
                'url' => 'https://xoftware.test/pay/TRX-REALTIME-1',
                'fee_preview' => [
                    'gross' => $sentPayload['amount'],
                    'bank' => 71,
                    'app' => 250,
                    'net' => 9840,
                ],
            ], 201);
        });

        $result = $this->testableService()->createForTest([
            'customer' => ['name' => 'Pelanggan'],
            'products' => [[
                'product_code' => 'SKU-REALTIME',
                'product_name' => 'Produk Realtime',
            ]],
        ]);

        $this->assertSame('QRISREALTIME', $sentPayload['channel_code']);
        $this->assertSame('merchant', $sentPayload['fee_direction']);
        $this->assertSame(10161, $sentPayload['amount']);
        $this->assertSame('QRISREALTIME', $result['payment_method']);
        $this->assertSame('QRIS Real Time', $result['payment_name']);
        $this->assertSame(161, data_get($result, 'raw.data.admin_fee'));
        $this->assertSame(10000, data_get($result, 'raw.data.wiboost_base_amount'));
        $this->assertSame(10161, data_get($result, 'raw.data.wiboost_total_payable'));
    }

    public function test_metadata_error_retries_without_dropping_products(): void
    {
        $payloads = [];

        Http::fake(function (Request $request) use (&$payloads) {
            $payloads[] = json_decode($request->body(), true, flags: JSON_THROW_ON_ERROR);

            if (count($payloads) === 1) {
                return Http::response([
                    'error' => true,
                    'message' => 'metadata wajib berisi minimal 1 item',
                ], 422);
            }

            return Http::response($this->successfulResponse(), 201);
        });

        $service = $this->testableService();
        $service->createForTest([
            'customer' => ['name' => 'Pelanggan'],
            'products' => [[
                'product_code' => 'SKU-10',
                'product_name' => 'Produk Uji',
                'product_url' => 'https://wiboostore.test/user/history',
            ]],
        ]);

        $this->assertCount(2, $payloads);
        $this->assertArrayHasKey('customer', $payloads[1]['metadata']);
        $this->assertArrayHasKey('products', $payloads[1]['metadata']);
        $this->assertCount(1, $payloads[1]['metadata']['products']);
        $this->assertSame('Pelanggan', $payloads[1]['metadata']['customer']['name']);
        $this->assertSame('SKU-10', $payloads[1]['metadata']['products'][0]['product_code']);
    }

    public function test_second_metadata_error_retries_with_fallback_product(): void
    {
        $payloads = [];

        Http::fake(function (Request $request) use (&$payloads) {
            $payloads[] = json_decode($request->body(), true, flags: JSON_THROW_ON_ERROR);

            if (count($payloads) < 3) {
                return Http::response([
                    'error' => true,
                    'message' => 'Invalid metadata',
                ], 422);
            }

            return Http::response($this->successfulResponse(), 201);
        });

        $this->testableService()->createForTest([
            'customer' => ['name' => 'Pelanggan'],
            'products' => [[
                'product_code' => 'SKU-10',
                'product_name' => 'Produk Uji',
                'product_url' => 'https://wiboostore.test/user/history',
            ]],
        ]);

        $this->assertCount(3, $payloads);
        $this->assertCount(1, $payloads[2]['metadata']['products']);
        $this->assertSame('WIB-TEST-1', $payloads[2]['metadata']['products'][0]['product_code']);
        $this->assertSame('Produk Wiboost', $payloads[2]['metadata']['products'][0]['product_name']);
    }

    public function test_qris_payload_without_checkout_url_falls_back_to_internal_qris_page(): void
    {
        Http::fake(function (Request $request) {
            return Http::response([
                'transaction_id' => 'TRX-LOCAL-1',
                'ref_id' => 'WIB-TEST-1',
                'channel_code' => 'QRIS',
                'amount' => 10000,
                'status' => 'PENDING',
                'qris_text' => '00020101021226670016ID.CO.TEST.WIBOOST',
                'fee_preview' => [
                    'gross' => 10820,
                ],
            ], 201);
        });

        $result = $this->testableService()->createForTest([
            'customer' => ['name' => 'Pelanggan'],
            'products' => [[
                'product_code' => 'SKU-10',
                'product_name' => 'Produk Uji',
                'product_url' => 'https://wiboostore.test/user/history',
            ]],
        ]);

        $this->assertSame('https://wiboostore.test/user/qris/WIB-TEST-1', $result['checkout_url']);
        $this->assertSame('00020101021226670016ID.CO.TEST.WIBOOST', data_get($result, 'raw.data.qris_text'));
    }

    public function test_non_metadata_error_is_not_retried(): void
    {
        Http::fake([
            '*' => Http::response([
                'error' => true,
                'message' => 'Invalid signature',
            ], 401),
        ]);

        try {
            $this->testableService()->createForTest([
                'customer' => ['name' => 'Pelanggan'],
                'products' => [[
                    'product_code' => 'SKU-10',
                    'product_name' => 'Produk Uji',
                ]],
            ]);

            $this->fail('Expected the Xoftware request to fail.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Invalid signature', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_err_54344_returns_actionable_provider_message_without_retry(): void
    {
        Http::fake([
            '*' => Http::response([
                'error' => true,
                'message' => 'create qris gagal [ERR:54344]',
            ], 422),
        ]);

        try {
            $this->testableService()->createForTest([
                'customer' => ['name' => 'Pelanggan'],
                'products' => [[
                    'product_code' => 'SKU-10',
                    'product_name' => 'Produk Uji',
                ]],
            ]);

            $this->fail('Expected the Xoftware request to fail.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('provider QRIS gagal membuat transaksi', $exception->getMessage());
            $this->assertStringContainsString('ERR:54344', $exception->getMessage());
            $this->assertStringContainsString('Pengajuan Integrasi', $exception->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_response_without_checkout_url_or_qris_payload_is_rejected(): void
    {
        Http::fake([
            '*' => Http::response([
                'transaction_id' => 'TRX-NO-QR',
                'ref_id' => 'WIB-TEST-1',
                'channel_code' => 'QRIS',
                'amount' => 10000,
                'status' => 'PENDING',
            ], 201),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Xoftware tidak mengembalikan URL atau data QRIS.');

        $this->testableService()->createForTest([
            'customer' => ['name' => 'Pelanggan'],
            'products' => [[
                'product_code' => 'SKU-10',
                'product_name' => 'Produk Uji',
            ]],
        ]);
    }

    public function test_it_reads_channels_and_balance_without_creating_transaction(): void
    {
        $requests = [];

        Http::fake(function (Request $request) use (&$requests) {
            $requests[] = [
                'method' => $request->method(),
                'url' => $request->url(),
                'body' => $request->body(),
            ];

            if (str_ends_with($request->url(), '/v1/api/channels')) {
                return Http::response([
                    'data' => [
                        ['code' => 'QRIS', 'name' => 'QRIS'],
                    ],
                ]);
            }

            return Http::response([
                'data' => [
                    'merchant_id' => 134,
                    'available' => 10000,
                ],
            ]);
        });

        $service = new XoftwarePaymentService();
        $channels = $service->paymentChannels();
        $balance = $service->balance();

        $this->assertSame('QRIS', data_get($channels, 'data.0.code'));
        $this->assertSame(10000, data_get($balance, 'data.available'));
        $this->assertSame('GET', $requests[0]['method']);
        $this->assertSame('POST', $requests[1]['method']);
        $this->assertSame('', $requests[1]['body']);
        Http::assertSentCount(2);
    }

    private function testableService(): XoftwarePaymentService
    {
        return new class extends XoftwarePaymentService
        {
            public function createForTest(array $metadata): array
            {
                return $this->createPayment(
                    merchantRef: 'WIB-TEST-1',
                    amount: 10000,
                    note: 'Pembayaran pengujian',
                    metadata: $metadata,
                );
            }
        };
    }

    private function successfulResponse(): array
    {
        return [
            'transaction_id' => 'TRX-1',
            'ref_id' => 'WIB-TEST-1',
            'channel_code' => 'QRIS',
            'amount' => 10000,
            'status' => 'PENDING',
            'url' => 'https://xoftware.test/pay/TRX-1',
            'fee_preview' => [
                'gross' => 10500,
            ],
        ];
    }
}
