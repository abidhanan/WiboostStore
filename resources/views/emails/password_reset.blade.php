<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password Wiboost Store</title>
</head>
<body style="margin:0;background:#f4f9ff;font-family:Arial,Helvetica,sans-serif;color:#2b3a67;padding:30px 10px;">
    <div style="max-width:550px;margin:0 auto;background:#ffffff;border-radius:40px;border:6px solid #ffffff;box-shadow:0 20px 40px rgba(189,224,254,0.6);overflow:hidden;">
        <div style="background:linear-gradient(135deg,#8faaf3,#5a76c8);padding:40px 30px;color:#ffffff;text-align:center;">
            <div style="font-size:50px;margin-bottom:15px;">🔒</div>
            <div style="display:inline-block;border-radius:20px;background:rgba(255,255,255,0.2);border:2px solid rgba(255,255,255,0.5);padding:8px 16px;font-size:11px;font-weight:900;letter-spacing:0.15em;text-transform:uppercase;">Keamanan Akun</div>
            <h1 style="margin:15px 0 10px;font-size:28px;font-weight:900;">Reset Sandi</h1>
            <p style="margin:0;color:rgba(255,255,255,0.9);font-size:15px;font-weight:bold;">Kami menerima permintaan pengaturan ulang kata sandi.</p>
        </div>

        <div style="padding:35px 30px;text-align:center;">
            <p style="margin:0 0 20px;font-size:15px;line-height:1.6;font-weight:bold;">Halo <span style="color:#5a76c8;">{{ $user->name ?? 'Pelanggan Wiboost' }}</span>, silakan klik tombol di bawah ini untuk membuat kata sandi baru untuk akunmu.</p>

            <div style="margin:35px 0;">
                <a href="{{ $resetUrl }}" style="display:inline-block;border-radius:30px;background:#4bc6b9;border:4px solid #ffffff;color:#ffffff;font-weight:900;text-decoration:none;padding:18px 32px;font-size:16px;box-shadow:0 12px 25px rgba(75,198,185,0.4);">Buat Sandi Baru ✨</a>
            </div>

            <div style="border-radius:25px;background:#fff5eb;border:3px solid #fef3c7;padding:20px;color:#d97706;font-size:13px;line-height:1.6;font-weight:bold;text-align:left;">
                ⚠️ Link ini hanya berlaku selama <strong>{{ $expiresIn }} menit</strong>. Jika kamu tidak pernah merasa meminta reset sandi, cukup abaikan email ini dan akunmu tetap aman.
            </div>

            <div style="margin-top:30px;padding-top:20px;border-top:2px dashed #f0f5ff;font-size:11px;font-weight:bold;color:#8faaf3;text-align:left;line-height:1.6;">
                Jika tombol di atas tidak berfungsi, salin dan tempel link berikut ke browsermu:<br>
                <span style="color:#5a76c8;word-break:break-all;">{{ $resetUrl }}</span>
            </div>
        </div>
    </div>
</body>
</html>