<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DigiflazzService
{
    protected $username;
    protected $key;
    protected $baseUrl;

    public function __construct()
    {
        // Pakai trim biar spasi ga sengaja di .env hilang
        $this->username = trim(env('DIGIFLAZZ_USERNAME'));
        $this->key      = trim(env('DIGIFLAZZ_KEY'));
        $this->baseUrl  = 'https://api.digiflazz.com/v1';
    }

    public function getBalance()
    {
        // Signature untuk cek saldo: md5(username + apikey + "depo")
        $sign = md5($this->username . $this->key . 'depo');
        
        $response = Http::post($this->baseUrl . '/cek-saldo', [
            'username' => $this->username,
            'sign'     => $sign
        ]);

        return $response->json();
    }

    public function getPriceList()
    {
        // Signature untuk pricelist: md5(username + apikey + "pricelist")
        $sign = md5($this->username . $this->key . 'pricelist');
        
        $response = Http::post($this->baseUrl . '/price-list', [
            'username' => $this->username,
            'sign'     => $sign
        ]);

        return $response->json();
    }

    public function placeOrder($sku, $target, $refId)
    {
        // Signature untuk transaksi: md5(username + apikey + ref_id)
        $sign = md5($this->username . $this->key . $refId);
        
        $response = Http::post($this->baseUrl . '/transaction', [
            'username'       => $this->username,
            'buyer_sku_code' => $sku,
            'customer_no'    => $target,
            'ref_id'         => $refId,
            'sign'           => $sign
        ]);

        return $response->json();
    }
}