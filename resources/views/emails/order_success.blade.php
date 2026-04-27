<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Berhasil</title>
</head>
<body style="margin:0;background:#f4f9ff;font-family:Arial,Helvetica,sans-serif;color:#2b3a67;padding:30px 10px;">
    @php
        $historyUrl = route('user.history');
        $credential = $transaction->credential_data ?? [];
        $fields = $transaction->order_input_fields ?? [];
    @endphp

    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:40px;border:6px solid #ffffff;box-shadow:0 20px 40px rgba(189,224,254,0.6);overflow:hidden;">
        <div style="background:linear-gradient(135deg,#8faaf3,#5a76c8,#4bc6b9);padding:40px 30px;color:#ffffff;text-align:center;">
            <div style="font-size:50px;margin-bottom:15px;">🎉</div>
            <div style="display:inline-block;border-radius:20px;background:rgba(255,255,255,0.2);border:2px solid rgba(255,255,255,0.5);padding:8px 16px;font-size:11px;font-weight:900;letter-spacing:0.15em;text-transform:uppercase;">Wiboost Store</div>
            <h1 style="margin:15px 0 10px;font-size:28px;font-weight:900;">Pesanan Berhasil!</h1>
            <p style="margin:0;color:rgba(255,255,255,0.9);font-size:15px;font-weight:bold;">Terima kasih sudah jajan di Wiboost.</p>
        </div>

        <div style="padding:35px 30px;">
            <p style="margin:0 0 20px;font-size:15px;line-height:1.6;font-weight:bold;">Halo <span style="color:#5a76c8;">{{ $transaction->user->name ?? 'Pelanggan Wiboost' }}</span>, pesananmu dengan invoice <strong>{{ $transaction->invoice_number }}</strong> sudah berstatus sukses.</p>

            <div style="border-radius:25px;background:#f4f9ff;border:3px solid #e0fbfc;padding:20px;margin:25px 0;">
                <table style="width:100%;border-collapse:collapse;font-size:14px;color:#2b3a67;">
                    <tr>
                        <td style="padding:10px 0;color:#8faaf3;font-weight:900;font-size:12px;text-transform:uppercase;">Produk</td>
                        <td style="padding:10px 0;text-align:right;font-weight:900;font-size:15px;">{{ $transaction->product->name ?? 'Produk Wiboost' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#8faaf3;font-weight:900;font-size:12px;text-transform:uppercase;">Total Bayar</td>
                        <td style="padding:10px 0;text-align:right;font-weight:900;font-size:18px;color:#4bc6b9;">Rp {{ number_format((float) $transaction->amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#8faaf3;font-weight:900;font-size:12px;text-transform:uppercase;">Target</td>
                        <td style="padding:10px 0;text-align:right;font-weight:900;font-size:14px;">{{ $transaction->target_data ?: '-' }}</td>
                    </tr>
                </table>
            </div>

            @if($fields !== [])
                <div style="border-radius:25px;border:3px solid #f0f5ff;background:#f8faff;padding:20px;margin-bottom:20px;">
                    <p style="margin:0 0 15px;font-size:11px;font-weight:900;color:#5a76c8;text-transform:uppercase;letter-spacing:0.1em;">📝 Data Input Pesanan</p>
                    @foreach($fields as $field)
                        <div style="background:#ffffff;padding:12px 15px;border-radius:15px;border:2px solid #f0f5ff;margin-bottom:10px;">
                            <p style="margin:0 0 4px;font-size:11px;color:#8faaf3;font-weight:900;">{{ $field['label'] ?? 'Input' }}</p>
                            <p style="margin:0;font-size:14px;font-weight:900;">{{ $field['value'] ?? '-' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            @if(is_array($credential) && array_filter($credential))
                <div style="border-radius:25px;background:#fffcf0;border:3px solid #fef3c7;padding:20px;margin-bottom:20px;">
                    <p style="margin:0 0 15px;font-size:11px;font-weight:900;color:#d97706;text-transform:uppercase;letter-spacing:0.1em;">🔑 Akses Produk Kamu</p>
                    @foreach(['email' => 'Email / Nomor', 'password' => 'Password', 'profile' => 'Profil', 'pin' => 'PIN', 'link' => 'Link Akses', 'tutorial_link' => 'Link Tutorial'] as $key => $label)
                        @if(!empty($credential[$key]))
                            <div style="background:#ffffff;padding:12px 15px;border-radius:15px;border:2px solid #fef3c7;margin-bottom:10px;">
                                <p style="margin:0 0 4px;font-size:11px;color:#f59e0b;font-weight:900;">{{ $label }}</p>
                                <p style="margin:0;font-size:14px;font-weight:900;color:#b45309;word-break:break-all;">{{ $credential[$key] }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            <div style="margin:35px 0 10px;text-align:center;">
                <a href="{{ $historyUrl }}" style="display:inline-block;border-radius:30px;background:#5a76c8;border:3px solid #ffffff;color:#ffffff;font-weight:900;text-decoration:none;padding:16px 30px;font-size:15px;box-shadow:0 10px 20px rgba(90,118,200,0.3);">Lihat Detail di Website 🚀</a>
            </div>

            <p style="margin:30px 0 0;color:#8faaf3;font-size:11px;font-weight:bold;text-align:center;line-height:1.6;">Email ini otomatis dikirim oleh sistem Wiboost Store.<br>Jangan ragu hubungi Admin jika butuh bantuan!</p>
        </div>
    </div>
</body>
</html>