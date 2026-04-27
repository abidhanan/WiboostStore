@extends('layouts.guest')

@section('title', 'Verifikasi Email - Wiboost Store')

@section('content')
    <div class="mx-auto w-full max-w-md text-center sm:text-left">
        <div class="mb-8">
            <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-xs border-2 border-white shadow-sm uppercase tracking-widest">
                Verifikasi
            </div>
            <h2 class="text-3xl sm:text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm">
                Cek <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5]">Emailmu!</span> 📨
            </h2>
            <p class="mt-2 text-sm font-bold text-[#8faaf3]">Kami sudah mengirim tautan verifikasi ke email terdaftar. Klik tautan itu untuk mengaktifkan akunmu.</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 rounded-[1.5rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-bold text-emerald-600 shadow-lg shadow-emerald-100 text-left">
                Tautan verifikasi baru sudah dikirim ke email kamu!
            </div>
        @endif

        <div class="space-y-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full rounded-full border-4 border-white bg-[#5a76c8] px-5 py-4 text-base font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9] flex items-center justify-center gap-2">
                    Kirim Ulang Email 🚀
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-full border-4 border-white bg-[#ffe5e5] px-5 py-4 text-base font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc] flex items-center justify-center gap-2">
                    Keluar Akun
                </button>
            </form>
        </div>
    </div>
@endsection