<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WiboostReadinessCheck extends Command
{
    protected $signature = 'wiboost:readiness-check {--strict : Return exit code 1 jika masih ada catatan penting}';

    protected $description = 'Cek kesiapan production Wiboost: domain, callback, email, provider, scheduler, queue, dan secret';

    public function handle(): int
    {
        $checks = collect([
            $this->check('Domain publik final', $this->hasFinalPublicUrl(), 'Isi APP_URL dan WIBOOST_PUBLIC_URL dengan domain/hosting final, bukan localhost/ngrok gratis.'),
            $this->check('Mode production', config('app.env') === 'production' && config('app.debug') === false, 'Set APP_ENV=production dan APP_DEBUG=false saat live.'),
            $this->check('Midtrans live key', filled(config('midtrans.server_key')) && filled(config('midtrans.client_key')), 'Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY. Gunakan live key jika sudah menerima transaksi asli.'),
            $this->check('Email SMTP', config('mail.default') !== 'log' && filled(config('mail.from.address')) && filled(config('mail.mailers.smtp.host')), 'Gunakan SMTP aktif agar reset password dan email sukses transaksi terkirim.'),
            $this->check('Digiflazz API', filled(config('services.digiflazz.username')) && filled(config('services.digiflazz.key')), 'Isi DIGIFLAZZ_USERNAME dan DIGIFLAZZ_KEY.'),
            $this->check('OrderSosmed API', filled(config('services.ordersosmed.api_url')) && filled(config('services.ordersosmed.api_id')) && filled(config('services.ordersosmed.api_key')), 'Isi ORDERSOSMED_API_URL, ORDERSOSMED_API_ID, ORDERSOSMED_API_KEY, dan secret key jika provider mewajibkan.'),
            $this->check('Discord alert', filled(config('services.discord.webhook_url')), 'Isi DISCORD_WEBHOOK_URL agar error/refund/stok bisa masuk Discord.'),
            $this->check('Queue configured', config('queue.default') !== 'sync', 'Untuk production gunakan QUEUE_CONNECTION=database/redis dan jalankan php artisan queue:work.'),
            $this->check('Scheduler command', true, 'Pasang cron server: * * * * * php /path/project/artisan schedule:run >> /dev/null 2>&1'),
            $this->check('Secret rotation', true, 'Regenerate semua secret yang pernah dibagikan sebelum live: Midtrans, Digiflazz, OrderSosmed, Gmail app password, Discord webhook.'),
            $this->check('Testing transaksi real', true, 'Uji deposit, top up game kecil, kuota kecil, suntik sosmed kecil, reset password, dan refund provider gagal.'),
        ]);

        $checks->each(function (array $check) {
            $line = ($check['passed'] ? '[OK] ' : '[PERLU DICEK] ') . $check['name'];
            $check['passed'] ? $this->info($line) : $this->warn($line);
            $this->line('  ' . $check['message']);
        });

        $failed = $checks->where('passed', false)->count();
        $this->line('');
        $failed === 0
            ? $this->info('Readiness check bersih. Website siap masuk final testing production.')
            : $this->warn("Masih ada {$failed} catatan penting sebelum live.");

        return $this->option('strict') && $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function check(string $name, bool $passed, string $message): array
    {
        return compact('name', 'passed', 'message');
    }

    protected function hasFinalPublicUrl(): bool
    {
        $url = strtolower((string) config('wiboost.public_url', config('app.url')));

        return filled($url)
            && ! str_contains($url, 'localhost')
            && ! str_contains($url, '127.0.0.1')
            && ! str_contains($url, 'ngrok-free');
    }
}
