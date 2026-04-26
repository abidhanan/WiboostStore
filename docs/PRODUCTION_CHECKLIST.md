# Wiboost Store Production Checklist

Gunakan checklist ini sebelum menerima buyer asli.

## 1. Hosting dan domain final

- Gunakan domain tetap, bukan URL ngrok gratis.
- Set `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-kamu.com
WIBOOST_PUBLIC_URL=https://domain-kamu.com
```

- Jalankan:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

## 2. Midtrans callback

Set callback/notification URL di dashboard Midtrans:

```text
https://domain-kamu.com/midtrans/callback
```

Pastikan mode key sesuai kebutuhan:

- `MIDTRANS_IS_PRODUCTION=false` untuk sandbox.
- `MIDTRANS_IS_PRODUCTION=true` untuk transaksi asli.

## 3. Cron scheduler

Pasang cron di server:

```cron
* * * * * php /path/to/WiboostStore/artisan schedule:run >> /dev/null 2>&1
```

Scheduler menjalankan:

- `wiboost:check-provider-orders` setiap 5 menit.
- `wiboost:auto-refund` setiap jam.
- `wiboost:maintenance-report` setiap 2 jam.

## 4. Queue worker

Jika `QUEUE_CONNECTION=database`, jalankan worker:

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work --tries=3 --timeout=90
```

Di hosting VPS, jalankan queue worker lewat Supervisor atau process manager sejenis.

## 5. Rotasi secret sebelum live

Regenerate semua secret yang pernah dibagikan atau dipakai testing:

- `APP_KEY` jika pernah bocor.
- Midtrans server/client key.
- Digiflazz username/key.
- OrderSosmed API key/secret key.
- Gmail app password.
- Discord webhook URL.

Setelah mengganti `.env`, jalankan:

```bash
php artisan config:clear
php artisan optimize
```

## 6. Halaman legal dan trust

Pastikan halaman ini bisa dibuka:

- `/legal/terms`
- `/legal/privacy-policy`
- `/legal/refund-policy`
- `/legal/contact`

## 7. Final testing transaksi real

Uji dengan nominal kecil:

- Deposit saldo via Midtrans.
- Top up game dengan User ID dan Zone ID.
- Kuota murah ke nomor sendiri.
- Suntik sosmed quantity kecil.
- Aplikasi premium stok test.
- Nomor luar stok test.
- Buzzer manual test.
- Reset password dari email asli.
- Provider gagal atau produk nonaktif untuk memastikan refund berjalan.

## Readiness command

Jalankan:

```bash
php artisan wiboost:readiness-check
```

Gunakan strict mode untuk CI/deploy:

```bash
php artisan wiboost:readiness-check --strict
```
