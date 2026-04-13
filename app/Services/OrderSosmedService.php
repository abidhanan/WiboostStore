<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderSosmedService
{
    protected string $apiUrl;
    protected string $apiId;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiUrl = trim((string) config('services.ordersosmed.api_url'));
        $this->apiId = trim((string) config('services.ordersosmed.api_id'));
        $this->apiKey = trim((string) config('services.ordersosmed.api_key'));
    }

    public function isConfigured(): bool
    {
        return $this->apiUrl !== '' && $this->apiId !== '' && $this->apiKey !== '';
    }

    public function getServices(): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Kredensial OrderSosmed belum diatur.',
                'data' => [],
            ];
        }

        try {
            $response = Http::asForm()->timeout(30)->acceptJson()->post($this->apiUrl, [
                'api_id' => $this->apiId,
                'api_key' => $this->apiKey,
                'action' => 'services',
            ]);

            $body = $response->json() ?? [];
            $data = $body['data'] ?? $body['services'] ?? $body;
            $services = is_array($data) && array_is_list($data) ? $data : [];
            $success = $response->successful() && $services !== [];
            $message = $body['message'] ?? ($success ? 'Daftar layanan berhasil diambil.' : 'Provider tidak mengembalikan daftar layanan.');

            return [
                'success' => $success,
                'message' => $message,
                'data' => $services,
                'raw' => $body,
            ];
        } catch (Throwable $e) {
            Log::error('OrderSosmed getServices error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Koneksi ke OrderSosmed gagal.',
                'data' => [],
            ];
        }
    }

    public function placeOrder(string $providerServiceId, string $target, int $quantity = 1): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Kredensial OrderSosmed belum diatur.',
                'order_status' => 'failed',
            ];
        }

        $payload = [
            'api_id' => $this->apiId,
            'api_key' => $this->apiKey,
            'service' => $providerServiceId,
            'target' => $target,
            'quantity' => max(1, $quantity),
        ];

        try {
            $response = Http::asForm()->timeout(30)->acceptJson()->post($this->apiUrl, $payload);
            $result = $response->json() ?? [];
            $providerOrderId = $result['data']['id'] ?? $result['order_id'] ?? $result['id'] ?? null;
            $accepted = $response->successful()
                && (
                    ($result['status'] ?? false) === true
                    || $providerOrderId !== null
                );

            $message = $result['data']['message']
                ?? $result['message']
                ?? ($accepted ? 'Pesanan berhasil dikirim ke provider.' : 'Provider menolak pesanan.');

            if ($accepted) {
                Log::info('OrderSosmed accepted order', [
                    'provider_order_id' => $providerOrderId,
                    'service' => $providerServiceId,
                ]);
            } else {
                Log::warning('OrderSosmed rejected order', [
                    'response' => $result,
                    'service' => $providerServiceId,
                ]);
            }

            return [
                'success' => $accepted,
                'message' => $message,
                'provider_order_id' => $providerOrderId,
                'order_status' => $accepted ? 'processing' : 'failed',
                'raw' => $result,
            ];
        } catch (Throwable $e) {
            Log::error('OrderSosmed placeOrder error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Koneksi ke OrderSosmed gagal.',
                'order_status' => 'failed',
            ];
        }
    }
}
