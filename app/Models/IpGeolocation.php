<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpGeolocation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'lat' => 'float',
        'lon' => 'float',
        'offset' => 'integer',
        'mobile' => 'boolean',
        'proxy' => 'boolean',
        'hosting' => 'boolean',
        'raw' => 'array',
    ];

    /**
     * Susun data persis format ip-api.com (untuk ditampilkan di halaman log).
     */
    public function toIpApiArray(): array
    {
        if (is_array($this->raw) && $this->raw !== []) {
            return $this->raw;
        }

        return [
            'query' => $this->ip,
            'status' => $this->status ?: 'success',
            'continent' => $this->continent,
            'continentCode' => $this->continent_code,
            'country' => $this->country,
            'countryCode' => $this->country_code,
            'region' => $this->region,
            'regionName' => $this->region_name,
            'city' => $this->city,
            'district' => $this->district,
            'zip' => $this->zip,
            'lat' => $this->lat,
            'lon' => $this->lon,
            'timezone' => $this->timezone,
            'offset' => $this->offset,
            'currency' => $this->currency,
            'isp' => $this->isp,
            'org' => $this->org,
            'as' => $this->as_number,
            'asname' => $this->asname,
            'mobile' => (bool) $this->mobile,
            'proxy' => (bool) $this->proxy,
            'hosting' => (bool) $this->hosting,
        ];
    }
}
