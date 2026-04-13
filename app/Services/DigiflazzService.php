<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DigiflazzService
{
    protected string $username;
    protected string $key;
    protected string $baseUrl;

    public function __construct()
    {
        $this->username = trim((string) config('services.digiflazz.username'));
        $this->key = trim((string) config('services.digiflazz.key'));
        $this->baseUrl = rtrim((string) config('services.digiflazz.base_url', 'https://api.digiflazz.com/v1'), '/');
    }

    public function isConfigured(): bool
    {
        return $this->username !== '' && $this->key !== '';
    }

    public function getBalance(): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'Kredensial Digiflazz belum diatur.'];
        }

        $sign = md5($this->username . $this->key . 'depo');

        try {
            $response = Http::timeout(30)->acceptJson()->post($this->baseUrl . '/cek-saldo', [
                'username' => $this->username,
                'sign' => $sign,
            ]);

            $body = $response->json() ?? [];

            return [
                'success' => $response->successful(),
                'message' => $body['data']['message'] ?? $body['message'] ?? null,
                'raw' => $body,
            ];
        } catch (Throwable $e) {
            Log::error('Digiflazz getBalance error: ' . $e->getMessage());

            return ['success' => false, 'message' => 'Koneksi ke Digiflazz gagal.'];
        }
    }

    public function getPriceList(): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'Kredensial Digiflazz belum diatur.'];
        }

        $sign = md5($this->username . $this->key . 'pricelist');

        try {
            $response = Http::timeout(30)->acceptJson()->post($this->baseUrl . '/price-list', [
                'username' => $this->username,
                'sign' => $sign,
                'cmd' => 'prepaid',
            ]);

            $body = $response->json() ?? [];
            $data = $body['data'] ?? [];
            $success = $response->successful()
                && (
                    (is_array($data) && array_is_list($data))
                    || in_array((string) ($data['rc'] ?? $body['rc'] ?? ''), ['00', '0'], true)
                );

            return [
                'success' => $success,
                'message' => $data['message'] ?? $body['message'] ?? null,
                'raw' => $body,
            ];
        } catch (Throwable $e) {
            Log::error('Digiflazz getPriceList error: ' . $e->getMessage());

            return ['success' => false, 'message' => 'Koneksi ke Digiflazz gagal.'];
        }
    }

    public function placeOrder(string $sku, string $target, string $refId): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Kredensial Digiflazz belum diatur.',
                'order_status' => 'failed',
            ];
        }

        $sign = md5($this->username . $this->key . $refId);

        try {
            $response = Http::timeout(30)->acceptJson()->post($this->baseUrl . '/transaction', [
                'username' => $this->username,
                'buyer_sku_code' => $sku,
                'customer_no' => $target,
                'ref_id' => $refId,
                'sign' => $sign,
            ]);

            $body = $response->json() ?? [];
            $data = $body['data'] ?? [];
            $statusText = strtolower((string) ($data['status'] ?? $body['status'] ?? ''));
            $responseCode = (string) ($data['rc'] ?? $body['rc'] ?? '');
            $providerOrderId = $data['ref_id'] ?? $data['sn'] ?? $refId;
            $message = $data['message'] ?? $body['message'] ?? 'Digiflazz merespons tanpa pesan.';

            $success = $response->successful()
                && in_array($responseCode, ['00', '0', ''], true)
                && ! in_array($statusText, ['gagal', 'failed', 'error'], true);

            $orderStatus = match (true) {
                str_contains($statusText, 'sukses'), str_contains($statusText, 'success') => 'success',
                str_contains($statusText, 'pending'), $success => 'processing',
                default => 'failed',
            };

            return [
                'success' => $success,
                'message' => $message,
                'provider_order_id' => $providerOrderId,
                'order_status' => $orderStatus,
                'raw' => $body,
            ];
        } catch (Throwable $e) {
            Log::error("Digiflazz placeOrder error [{$refId}]: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Koneksi ke Digiflazz gagal.',
                'order_status' => 'failed',
            ];
        }
    }
}
