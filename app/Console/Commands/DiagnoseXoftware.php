<?php

namespace App\Console\Commands;

use App\Services\XoftwarePaymentService;
use Illuminate\Console\Command;
use Throwable;

class DiagnoseXoftware extends Command
{
    protected $signature = 'xoftware:diagnose';

    protected $description = 'Periksa konfigurasi, channel aktif, dan saldo Xoftware tanpa membuat transaksi.';

    public function handle(XoftwarePaymentService $xoftware): int
    {
        $this->line('Konfigurasi Xoftware');
        $this->table(['Item', 'Value'], [
            ['base_url', rtrim((string) config('xoftware.base_url'), '/') ?: '-'],
            ['merchant_id', filled(config('xoftware.merchant_id')) ? (string) config('xoftware.merchant_id') : '-'],
            ['api_key_filled', filled(config('xoftware.api_key')) ? 'yes' : 'no'],
            ['channel_config', (string) config('xoftware.default_channel', 'QRIS')],
            ['fee_direction', (string) config('xoftware.fee_direction', 'merchant')],
            ['callback_url', $this->publicUrl('xoftware/callback')],
        ]);

        $channelResult = $this->inspectChannels($xoftware);
        $balanceResult = $this->inspectBalance($xoftware);

        if (! $channelResult['reachable'] || ! $balanceResult['reachable']) {
            $this->error('API Xoftware belum dapat diperiksa dengan lengkap dari server ini.');
            $this->warn('Pastikan IP hosting masuk whitelist Xoftware, lalu jalankan command ini lagi.');

            return self::FAILURE;
        }

        if (! $channelResult['qris_active']) {
            $this->error('Channel QRIS tidak ditemukan pada daftar channel aktif merchant.');
            $this->warn('Aktifkan QRIS atau selesaikan Pengajuan Integrasi di dashboard Xoftware.');

            return self::FAILURE;
        }

        $this->info('Channel QRIS terdeteksi aktif dan API merchant dapat diakses.');
        $this->warn('Jika create QRIS tetap menghasilkan ERR:54344, masalah terjadi saat Xoftware meneruskan transaksi ke provider QRIS.');
        $this->line('Buat tiket Xoftware dengan Merchant ID, waktu kejadian, invoice/ref_id, dan kode ERR:54344.');

        return self::SUCCESS;
    }

    protected function inspectChannels(XoftwarePaymentService $xoftware): array
    {
        try {
            $response = $xoftware->paymentChannels();
            $channels = $this->extractRows($response, ['channels', 'data.channels']);
            $rows = [];
            $qrisActive = false;

            foreach ($channels as $channel) {
                if (! is_array($channel)) {
                    continue;
                }

                $code = strtoupper(trim((string) (
                    $channel['code']
                    ?? $channel['channel_code']
                    ?? $channel['payment_code']
                    ?? ''
                )));
                $name = (string) ($channel['name'] ?? $channel['channel_name'] ?? '-');
                $status = $this->channelStatus($channel);

                if ($code === '') {
                    continue;
                }

                $qrisActive = $qrisActive || ($code === 'QRIS' && $status !== 'inactive');
                $rows[] = [$code, $name, $status];
            }

            $this->line('Channel pembayaran aktif');
            $rows === []
                ? $this->warn('Respons channel diterima, tetapi format daftar channel belum dikenali.')
                : $this->table(['Code', 'Name', 'Status'], $rows);

            return [
                'reachable' => true,
                'qris_active' => $qrisActive,
            ];
        } catch (Throwable $exception) {
            $this->error('Gagal membaca channel: ' . $exception->getMessage());

            return [
                'reachable' => false,
                'qris_active' => false,
            ];
        }
    }

    protected function inspectBalance(XoftwarePaymentService $xoftware): array
    {
        try {
            $response = $xoftware->balance();
            $data = data_get($response, 'data');

            if (! is_array($data)) {
                $data = $response;
            }

            $this->line('Saldo merchant');
            $this->table(['Item', 'Value'], [
                ['merchant_id', (string) ($data['merchant_id'] ?? config('xoftware.merchant_id') ?? '-')],
                ['available', $this->money($data['available'] ?? $data['balance'] ?? null)],
                ['unsettle', $this->money($data['unsettle'] ?? $data['unsettled'] ?? null)],
                ['pending_withdraw', $this->money($data['pending_withdraw'] ?? null)],
            ]);

            return ['reachable' => true];
        } catch (Throwable $exception) {
            $this->error('Gagal membaca saldo: ' . $exception->getMessage());

            return ['reachable' => false];
        }
    }

    protected function extractRows(array $response, array $keys): array
    {
        foreach ($keys as $key) {
            $value = data_get($response, $key);

            if (is_array($value) && array_is_list($value)) {
                return $value;
            }
        }

        if (array_is_list($response)) {
            return $response;
        }

        $data = data_get($response, 'data');

        return is_array($data) && array_is_list($data) ? $data : [];
    }

    protected function channelStatus(array $channel): string
    {
        $value = $channel['status'] ?? $channel['active'] ?? $channel['is_active'] ?? null;

        if (is_bool($value)) {
            return $value ? 'active' : 'inactive';
        }

        if ($value === null || $value === '') {
            return 'active';
        }

        $status = strtolower(trim((string) $value));

        return in_array($status, ['0', 'false', 'inactive', 'disabled', 'off'], true)
            ? 'inactive'
            : $status;
    }

    protected function money(mixed $value): string
    {
        if (! is_numeric($value)) {
            return '-';
        }

        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    protected function publicUrl(string $path): string
    {
        $baseUrl = rtrim((string) (config('wiboost.public_url') ?: config('app.url')), '/');

        return $baseUrl . '/' . ltrim($path, '/');
    }
}
