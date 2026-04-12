<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DigiflazzService
{
    protected $username;
    protected $key;
    protected $baseUrl;

    public function __construct()
    {
        // Menggunakan config() lebih stabil daripada env() langsung
        $this->username = trim(config('services.digiflazz.username'));
        $this->key      = trim(config('services.digiflazz.key'));
        $this->baseUrl  = 'https://api.digiflazz.com/v1';
    }

    /**
     * Cek Saldo Digiflazz
     */
    public function getBalance()
    {
        $sign = md5($this->username . $this->key . 'depo');
        
        try {
            $response = Http::post($this->baseUrl . '/cek-saldo', [
                'username' => $this->username,
                'sign'     => $sign
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Digiflazz GetBalance Error: ' . $e->getMessage());
            return ['data' => ['rc' => '99', 'message' => 'Koneksi API Gagal']];
        }
    }

    /**
     * Tarik Daftar Harga (Semua Produk Prepaid)
     */
    public function getPriceList()
    {
        $sign = md5($this->username . $this->key . 'pricelist');
        
        try {
            $response = Http::post($this->baseUrl . '/price-list', [
                'username' => $this->username,
                'sign'     => $sign,
                'cmd'      => 'prepaid' // Menarik semua list produk prabayar
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Digiflazz GetPriceList Error: ' . $e->getMessage());
            return ['data' => ['rc' => '99', 'message' => 'Koneksi API Gagal']];
        }
    }

    /**
     * Melakukan Pemesanan / Transaksi
     */
    public function placeOrder($sku, $target, $refId)
    {
        $sign = md5($this->username . $this->key . $refId);
        
        try {
            $response = Http::post($this->baseUrl . '/transaction', [
                'username'       => $this->username,
                'buyer_sku_code' => $sku,
                'customer_no'    => $target,
                'ref_id'         => $refId,
                'sign'           => $sign
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error("Digiflazz PlaceOrder Error [$refId]: " . $e->getMessage());
            return ['data' => ['rc' => '99', 'status' => 'Gagal', 'message' => 'Koneksi API Gagal']];
        }
    }
}