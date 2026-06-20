# Deploy WiboostStore ke Domainesia

Paket ini sudah membawa source terbaru, `vendor`, dan hasil build frontend. File `.env`
hosting sengaja tidak dimasukkan agar kredensial production tidak tertimpa.

## 1. Pengaturan `.env`

Pastikan nilai production berikut sudah benar:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
WIBOOST_PUBLIC_URL=https://domain-anda.com

XOFTWARE_API_KEY=...
XOFTWARE_SECRET_KEY=...
XOFTWARE_WEBHOOK_SECRET=...
XOFTWARE_MERCHANT_ID=...
XOFTWARE_BASE_URL=https://payment1.xoftware.id
XOFTWARE_DEFAULT_CHANNEL=QRISREALTIME
XOFTWARE_FEE_DIRECTION=merchant
XOFTWARE_BUYER_FEE_SHARE=0.5
XOFTWARE_QRIS_REALTIME_FEE_PERCENT=0.007
XOFTWARE_QRIS_REALTIME_FEE_FIXED=250
```

Atur webhook Xoftware ke:

```text
https://domain-anda.com/xoftware/callback
```

## 2. Command terminal setelah ZIP diunggah

Unggah ZIP ke folder `/home/wiboosto/wiboostore` seperti pada File Manager.
File `.env` production tidak ikut di dalam ZIP, tetapi tetap dibuatkan backup sebelum deploy.

```bash
cd /home/wiboosto/wiboostore
cp .env ".env.backup_$(date +%Y%m%d_%H%M%S)"
php artisan down || true
unzip -o wibooststore-domainesia-revisi-final-20260612.zip
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link || true
chmod -R 775 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Jika versi PHP default terminal berbeda dengan versi website, ganti `php` memakai
path PHP 8.2 atau 8.3 yang disediakan Domainesia.

## 3. Cron wajib

Tambahkan cron satu menit sekali agar status provider tetap diperiksa:

```cron
* * * * * cd /home/wiboosto/wiboostore && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

Jika `QUEUE_CONNECTION` menggunakan `database` atau `redis`, jalankan worker queue
melalui fitur cron/supervisor hosting. Dengan `QUEUE_CONNECTION=sync`, worker terpisah
tidak diperlukan.

## 4. Membuat ulang ZIP

Jalankan dari PowerShell pada komputer lokal:

```powershell
npm run build
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\create-deploy-zip.ps1
```
