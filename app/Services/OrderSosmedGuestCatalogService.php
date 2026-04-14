<?php

namespace App\Services;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use Throwable;

class OrderSosmedGuestCatalogService
{
    protected string $baseUrl;

    public function __construct()
    {
        $configuredUrl = trim((string) config('services.ordersosmed.api_url'));
        $normalizedUrl = preg_replace('#^https?://ordersosmed\.com#i', 'https://ordersosmed.id', $configuredUrl) ?? $configuredUrl;
        $this->baseUrl = rtrim((string) preg_replace('#/(api(?:/v\d+)?|api-1)$#i', '', $normalizedUrl), '/');
    }

    public function getServices(array $types = ['sosmed', 'games', 'prepaid'], int $row = 1000): array
    {
        if ($this->baseUrl === '') {
            return [
                'success' => false,
                'message' => 'Host OrderSosmed belum diatur.',
                'data' => [],
            ];
        }

        try {
            $services = [];

            foreach ($types as $type) {
                $services = array_merge($services, $this->fetchTypeServices($type, $row));
            }

            return [
                'success' => $services !== [],
                'message' => $services !== []
                    ? 'Layanan berhasil diambil dari katalog publik OrderSosmed.'
                    : 'Katalog publik OrderSosmed tidak mengembalikan data layanan.',
                'data' => $services,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Katalog publik OrderSosmed gagal diambil.',
                'data' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function fetchTypeServices(string $type, int $row): array
    {
        $cookieJar = new CookieJar();
        $client = Http::withOptions(['cookies' => $cookieJar])
            ->accept('text/html,application/xhtml+xml')
            ->timeout(30);

        $guestUrl = $this->baseUrl . '/page/services_guest?type=' . $type;
        $pageResponse = $client->get($guestUrl);
        $html = (string) $pageResponse->body();

        if (! $pageResponse->successful() || $html === '') {
            return [];
        }

        $csrfToken = $this->extractCsrfToken($html);
        $categories = $this->extractCategories($html);

        if ($csrfToken === '' || $categories === []) {
            return [];
        }

        $services = [];
        $ajaxClient = Http::withOptions(['cookies' => $cookieJar])
            ->acceptJson()
            ->asForm()
            ->timeout(30)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Referer' => $guestUrl,
            ]);

        foreach ($categories as $categoryId => $categoryName) {
            $page = 1;
            $totalPages = 1;

            do {
                $response = $ajaxClient->post($this->baseUrl . '/ajax/services?type=' . $type, [
                    'category' => $categoryId,
                    'page' => $page,
                    'row' => $row,
                    'search' => '',
                    'csrf_token' => $csrfToken,
                ]);

                $payload = $response->json() ?? [];
                $tbody = (string) ($payload['tbody'] ?? '');

                if (! $response->successful() || $tbody === '') {
                    break;
                }

                $services = array_merge(
                    $services,
                    $this->parseRows($tbody, $type, (string) $categoryName)
                );

                $totalPages = $this->resolveTotalPages((string) ($payload['tinfo'] ?? ''), $row);
                $page++;
            } while ($page <= $totalPages);
        }

        return $services;
    }

    protected function extractCsrfToken(string $html): string
    {
        if (preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/i', $html, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/csrf_token=([a-f0-9]+)/i', $html, $matches) === 1) {
            return $matches[1];
        }

        return '';
    }

    protected function extractCategories(string $html): array
    {
        if (preg_match('/<select[^>]*id="table-category"[^>]*>(.*?)<\/select>/is', $html, $matches) !== 1) {
            return [];
        }

        $optionsHtml = $matches[1];

        if (preg_match_all('/<option\s+value="([^"]+)"[^>]*>(.*?)<\/option>/is', $optionsHtml, $matches, PREG_SET_ORDER) !== false) {
            $categories = [];

            foreach ($matches as $match) {
                $value = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $label = trim(html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                if ($value !== '' && $label !== '') {
                    $categories[$value] = $label;
                }
            }

            return $categories;
        }

        return [];
    }

    protected function parseRows(string $tbody, string $type, string $categoryName): array
    {
        $dom = new \DOMDocument();
        $html = '<table><tbody>' . $tbody . '</tbody></table>';

        libxml_use_internal_errors(true);
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $services = [];

        foreach ($xpath->query('//tr') as $row) {
            $cells = [];

            foreach ($xpath->query('./td', $row) as $cell) {
                $cells[] = $this->cleanText($cell->textContent);
            }

            $service = $this->mapRowToService($cells, $type, $categoryName);

            if ($service !== null) {
                $services[] = $service;
            }
        }

        return $services;
    }

    protected function mapRowToService(array $cells, string $type, string $categoryName): ?array
    {
        if ($type === 'sosmed') {
            if (count($cells) < 6) {
                return null;
            }

            $hasFavoriteColumn = count($cells) >= 7;
            $id = $cells[0] ?? '';
            $name = $cells[$hasFavoriteColumn ? 2 : 1] ?? '';
            $min = $this->parseNumber($cells[$hasFavoriteColumn ? 3 : 2] ?? '');
            $max = $this->parseNumber($cells[$hasFavoriteColumn ? 4 : 3] ?? '');
            $price = $this->parseCurrency($cells[$hasFavoriteColumn ? 5 : 4] ?? '');

            if ($id === '' || $name === '' || $price <= 0) {
                return null;
            }

            return [
                'id' => $id,
                'name' => $name,
                'category' => $categoryName,
                'rate' => $price,
                'min' => max(1, $min),
                'max' => max($max, max(1, $min)),
                'description' => 'Kategori OrderSosmed: ' . $categoryName,
                '_ordersosmed_catalog_type' => $type,
                '_ordersosmed_pricing_mode' => 'per_1000',
            ];
        }

        if (count($cells) < 4) {
            return null;
        }

        $id = $cells[0] ?? '';
        $name = $cells[1] ?? '';
        $priceCandidates = array_values(array_filter([
            $this->parseCurrency($cells[2] ?? ''),
            $this->parseCurrency($cells[3] ?? ''),
        ]));
        $price = $priceCandidates[0] ?? 0;

        if ($id === '' || $name === '' || $price <= 0) {
            return null;
        }

        return [
            'id' => $id,
            'name' => $name,
            'category' => $categoryName,
            'price' => $price,
            'min' => 1,
            'max' => 1,
            'description' => 'Kategori OrderSosmed: ' . $categoryName,
            '_ordersosmed_catalog_type' => $type,
            '_ordersosmed_pricing_mode' => 'flat',
        ];
    }

    protected function resolveTotalPages(string $info, int $row): int
    {
        if (preg_match('/dari\s+([\d\.]+)\s+data/i', $info, $matches) !== 1) {
            return 1;
        }

        $total = $this->parseNumber($matches[1]);

        if ($total <= 0 || $row <= 0) {
            return 1;
        }

        return max(1, (int) ceil($total / $row));
    }

    protected function cleanText(string $value): string
    {
        $decoded = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = preg_replace('/\s+/u', ' ', $decoded) ?? $decoded;

        return trim($decoded);
    }

    protected function parseCurrency(string $value): float
    {
        $normalized = preg_replace('/[^\d,\.]/', '', $value) ?? '';
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return is_numeric($normalized) ? (float) $normalized : 0;
    }

    protected function parseNumber(string $value): int
    {
        $normalized = preg_replace('/[^\d]/', '', $value) ?? '';

        return $normalized !== '' ? (int) $normalized : 0;
    }
}
