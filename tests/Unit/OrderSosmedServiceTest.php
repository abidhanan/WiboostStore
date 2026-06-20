<?php

namespace Tests\Unit;

use App\Services\OrderSosmedService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderSosmedServiceTest extends TestCase
{
    public function test_place_order_uses_route_endpoint_and_continues_after_connection_error(): void
    {
        config([
            'services.ordersosmed.api_url' => 'https://ordersosmed.id/api-1',
            'services.ordersosmed.api_id' => 'demo-id',
            'services.ordersosmed.api_key' => 'demo-key',
            'services.ordersosmed.secret_key' => 'demo-secret',
        ]);

        $attempts = 0;
        $urls = [];

        Http::fake(function (Request $request) use (&$attempts, &$urls) {
            $attempts++;
            $urls[] = $request->url();

            if ($attempts === 1) {
                throw new ConnectionException('Connection reset by peer');
            }

            return Http::response([
                'status' => true,
                'data' => [
                    'id' => 'OS-12345',
                ],
                'message' => 'Pesanan berhasil dibuat',
            ], 200);
        });

        $result = app(OrderSosmedService::class)->placeOrder('svc-1', 'https://instagram.com/wiboost.store', 620);

        $this->assertSame('https://ordersosmed.id/api-1/order', $urls[0]);
        $this->assertTrue($result['success']);
        $this->assertSame('OS-12345', $result['provider_order_id']);
        $this->assertSame('processing', $result['order_status']);
    }
}
