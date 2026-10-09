<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $subjectText }}</title>
</head>
<body style="margin:0;padding:0;background:#eff6ff;font-family:Arial,Helvetica,sans-serif;color:#2b3a67;">
@php
    $logoPath = public_path('images/wiboost-logo-nav.png');
    $logoUrl = file_exists($logoPath)
        ? $message->embed($logoPath)
        : secure_url('images/wiboost-logo-nav.png') . '?v=20260723-mail';
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#eff6ff" style="width:100%;border-collapse:collapse;background:#eff6ff;">
    <tr>
        <td align="center" style="padding:22px 10px;">
            <table role="presentation" width="640" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="width:100%;max-width:640px;border-collapse:separate;border-spacing:0;background:#ffffff;border:4px solid #dbeafe;border-radius:30px;overflow:hidden;box-shadow:0 22px 60px rgba(90,118,200,.18);">
                <tr>
                    <td align="center" bgcolor="#dbeafe" style="padding:28px 22px;background:#dbeafe;text-align:center;">
                        <img src="{{ $logoUrl }}" alt="Logo Wiboost Store" width="72" height="72" style="display:block;width:72px;height:72px;margin:0 auto 12px;border:0;border-radius:999px;object-fit:contain;">
                        <p style="margin:0;font-size:24px;line-height:1.15;font-weight:900;color:#2b3a67;text-align:center;">Wiboost Store</p>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#f8fbff" style="padding:26px 22px;background:#f8fbff;">
                        <h1 style="margin:0 0 18px;font-size:26px;line-height:1.25;font-weight:900;color:#2b3a67;word-break:break-word;">{{ $subjectText }}</h1>
                        <div style="margin:0;font-size:15px;line-height:1.75;font-weight:600;color:#2b3a67;white-space:pre-line;word-break:break-word;">{{ $bodyText }}</div>

                        @if(filled($actionLabel) && filled($actionUrl))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin-top:24px;border-collapse:collapse;">
                                <tr>
                                    <td align="center" style="text-align:center;">
                                        <a href="{{ $actionUrl }}" target="_blank" rel="noopener" style="display:inline-block;min-width:190px;border:3px solid #ffffff;border-radius:999px;background:#5a76c8;color:#ffffff;font-size:14px;line-height:1.2;font-weight:900;text-align:center;text-decoration:none;padding:15px 24px;box-shadow:0 14px 28px rgba(90,118,200,.25);">{{ $actionLabel }}</a>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        <p style="margin:26px 0 2px;text-align:center;font-size:11px;line-height:1.6;font-weight:700;color:#8faaf3;">Email ini dikirim oleh tim Wiboost Store.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
