<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Pesanan Berhasil</title>
</head>
<body style="margin:0;padding:0;background:#f4f9ff;font-family:Arial,Helvetica,sans-serif;color:#2b3a67;">
@php
    $historyUrl = route('user.history');
    $credential = is_array($transaction->credential_data ?? null) ? $transaction->credential_data : [];
    $fields = is_array($transaction->order_input_fields ?? null) ? $transaction->order_input_fields : [];
    $productName = $transaction->product->name ?? 'Produk Wiboost';
    $targetData = $transaction->target_data ?: '-';
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;background:#f4f9ff;">
    <tr>
        <td align="center" style="padding:24px 10px;">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;border-collapse:separate;border-spacing:0;background:#111827;border:1px solid #253044;border-radius:28px;overflow:hidden;">
                <tr>
                    <td align="center" style="padding:34px 24px;background:#5a76c8;background-image:linear-gradient(135deg,#8faaf3,#5a76c8,#4bc6b9);color:#ffffff;">
                        <div style="display:inline-block;border-radius:999px;background:rgba(43,58,103,.35);border:1px solid rgba(255,255,255,.35);padding:8px 18px;font-size:11px;font-weight:800;letter-spacing:4px;text-transform:uppercase;">
                            WIBOOST STORE
                        </div>
                        <h1 style="margin:18px 0 8px;font-size:30px;line-height:1.15;font-weight:900;color:#ffffff;">Pesanan Berhasil!</h1>
                        <p style="margin:0;font-size:16px;line-height:1.5;font-weight:700;color:#eef7ff;">Terima kasih sudah order di Wiboost Store.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px 22px 8px;background:#111827;color:#dbe7ff;">
                        <p style="margin:0 0 18px;font-size:16px;line-height:1.7;font-weight:700;color:#dbe7ff;">
                            Halo <span style="color:#9fb7ff;">{{ $transaction->user->name ?? 'Pelanggan Wiboost' }}</span>, pesananmu dengan invoice <strong style="color:#ffffff;">{{ $transaction->invoice_number }}</strong> sudah berstatus sukses.
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:separate;border-spacing:0;border:1px solid #4b5563;border-radius:22px;background:#1f2430;">
                            <tr>
                                <td style="padding:18px 20px;border-bottom:1px solid #374151;">
                                    <p style="margin:0 0 6px;font-size:11px;line-height:1.4;font-weight:900;letter-spacing:1.5px;text-transform:uppercase;color:#9fb7ff;">Produk</p>
                                    <p style="margin:0;font-size:17px;line-height:1.45;font-weight:900;color:#eef2ff;word-break:break-word;">{{ $productName }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:18px 20px;border-bottom:1px solid #374151;">
                                    <p style="margin:0 0 6px;font-size:11px;line-height:1.4;font-weight:900;letter-spacing:1.5px;text-transform:uppercase;color:#9fb7ff;">Total Bayar</p>
                                    <p style="margin:0;font-size:24px;line-height:1.2;font-weight:900;color:#4bc6b9;">Rp {{ number_format((float) $transaction->amount, 0, ',', '.') }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:18px 20px;">
                                    <p style="margin:0 0 6px;font-size:11px;line-height:1.4;font-weight:900;letter-spacing:1.5px;text-transform:uppercase;color:#9fb7ff;">Target</p>
                                    <p style="margin:0;font-size:15px;line-height:1.55;font-weight:800;color:#eef2ff;word-break:break-word;">{{ $targetData }}</p>
                                </td>
                            </tr>
                        </table>

                        @if($fields !== [])
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin-top:20px;border-collapse:separate;border-spacing:0;border:1px solid #4b5563;border-radius:22px;background:#1b202b;">
                                <tr>
                                    <td style="padding:18px 20px 8px;">
                                        <p style="margin:0;font-size:12px;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:#9fb7ff;">Data Input Pesanan</p>
                                    </td>
                                </tr>
                                @foreach($fields as $field)
                                    <tr>
                                        <td style="padding:8px 20px 14px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:separate;border-spacing:0;border:1px solid #374151;border-radius:14px;background:#111827;">
                                                <tr>
                                                    <td style="padding:12px 14px;">
                                                        <p style="margin:0 0 5px;font-size:11px;font-weight:900;color:#9fb7ff;">{{ preg_replace('/email\s+khusus\s+aplikasi/i', 'Email', $field['label'] ?? 'Input') }}</p>
                                                        <p style="margin:0;font-size:14px;line-height:1.5;font-weight:800;color:#eef2ff;word-break:break-word;">{{ $field['value'] ?? '-' }}</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif

                        @if(array_filter($credential))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin-top:20px;border-collapse:separate;border-spacing:0;border:1px solid #a16207;border-radius:22px;background:#241c0b;">
                                <tr>
                                    <td style="padding:18px 20px 8px;">
                                        <p style="margin:0;font-size:12px;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:#fbbf24;">Akses Produk Kamu</p>
                                    </td>
                                </tr>
                                @foreach(['email' => 'Email / Nomor', 'password' => 'Password', 'profile' => 'Profil', 'pin' => 'PIN', 'link' => 'Link Akses', 'tutorial_link' => 'Link Tutorial'] as $key => $label)
                                    @if(!empty($credential[$key]))
                                        <tr>
                                            <td style="padding:8px 20px 14px;">
                                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:separate;border-spacing:0;border:1px solid #78350f;border-radius:14px;background:#111827;">
                                                    <tr>
                                                        <td style="padding:12px 14px;">
                                                            <p style="margin:0 0 5px;font-size:11px;font-weight:900;color:#fbbf24;">{{ $label }}</p>
                                                            <p style="margin:0;font-size:14px;line-height:1.5;font-weight:800;color:#fff7ed;word-break:break-word;">{{ $credential[$key] }}</p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </table>
                        @endif

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin:28px 0 14px;border-collapse:collapse;">
                            <tr>
                                <td align="center">
                                    <a href="{{ $historyUrl }}" style="display:inline-block;border-radius:999px;background:#5a76c8;color:#ffffff;font-size:15px;font-weight:900;text-decoration:none;padding:15px 28px;">Lihat Detail di Website</a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:20px 0 10px;text-align:center;font-size:11px;line-height:1.6;font-weight:700;color:#9fb7ff;">Email ini otomatis dikirim oleh sistem Wiboost Store.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
