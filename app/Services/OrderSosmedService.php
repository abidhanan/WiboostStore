<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderSosmedService
{
    protected string $apiUrl;
    protected string $apiId;
    protected string $apiKey;
    protected string $secretKey;

    public function __construct()
    {
        $this->apiUrl = $this->normalizeApiUrl(trim((string) config('services.ordersosmed.api_url')));
        $this->apiId = trim((string) config('services.ordersosmed.api_id'));
        $this->apiKey = trim((string) config('services.ordersosmed.api_key'));
        $this->secretKey = trim((string) config('services.ordersosmed.secret_key'));
    }

    public function isConfigured(): bool
    {
        if ($this->apiUrl === '' || $this->apiId === '' || $this->apiKey === '') {
            return false;
        }

        if ($this->usesRouteBasedApi() && $this->secretKey === '') {
            return false;
        }

        return true;
    }

    public function getServices(): array
    {
        if (! $this->hasBaseCredentials()) {
            return [
                'success' => false,
                'message' => 'Kredensial OrderSosmed belum diatur.',
                'data' => [],
            ];
        }

        if ($this->usesRouteBasedApi() && $this->secretKey === '') {
            return [
                'success' => false,
                'message' => 'Secret Key OrderSosmed belum diatur.',
                'data' => [],
            ];
        }

        try {
            $attempt = $this->attemptRequest([
                [],
                [
                    'action' => 'services',
                ],
            ], 'services');

            $response = $attempt['response'] ?? null;
            $url = $attempt['url'] ?? $this->apiUrl;
            $body = $attempt['body'] ?? [];
            $services = $attempt['services'] ?? [];
            $success = $response instanceof Response && $response->successful() && $services !== [];
            $message = $this->resolveMessage($body, $success ? 'Daftar layanan berhasil diambil.' : 'Provider tidak mengembalikan daftar layanan.');

            if (! $success) {
                Log::warning('OrderSosmed getServices unexpected response', [
                    'url' => $url,
                    'status' => $response?->status(),
                    'message' => $message,
                    'body' => $this->stringifyBody($body),
                ]);
            }

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
        if (! $this->hasBaseCredentials()) {
            return [
                'success' => false,
                'message' => 'Kredensial OrderSosmed belum diatur.',
                'order_status' => 'failed',
            ];
        }

        if ($this->usesRouteBasedApi() && $this->secretKey === '') {
            return [
                'success' => false,
                'message' => 'Secret Key OrderSosmed belum diatur.',
                'order_status' => 'failed',
            ];
        }

        $providerServiceId = $this->normalizeProviderServiceId($providerServiceId);

        try {
            $attempt = $this->attemptRequest([
                [
                    'action' => 'add',
                    'service' => $providerServiceId,
                    'link' => $target,
                    'quantity' => max(1, $quantity),
                ],
                [
                    'action' => 'add',
                    'service' => $providerServiceId,
                    'target' => $target,
                    'quantity' => max(1, $quantity),
                ],
                [
                    'service' => $providerServiceId,
                    'link' => $target,
                    'quantity' => max(1, $quantity),
                ],
                [
                    'service' => $providerServiceId,
                    'target' => $target,
                    'quantity' => max(1, $quantity),
                ],
            ], 'order', true);

            $response = $attempt['response'] ?? null;
            $result = $attempt['body'] ?? [];
            $providerOrderId = $this->resolveProviderOrderId($result);
            $accepted = $response instanceof Response
                && $response->successful()
                && (
                    ($result['status'] ?? false) === true
                    || ($result['success'] ?? false) === true
                    || ($result['response'] ?? false) === true
                    || $providerOrderId !== null
                );

            $message = $this->resolveMessage($result, $accepted ? 'Pesanan berhasil dikirim ke provider.' : 'Provider menolak pesanan.');

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

    protected function attemptRequest(array $payloadVariants, ?string $resource = null, bool $stopOnOrderAccepted = false): array
    {
        $lastResponse = null;
        $lastBody = [];
        $lastServices = [];

        foreach ($this->resolveApiUrls($resource) as $url) {
            foreach ($payloadVariants as $payloadVariant) {
                foreach ($this->buildAuthPayloads($payloadVariant) as $payload) {
                    $response = Http::asForm()
                        ->timeout(30)
                        ->acceptJson()
                        ->post($url, $payload);

                    $body = $this->decodeBody($response);
                    $services = $this->extractServices($body);
                    $orderId = $this->resolveProviderOrderId($body);
                    $accepted = $response->successful()
                        && (
                            $services !== []
                            || ($body['status'] ?? false) === true
                            || ($body['success'] ?? false) === true
                            || $orderId !== null
                        );

                    if ($accepted || (! $stopOnOrderAccepted && $services !== [])) {
                        return [
                            'response' => $response,
                            'body' => $body,
                            'services' => $services,
                            'url' => $url,
                        ];
                    }

                    $lastResponse = $response;
                    $lastBody = $body;
                    $lastServices = $services;
                }
            }
        }

        $urls = $this->resolveApiUrls($resource);

        return [
            'response' => $lastResponse,
            'body' => $lastBody,
            'services' => $lastServices,
            'url' => end($urls) ?: $this->apiUrl,
        ];
    }

    protected function resolveApiUrls(?string $resource = null): array
    {
        $urls = [$this->apiUrl];
        $baseUrl = $this->resolveBaseUrl();

        if ($resource !== null) {
            foreach ($this->resolveRouteBasedUrls($resource, $baseUrl) as $url) {
                $urls[] = $url;
            }
        }

        if ($baseUrl !== '') {
            $urls[] = rtrim($baseUrl, '/') . '/api';
            $urls[] = rtrim($baseUrl, '/') . '/api/v1';
            $urls[] = rtrim($baseUrl, '/') . '/api/v2';
        }

        return collect($urls)
            ->filter(fn (?string $url) => filled($url))
            ->unique()
            ->values()
            ->all();
    }

    protected function resolveRouteBasedUrls(string $resource, string $baseUrl): array
    {
        if ($baseUrl === '') {
            return [];
        }

        $path = match ($resource) {
            'profile' => 'api-1/profile',
            'services' => 'api-1/service',
            'order' => 'api-1/order',
            'status' => 'api-1/status',
            'refill' => 'api-1/refill',
            'refill_status' => 'api-1/status_refill',
            default => null,
        };

        return $path ? [rtrim($baseUrl, '/') . '/' . $path] : [];
    }

    protected function buildAuthPayloads(array $payload): array
    {
        $variants = [];

        if ($this->apiId !== '' && $this->secretKey !== '') {
            $variants[] = [
                'api_id' => $this->apiId,
                'api_key' => $this->apiKey,
                'secret_key' => $this->secretKey,
                ...$payload,
            ];
        }

        if ($this->apiId !== '') {
            $variants[] = [
                'api_id' => $this->apiId,
                'api_key' => $this->apiKey,
                ...$payload,
            ];
        }

        if ($this->secretKey !== '') {
            $variants[] = [
                'api_key' => $this->apiKey,
                'secret_key' => $this->secretKey,
                ...$payload,
            ];
        }

        $variants[] = [
            'api_key' => $this->apiKey,
            ...$payload,
        ];

        $variants[] = [
            'key' => $this->apiKey,
            ...$payload,
        ];

        return collect($variants)
            ->unique(fn (array $item) => md5(json_encode($item)))
            ->values()
            ->all();
    }

    protected function decodeBody(Response $response): array
    {
        $decoded = $response->json();

        if (is_array($decoded)) {
            return $decoded;
        }

        return [
            'body' => trim((string) $response->body()),
        ];
    }

    protected function extractServices(array $body): array
    {
        $candidates = [
            $body,
            $body['data'] ?? null,
            $body['services'] ?? null,
            $body['result'] ?? null,
            $body['results'] ?? null,
            $body['data']['services'] ?? null,
            $body['result']['services'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_array($candidate) && array_is_list($candidate) && $candidate !== []) {
                return $candidate;
            }
        }

        return [];
    }

    protected function resolveProviderOrderId(array $body): ?string
    {
        $orderId = $body['data']['id']
            ?? $body['data']['order_id']
            ?? $body['data']['order']
            ?? $body['order_id']
            ?? $body['order']
            ?? $body['id']
            ?? null;

        return filled($orderId) ? (string) $orderId : null;
    }

    protected function resolveMessage(array $body, string $fallback): string
    {
        $message = $body['data']['message']
            ?? $body['data']['msg']
            ?? $body['message']
            ?? $body['error']
            ?? $body['errors'][0] ?? null;

        return filled($message) ? (string) $message : $fallback;
    }

    protected function stringifyBody(array $body): string
    {
        $string = json_encode($body, JSON_UNESCAPED_UNICODE);

        return is_string($string) ? substr($string, 0, 1000) : '';
    }

    protected function hasBaseCredentials(): bool
    {
        return $this->apiUrl !== '' && $this->apiId !== '' && $this->apiKey !== '';
    }

    protected function usesRouteBasedApi(): bool
    {
        return str_contains($this->apiUrl, '/api-1');
    }

    protected function resolveBaseUrl(): string
    {
        return rtrim((string) (preg_replace('#/(api(?:/v\d+)?|api-1)$#i', '', $this->apiUrl) ?? $this->apiUrl), '/');
    }

    protected function normalizeProviderServiceId(string $providerServiceId): string
    {
        if (str_contains($providerServiceId, ':')) {
            $parts = explode(':', $providerServiceId);

            return (string) end($parts);
        }

        return $providerServiceId;
    }

    protected function normalizeApiUrl(string $apiUrl): string
    {
        if ($apiUrl === '') {
            return '';
        }

        $normalized = rtrim($apiUrl, '/');
        $host = parse_url($normalized, PHP_URL_HOST);

        if ($host === 'ordersosmed.com') {
            $normalized = preg_replace(
                '#^https?://ordersosmed\.com#i',
                'https://ordersosmed.id',
                $normalized
            ) ?? $normalized;
        }

        return $normalized;
    }
}
