<?php

namespace App\Services;

use App\Models\IpGeolocation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IpGeolocationService
{
    /**
     * Ambil geolokasi untuk sebuah IP. Hasil disimpan di DB (cache) supaya
     * ip-api.com hanya dipanggil untuk IP baru / data lama (>30 hari).
     */
    public function resolve(?string $ip): ?IpGeolocation
    {
        $ip = trim((string) $ip);

        if ($ip === '' || $this->isPrivateOrReserved($ip)) {
            return null;
        }

        $record = IpGeolocation::query()->where('ip', $ip)->first();

        if ($record && $record->updated_at && $record->updated_at->greaterThan(now()->subDays(30))) {
            return $record;
        }

        // Cegah lookup ganda/berulang untuk IP yang sama dalam waktu berdekatan (hemat kuota ip-api 45/menit).
        if (! Cache::add('ipgeo:' . $ip, 1, now()->addMinutes(3))) {
            return $record;
        }

        $data = $this->fetch($ip);

        if (! is_array($data) || ($data['status'] ?? null) !== 'success') {
            return $record;
        }

        return IpGeolocation::updateOrCreate(['ip' => $ip], $this->mapFields($data));
    }

    protected function fetch(string $ip): ?array
    {
        try {
            // Ambil SEMUA field yang tersedia di ip-api (endpoint gratis), termasuk reverse DNS.
            $fields = 'status,message,continent,continentCode,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,offset,currency,isp,org,as,asname,reverse,mobile,proxy,hosting,query';

            $response = Http::connectTimeout(2)->timeout(3)->retry(1, 200)
                ->get('http://ip-api.com/json/' . urlencode($ip), ['fields' => $fields]);

            return $response->ok() ? $response->json() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function mapFields(array $d): array
    {
        return [
            'status' => $d['status'] ?? null,
            'continent' => $d['continent'] ?? null,
            'continent_code' => $d['continentCode'] ?? null,
            'country' => $d['country'] ?? null,
            'country_code' => $d['countryCode'] ?? null,
            'region' => $d['region'] ?? null,
            'region_name' => $d['regionName'] ?? null,
            'city' => $d['city'] ?? null,
            'district' => $d['district'] ?? null,
            'zip' => $d['zip'] ?? null,
            'lat' => $d['lat'] ?? null,
            'lon' => $d['lon'] ?? null,
            'timezone' => $d['timezone'] ?? null,
            'offset' => $d['offset'] ?? null,
            'currency' => $d['currency'] ?? null,
            'isp' => $d['isp'] ?? null,
            'org' => $d['org'] ?? null,
            'as_number' => $d['as'] ?? null,
            'asname' => $d['asname'] ?? null,
            'mobile' => (bool) ($d['mobile'] ?? false),
            'proxy' => (bool) ($d['proxy'] ?? false),
            'hosting' => (bool) ($d['hosting'] ?? false),
            'raw' => $d,
        ];
    }

    protected function isPrivateOrReserved(string $ip): bool
    {
        // filter_var mengembalikan IP jika PUBLIC; false jika privat/reserved/invalid.
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
