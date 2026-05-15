@extends('layouts.guest')

@section('title', 'Verifikasi Email - Wiboost Store')

@section('content')
    <div class="mx-auto w-full max-w-md text-center sm:text-left">
        <div class="mb-8">
            <div class="inline-block rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-xs font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                Verifikasi
            </div>
            <h2 class="text-3xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm sm:text-4xl">
                Cek <span class="bg-gradient-to-r from-[#5a76c8] to-[#9a8ce5] bg-clip-text text-transparent">Emailmu!</span>
            </h2>
            <p class="mt-2 text-sm font-bold text-[#8faaf3]">Kami sudah mengirim tautan verifikasi ke email terdaftar. Klik tautan itu untuk mengaktifkan akunmu.</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 rounded-[1.5rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-left text-sm font-bold text-emerald-600 shadow-lg shadow-emerald-100">
                Tautan verifikasi baru sudah dikirim ke email kamu!
            </div>
        @endif

        <div class="space-y-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] px-5 py-4 text-base font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform hover:bg-[#4760a9] active:scale-95">
                    Kirim Ulang Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] px-5 py-4 text-base font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 transition-transform hover:bg-[#ffcccc] active:scale-95">
                    Keluar Akun
                </button>
            </form>
        </div>
    </div>
@endsection
