<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f9ff; padding: 20px; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 15px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { color: #5a76c8; font-size: 24px; font-weight: bold; margin-bottom: 20px; text-align: center; }
        .content { color: #333333; line-height: 1.6; }
        .box { background-color: #f0f5ff; padding: 15px; border-radius: 10px; margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #5a76c8; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Wiboost Store</div>
        <div class="content">
            <p>Halo, <b>{{ $transaction->user->name ?? 'Pelanggan Setia' }}</b>! 👋</p>
            <p>Terima kasih sudah jajan di Wiboost. Pesanan kamu dengan nomor invoice <b>{{ $transaction->invoice_number }}</b> telah berhasil diproses.</p>
            
            <div class="box">
                <p><b>Detail Layanan:</b> {{ $transaction->product->name ?? 'Produk Wiboost' }}</p>
                <p><b>Total Harga:</b> Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                <p><b>Status:</b> ✅ Sukses</p>
            </div>

            <p>Untuk melihat detail data akun/nomor/pesanan, silakan cek langsung di menu Riwayat Pesanan pada Dashboard kamu.</p>
            
            <div style="text-align: center;">
                <a href="{{ route('user.history') }}" class="btn">Cek Riwayat Pesanan</a>
            </div>
            
            <p style="margin-top: 30px; font-size: 12px; color: #888; text-align: center;">Ini adalah email otomatis, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>