<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderSosmedService
{
    protected $apiUrl;
    protected $apiId;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = env('ORDERSOSMED_API_URL');
        $this->apiId = env('ORDERSOSMED_API_ID');
        $this->apiKey = env('ORDERSOSMED_API_KEY');
    }

    /**
     * Mengirim pesanan ke OrderSosmed
     */
    public function placeOrder($providerServiceId, $target, $quantity = 1000)
    {
        // Standar payload SMM Panel Indonesia pada umumnya
        $payload = [
            'api_id' => $this->apiId,
            'api_key' => $this->apiKey,
            'service' => $providerServiceId,
            'target' => $target,
            'quantity' => $quantity,
        ];

        try {
            // Mengirim request POST ke API OrderSosmed
            $response = Http::post($this->apiUrl, $payload);
            $result = $response->json();

            // Cek apakah response dari provider sukses
            if (isset($result['status']) && $result['status'] == true) {
                Log::info('OrderSosmed Sukses: ' . json_encode($result));
                return [
                    'success' => true,
                    'provider_order_id' => $result['data']['id'] ?? null // Menyimpan ID struk dari pusat
                ];
            } else {
                Log::error('OrderSosmed Gagal: ' . json_encode($result));
                return ['success' => false, 'message' => $result['data'] ?? 'Gagal dari provider'];
            }
        } catch (\Exception $e) {
            Log::error('OrderSosmed Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Koneksi ke API terputus.'];
        }
    }
}