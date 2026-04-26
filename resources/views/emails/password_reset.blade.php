<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password Wiboost Store</title>
</head>
<body style="margin:0;background:#f4f9ff;font-family:Arial,Helvetica,sans-serif;color:#2b3a67;">
    <div style="padding:32px 16px;">
        <div style="max-width:620px;margin:0 auto;overflow:hidden;border-radius:28px;border:1px solid #dce8ff;background:#ffffff;box-shadow:0 18px 45px rgba(90,118,200,.16);">
            <div style="background:linear-gradient(135deg,#8faaf3,#5a76c8 58%,#4bc6b9);padding:30px;color:#ffffff;">
                <div style="display:inline-block;border-radius:16px;background:rgba(255,255,255,.18);padding:10px 14px;font-size:12px;font-weight:800;letter-spacing:.18em;text-transform:uppercase;">Wiboost Store</div>
                <h1 style="margin:20px 0 8px;font-size:28px;line-height:1.15;">Reset password akunmu</h1>
                <p style="margin:0;color:rgba(255,255,255,.86);font-size:14px;line-height:1.7;">Kami menerima permintaan untuk membuat kata sandi baru di akun Wiboost Store.</p>
            </div>

            <div style="padding:30px;">
                <p style="margin:0 0 16px;font-size:15px;line-height:1.7;">Halo <strong>{{ $user->name ?? 'Pelanggan Wiboost' }}</strong>, klik tombol di bawah ini untuk mengatur ulang password.</p>

                <div style="margin:26px 0;text-align:center;">
                    <a href="{{ $resetUrl }}" style="display:inline-block;border-radius:18px;background:#4bc6b9;color:#ffffff;font-weight:800;text-decoration:none;padding:15px 24px;box-shadow:0 10px 22px rgba(75,198,185,.28);">Buat Password Baru</a>
                </div>

                <div style="border-radius:20px;background:#f0f5ff;padding:18px;color:#4a5f96;font-size:13px;line-height:1.7;">
                    Link ini berlaku selama {{ $expiresIn }} menit. Jika kamu tidak meminta reset password, abaikan email ini dan password lama tetap aman.
                </div>

                <p style="margin:22px 0 0;color:#8faaf3;font-size:12px;line-height:1.7;">Jika tombol tidak bisa dibuka, salin link ini ke browser:<br><span style="word-break:break-all;color:#5a76c8;">{{ $resetUrl }}</span></p>
            </div>
        </div>
    </div>
</body>
</html>
