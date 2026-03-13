<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Email - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Nunito', sans-serif; background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); } </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <div class="w-full max-w-md relative z-10">
        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-2xl shadow-[#5a76c8]/20 border-4 border-white text-center">
            
            <div class="text-6xl mb-6">✉️</div>
            <h2 class="text-2xl font-black text-[#2b3a67] mb-2">Cek Email Kamu!</h2>
            <p class="text-[#8faaf3] font-bold text-sm mb-6">
                Terima kasih sudah mendaftar! Kami sudah mengirimkan tautan verifikasi ke email kamu. Silakan klik tautan tersebut untuk mulai bertransaksi.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 font-bold text-sm text-emerald-500 bg-[#e6fff7] p-4 rounded-[1.5rem] border-2 border-white">
                    Link verifikasi baru telah dikirim ke email kamu!
                </div>
            @endif

            <div class="flex flex-col gap-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-2 border-white">
                        Kirim Ulang Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-[#ffe5e5] hover:bg-[#ffcccc] text-[#ff6b6b] font-black py-4 rounded-[1.5rem] transition-colors border-2 border-white">
                        Keluar Akun
                    </button>
                </form>
            </div>

        </div>
    </div>
</body>
</html>