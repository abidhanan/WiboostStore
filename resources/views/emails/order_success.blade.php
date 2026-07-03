<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Pesanan Sukses Wiboost Store</title>
    <style>
        :root {
            color-scheme: light;
            supported-color-schemes: light;
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#eff6ff;font-family:Arial,Helvetica,sans-serif;color:#2b3a67;">
@php
    $logoPath = public_path('images/wiboost-logo-nav.png');
    $logoUrl = file_exists($logoPath)
        ? $message->embed($logoPath)
        : asset('images/wiboost-logo-nav.png');
    $credential = is_array($transaction->credential_data ?? null) ? $transaction->credential_data : [];
    $fields = is_array($transaction->order_input_fields ?? null) ? $transaction->order_input_fields : [];
    $productName = $transaction->product->name ?? 'Produk Wiboost';
    $detailUrl = route('user.history', ['auto_open' => $transaction->id]);
    $isFailed = $transaction->order_status === 'failed' || $transaction->payment_status === 'failed';
    $headline = $isFailed ? 'Pesanan Gagal' : 'Pesanan Sukses!';
    $intro = $isFailed
        ? 'Halo ' . ($transaction->user->name ?? 'Pelanggan Wiboost') . ', pesanan kamu gagal diproses. Silakan cek detail pesanan di Wiboost Store.'
        : 'Halo ' . ($transaction->user->name ?? 'Pelanggan Wiboost') . ', pesanan kamu sudah sukses. Terima kasih sudah order di Wiboost Store.';
    $categoryPath = collect();
    $categoryCursor = $transaction->product?->category;

    while ($categoryCursor) {
        $categoryPath->prepend($categoryCursor->name);
        $categoryCursor = $categoryCursor->parent;
    }

    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $createdAt = $transaction->created_at?->copy()->timezone(config('app.timezone', 'Asia/Jakarta'));
    $transactionTime = $createdAt
        ? $createdAt->format('d') . ' ' . ($monthNames[(int) $createdAt->format('n')] ?? $createdAt->format('M')) . ' ' . $createdAt->format('Y, H:i')
        : '-';

    $detailRows = [
        ['label' => 'Invoice', 'value' => $transaction->invoice_number],
        ['label' => 'Metode Bayar', 'value' => $transaction->display_payment_method],
        ['label' => 'Total Bayar', 'value' => 'Rp ' . number_format((int) $transaction->display_total_payable, 0, ',', '.')],
        ['label' => 'Status Transaksi', 'value' => strtoupper($transaction->display_status)],
        ['label' => 'Waktu Transaksi', 'value' => $transactionTime],
    ];

    if ($categoryPath->isNotEmpty()) {
        $detailRows[] = ['label' => 'Kategori', 'value' => $categoryPath->implode(' / ')];
    }

    $credentialLabels = [
        'email' => 'Email',
        'password' => 'Password',
        'profile' => 'Profil',
        'pin' => 'PIN',
        'link' => 'Link Akses',
        'tutorial_link' => 'Link Tutorial',
    ];
    $hasCredentialRows = false;

    foreach (array_keys($credentialLabels) as $credentialKey) {
        if (! empty($credential[$credentialKey])) {
            $hasCredentialRows = true;
            break;
        }
    }
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#eff6ff" style="width:100%;border-collapse:collapse;background:#eff6ff;">
    <tr>
        <td align="center" style="padding:22px 10px;">
            <table role="presentation" width="640" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="width:100%;max-width:640px;border-collapse:separate;border-spacing:0;background:#ffffff;border:4px solid #dbeafe;border-radius:30px;overflow:hidden;box-shadow:0 22px 60px rgba(90,118,200,.18);">
                <tr>
                    <td align="center" bgcolor="#dbeafe" style="padding:30px 22px;background:#dbeafe;background-image:linear-gradient(135deg,#bfdbfe 0%,#dbeafe 55%,#eff6ff 100%);text-align:center;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;">
                            <tr>
                                <td align="center" style="text-align:center;">
                                    <img src="{{ $logoUrl }}" alt="Logo Wiboost Store" width="72" height="72" style="display:block;width:72px;height:72px;margin:0 auto 12px;border:0;border-radius:999px;object-fit:contain;">
                                    <p style="margin:0;font-size:24px;line-height:1.15;font-weight:900;color:#2b3a67;text-align:center;">Wiboost <span style="color:#5a76c8;">Store</span></p>
                                </td>
                            </tr>
                        </table>

                        <h1 style="margin:24px 0 10px;font-size:30px;line-height:1.12;font-weight:900;color:#2b3a67;text-align:center;">{{ $headline }}</h1>
                        <p style="margin:0 auto;max-width:520px;font-size:15px;line-height:1.65;font-weight:700;color:#5a76c8;text-align:center;">{{ $intro }}</p>
                    </td>
                </tr>

                <tr>
                    <td bgcolor="#f8fbff" style="padding:22px;background:#f8fbff;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="width:100%;border-collapse:separate;border-spacing:0;border:2px solid #dbeafe;border-radius:24px;background:#ffffff;overflow:hidden;">
                            <tr>
                                <td style="padding:18px 18px 6px;">
                                    <p style="margin:0;font-size:11px;line-height:1.3;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:#5a76c8;">Produk</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0 18px 18px;">
                                    <p style="margin:0;font-size:22px;line-height:1.25;font-weight:900;color:#2b3a67;word-break:break-word;">{{ $productName }}</p>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin-top:14px;border-collapse:separate;border-spacing:0;">
                            @foreach($detailRows as $row)
                                <tr>
                                    <td style="padding:0 0 10px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="width:100%;border-collapse:separate;border-spacing:0;border:2px solid #dbeafe;border-radius:18px;background:#ffffff;">
                                            <tr>
                                                <td style="padding:14px 16px;">
                                                    <p style="margin:0 0 5px;font-size:10px;line-height:1.3;font-weight:900;letter-spacing:1.6px;text-transform:uppercase;color:#5a76c8;">{{ $row['label'] }}</p>
                                                    <p style="margin:0;font-size:16px;line-height:1.45;font-weight:900;color:{{ $row['label'] === 'Total Bayar' ? '#3f67c6' : '#2b3a67' }};word-break:break-word;">{{ $row['value'] }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </table>

                        @if($fields !== [])
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#eef6ff" style="width:100%;margin-top:8px;border-collapse:separate;border-spacing:0;border:2px dashed #93b4f4;border-radius:24px;background:#eef6ff;">
                                <tr>
                                    <td style="padding:18px 18px 8px;">
                                        <p style="margin:0;font-size:12px;line-height:1.3;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:#5a76c8;">Input Buyer</p>
                                    </td>
                                </tr>
                                @foreach($fields as $field)
                                    <tr>
                                        <td style="padding:0 18px 12px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="width:100%;border-collapse:separate;border-spacing:0;border-radius:16px;background:#ffffff;">
                                                <tr>
                                                    <td style="padding:13px 14px;">
                                                        <p style="margin:0 0 5px;font-size:10px;line-height:1.3;font-weight:900;letter-spacing:1px;text-transform:uppercase;color:#5a76c8;">{{ preg_replace('/email\s+khusus\s+aplikasi/i', 'Email', $field['label'] ?? 'Input') }}</p>
                                                        <p style="margin:0;font-size:15px;line-height:1.45;font-weight:800;color:#2b3a67;word-break:break-word;">{{ $field['value'] ?? '-' }}</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif

                        @if($hasCredentialRows)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#eaf2ff" style="width:100%;margin-top:16px;border-collapse:separate;border-spacing:0;border:2px solid #93b4f4;border-radius:24px;background:#eaf2ff;">
                                <tr>
                                    <td style="padding:18px 18px 8px;">
                                        <p style="margin:0;font-size:12px;line-height:1.3;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:#3f67c6;">Akses Produk</p>
                                    </td>
                                </tr>
                                @foreach($credentialLabels as $key => $label)
                                    @if(!empty($credential[$key]))
                                        @php
                                            $credentialValue = (string) $credential[$key];
                                            $isCredentialUrl = preg_match('/^https?:\/\//i', $credentialValue) === 1;
                                        @endphp
                                        <tr>
                                            <td style="padding:0 18px 12px;">
                                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="width:100%;border-collapse:separate;border-spacing:0;border-radius:16px;background:#ffffff;">
                                                    <tr>
                                                        <td style="padding:13px 14px;">
                                                            <p style="margin:0 0 5px;font-size:10px;line-height:1.3;font-weight:900;letter-spacing:1px;text-transform:uppercase;color:#5a76c8;">{{ $label }}</p>
                                                            @if($isCredentialUrl)
                                                                <a href="{{ $credentialValue }}" style="font-size:15px;line-height:1.45;font-weight:800;color:#5a76c8;text-decoration:underline;word-break:break-word;">{{ $credentialValue }}</a>
                                                            @else
                                                                <p style="margin:0;font-size:15px;line-height:1.45;font-weight:800;color:#2b3a67;word-break:break-word;">{{ $credentialValue }}</p>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </table>
                        @endif

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin-top:20px;border-collapse:collapse;">
                            <tr>
                                <td align="center" style="text-align:center;">
                                    <a href="{{ $detailUrl }}" target="_blank" style="display:inline-block;min-width:210px;border:3px solid #ffffff;border-radius:999px;background:#5a76c8;color:#ffffff;font-size:14px;line-height:1.2;font-weight:900;text-align:center;text-decoration:none;padding:15px 24px;box-shadow:0 14px 28px rgba(90,118,200,.25);">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:24px 0 2px;text-align:center;font-size:11px;line-height:1.6;font-weight:700;color:#5a76c8;">Email ini otomatis dikirim oleh sistem.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
