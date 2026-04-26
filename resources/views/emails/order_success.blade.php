<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Berhasil</title>
</head>
<body style="margin:0;background:#f4f9ff;font-family:Arial,Helvetica,sans-serif;color:#2b3a67;">
    @php
        $historyUrl = route('user.history');
        $credential = $transaction->credential_data ?? [];
        $fields = $transaction->order_input_fields ?? [];
    @endphp

    <div style="padding:32px 16px;">
        <div style="max-width:660px;margin:0 auto;overflow:hidden;border-radius:30px;border:1px solid #dce8ff;background:#ffffff;box-shadow:0 18px 45px rgba(90,118,200,.16);">
            <div style="background:linear-gradient(135deg,#8faaf3,#5a76c8 58%,#4bc6b9);padding:32px;color:#ffffff;">
                <div style="display:inline-block;border-radius:16px;background:rgba(255,255,255,.18);padding:10px 14px;font-size:12px;font-weight:800;letter-spacing:.18em;text-transform:uppercase;">Wiboost Store</div>
                <h1 style="margin:20px 0 8px;font-size:30px;line-height:1.15;">Pesanan kamu berhasil</h1>
                <p style="margin:0;color:rgba(255,255,255,.86);font-size:14px;line-height:1.7;">Terima kasih sudah transaksi di Wiboost. Detail pesananmu sudah kami simpan di riwayat akun.</p>
            </div>

            <div style="padding:30px;">
                <p style="margin:0 0 18px;font-size:15px;line-height:1.7;">Halo <strong>{{ $transaction->user->name ?? 'Pelanggan Wiboost' }}</strong>, pesanan dengan invoice <strong>{{ $transaction->invoice_number }}</strong> sudah berstatus sukses.</p>

                <div style="border-radius:24px;background:#f0f5ff;padding:20px;margin:22px 0;">
                    <table style="width:100%;border-collapse:collapse;font-size:14px;color:#2b3a67;">
                        <tr>
                            <td style="padding:8px 0;color:#8faaf3;font-weight:800;">Produk</td>
                            <td style="padding:8px 0;text-align:right;font-weight:800;">{{ $transaction->product->name ?? 'Produk Wiboost' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0;color:#8faaf3;font-weight:800;">Total</td>
                            <td style="padding:8px 0;text-align:right;font-weight:800;">Rp {{ number_format((float) $transaction->amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="padding:8px 0;color:#8faaf3;font-weight:800;">Target</td>
                            <td style="padding:8px 0;text-align:right;font-weight:800;">{{ $transaction->target_data ?: '-' }}</td>
                        </tr>
                    </table>
                </div>

                @if($fields !== [])
                    <div style="border-radius:22px;border:1px solid #e0ebff;padding:18px;margin-bottom:18px;">
                        <p style="margin:0 0 12px;font-size:13px;font-weight:800;color:#5a76c8;text-transform:uppercase;letter-spacing:.12em;">Data Pesanan</p>
                        @foreach($fields as $field)
                            <p style="margin:8px 0;font-size:13px;line-height:1.6;"><strong>{{ $field['label'] ?? 'Input' }}:</strong> {{ $field['value'] ?? '-' }}</p>
                        @endforeach
                    </div>
                @endif

                @if(is_array($credential) && array_filter($credential))
                    <div style="border-radius:22px;background:#e6fff7;padding:18px;margin-bottom:18px;color:#176b5c;">
                        <p style="margin:0 0 12px;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.12em;">Akses Produk</p>
                        @foreach(['email' => 'Email / Nomor', 'password' => 'Password', 'profile' => 'Profil', 'pin' => 'PIN', 'link' => 'Link Akses', 'tutorial_link' => 'Link Tutorial'] as $key => $label)
                            @if(!empty($credential[$key]))
                                <p style="margin:8px 0;font-size:13px;line-height:1.6;"><strong>{{ $label }}:</strong> {{ $credential[$key] }}</p>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div style="margin:26px 0;text-align:center;">
                    <a href="{{ $historyUrl }}" style="display:inline-block;border-radius:18px;background:#4bc6b9;color:#ffffff;font-weight:800;text-decoration:none;padding:15px 24px;box-shadow:0 10px 22px rgba(75,198,185,.28);">Buka Riwayat Pesanan</a>
                </div>

                <p style="margin:24px 0 0;color:#8faaf3;font-size:12px;text-align:center;line-height:1.7;">Email ini dikirim otomatis oleh Wiboost Store. Jika ada kendala, laporkan lewat tombol bantuan di website.</p>
            </div>
        </div>
    </div>
</body>
</html>
