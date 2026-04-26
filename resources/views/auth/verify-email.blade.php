@extends('layouts.guest')

@section('title', 'Verifikasi Email - Wiboost Store')

@section('content')
    <div class="mx-auto w-full max-w-md">
        <div class="mb-4">
            <p class="inline-flex rounded-full border-2 border-white bg-[#f4f9ff] px-4 py-1.5 text-[11px] font-black uppercase tracking-[0.3em] text-[#8faaf3] shadow-sm">Verifikasi</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-[#2b3a67] sm:text-3xl">Cek email kamu</h2>
            <p class="mt-1 text-xs font-bold text-slate-500 sm:text-sm">Kami sudah mengirim tautan verifikasi ke email terdaftar. Klik tautan itu untuk mengaktifkan akunmu.</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-3 rounded-[1.4rem] border-2 border-white bg-[#e6fff7] px-4 py-3 text-sm font-bold text-emerald-600 shadow-sm">
                Link verifikasi baru sudah dikirim ke email kamu.
            </div>
        @endif

        <div class="space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full rounded-[1.35rem] border-2 border-white bg-[#5a76c8] px-5 py-3 text-base font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                    Kirim Ulang Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-[1.35rem] border-2 border-white bg-[#ffe5e5] px-5 py-3 text-base font-black text-[#ff6b6b] shadow-sm transition hover:bg-[#ffd6d6]">
                    Keluar Akun
                </button>
            </form>
        </div>
    </div>
@endsection
