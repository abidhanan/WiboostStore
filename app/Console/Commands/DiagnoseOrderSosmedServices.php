<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DiagnoseOrderSosmedServices extends Command
{
    protected $signature = 'ordersosmed:diagnose-services';

    protected $description = 'Diagnosa aman endpoint daftar layanan OrderSosmed tanpa menampilkan credential.';

    public function handle(): int
    {
        $apiUrl = $this->normalizeApiUrl(trim((string) config('services.ordersosmed.api_url')));
        $apiId = trim((string) config('services.ordersosmed.api_id'));
        $apiKey = trim((string) config('services.ordersosmed.api_key'));
        $secretKey = trim((string) config('services.ordersosmed.secret_key'));

        $this->line('Config OrderSosmed');
        $this->table(['Item', 'Value'], [
            ['api_url', $apiUrl ?: '-'],
            ['api_id_filled', filled($apiId) ? 'yes' : 'no'],
            ['api_key_filled', filled($apiKey) ? 'yes' : 'no'],
            ['secret_key_filled', filled($secretKey) ? 'yes' : 'no'],
        ]);

        if ($apiUrl === '' || $apiId === '' || $apiKey === '') {
            $this->error('ORDERSOSMED_API_URL, ORDERSOSMED_API_ID, dan ORDERSOSMED_API_KEY wajib terisi.');

            return self::FAILURE;
        }

        $payloadVariants = $this->payloadVariants($apiId, $apiKey, $secretKey);
        $rows = [];
        $found = false;

        foreach ($this->endpointCandidates($apiUrl) as $url) {
            foreach ($payloadVariants as $label => $payload) {
                try {
                    $response = Http::asForm()
                        ->timeout(20)
                        ->acceptJson()
                        ->post($url, $payload);

                    $summary = $this->summarizeResponse($response);
                    $found = $found || $summary['service_count'] > 0;

                    $rows[] = [
                        'url' => $url,
                        'payload' => $label,
                        'status' => (string) $response->status(),
                        'keys' => $summary['keys'],
                        'message' => $summary['message'],
                        'count' => (string) $summary['service_count'],
                        'first_keys' => $summary['first_keys'],
                    ];
                } catch (\Throwable $e) {
                    $rows[] = [
                        'url' => $url,
                        'payload' => $label,
                        'status' => 'ERR',
                        'keys' => '-',
                        'message' => Str::limit($e->getMessage(), 120),
                        'count' => '0',
                        'first_keys' => '-',
                    ];
                }
            }
        }

        $this->table(['URL', 'Payload', 'HTTP', 'Keys', 'Message', 'Count', 'First row keys'], $rows);

        if (! $found) {
            $this->warn('Belum ada endpoint yang mengembalikan daftar layanan.');
            $this->warn('Kirim output tabel ini ke chat supaya bisa kita cocokkan dengan format respons provider.');

            return self::FAILURE;
        }

        $this->info('Ada endpoint yang mengembalikan daftar layanan. Kita bisa pakai pola itu untuk sync.');

        return self::SUCCESS;
    }

    protected function payloadVariants(string $apiId, string $apiKey, string $secretKey): array
    {
        $basePayloads = [
            'empty' => [],
            'action_services' => ['action' => 'services'],
            'action_service' => ['action' => 'service'],
            'action_layanan' => ['action' => 'layanan'],
        ];

        $authPayloads = [];

        foreach ($basePayloads as $baseLabel => $basePayload) {
            if ($secretKey !== '') {
                $authPayloads['api_id+api_key+secret_key/' . $baseLabel] = [
                    'api_id' => $apiId,
                    'api_key' => $apiKey,
                    'secret_key' => $secretKey,
                    ...$basePayload,
                ];
                $authPayloads['api_id+api_key+secret/' . $baseLabel] = [
                    'api_id' => $apiId,
                    'api_key' => $apiKey,
                    'secret' => $secretKey,
                    ...$basePayload,
                ];
            }

            $authPayloads['api_id+api_key/' . $baseLabel] = [
                'api_id' => $apiId,
                'api_key' => $apiKey,
                ...$basePayload,
            ];
        }

        return $authPayloads;
    }

    protected function endpointCandidates(string $apiUrl): array
    {
        $baseUrl = rtrim((string) (preg_replace('#/(api(?:/v\d+)?|api-1)$#i', '', $apiUrl) ?? $apiUrl), '/');

        return collect([
            $apiUrl,
            $baseUrl . '/api-1/service',
            $baseUrl . '/api-1/services',
            $baseUrl . '/api-1/layanan',
            $baseUrl . '/api',
            $baseUrl . '/api/v1',
            $baseUrl . '/api/v2',
        ])->filter()->unique()->values()->all();
    }

    protected function summarizeResponse(Response $response): array
    {
        $body = $response->json();

        if (! is_array($body)) {
            $text = trim((string) $response->body());

            return [
                'keys' => 'body',
                'message' => Str::limit($text, 120),
                'service_count' => 0,
                'first_keys' => '-',
            ];
        }

        $services = $this->extractServices($body);
        $first = $services[0] ?? null;
        $message = $body['data']['message']
            ?? $body['data']['msg']
            ?? $body['message']
            ?? $body['msg']
            ?? $body['error']
            ?? '-';

        return [
            'keys' => implode(',', array_slice(array_keys($body), 0, 8)),
            'message' => Str::limit((string) $message, 120),
            'service_count' => count($services),
            'first_keys' => is_array($first) ? implode(',', array_slice(array_keys($first), 0, 10)) : '-',
        ];
    }

    protected function extractServices(array $body): array
    {
        $candidates = [
            $body,
            $body['data'] ?? null,
            $body['services'] ?? null,
            $body['service'] ?? null,
            $body['layanan'] ?? null,
            $body['result'] ?? null,
            $body['results'] ?? null,
            $body['data']['services'] ?? null,
            $body['data']['service'] ?? null,
            $body['data']['layanan'] ?? null,
            $body['result']['services'] ?? null,
            $body['result']['service'] ?? null,
            $body['result']['layanan'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_array($candidate) && array_is_list($candidate) && $candidate !== []) {
                return $candidate;
            }

            if (is_array($candidate) && $this->looksLikeServiceMap($candidate)) {
                return array_values($candidate);
            }
        }

        return [];
    }

    protected function looksLikeServiceMap(array $candidate): bool
    {
        if ($candidate === [] || array_is_list($candidate)) {
            return false;
        }

        $sample = array_slice(array_values($candidate), 0, 10);

        return collect($sample)->contains(function ($row) {
            return is_array($row)
                && (
                    filled($row['id'] ?? null)
                    || filled($row['service'] ?? null)
                    || filled($row['service_id'] ?? null)
                    || filled($row['name'] ?? null)
                    || filled($row['service_name'] ?? null)
                );
        });
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
