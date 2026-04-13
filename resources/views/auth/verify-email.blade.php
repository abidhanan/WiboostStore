@extends('layouts.guest')

@section('title', 'Verifikasi Email - Wiboost Store')
@section('hero_badge', 'Aktivasi akun')
@section('hero_title', 'Verifikasi email untuk mulai transaksi dengan akun yang sudah aktif.')
@section('hero_copy', 'Kami pertahankan alur verifikasi yang jelas supaya pengguna baru langsung paham langkah berikutnya, baik di desktop maupun mobile.')

@section('content')
    <div class="mx-auto w-full max-w-md">
        <div class="mb-8">
            <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Verifikasi</p>
            <h2 class="mt-2 text-3xl font-black tracking-tight text-[#2b3a67]">Cek email kamu</h2>
            <p class="mt-2 text-sm font-bold text-slate-500">Kami sudah mengirim tautan verifikasi ke email terdaftar. Klik tautan itu untuk mengaktifkan akunmu.</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 rounded-[1.75rem] border-2 border-white bg-[#e6fff7] px-5 py-4 text-sm font-bold text-emerald-600 shadow-sm">
                Link verifikasi baru sudah dikirim ke email kamu.
            </div>
        @endif

        <div class="space-y-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full rounded-[1.5rem] border-2 border-white bg-[#5a76c8] px-5 py-4 text-base font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                    Kirim Ulang Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-[1.5rem] border-2 border-white bg-[#ffe5e5] px-5 py-4 text-base font-black text-[#ff6b6b] shadow-sm transition hover:bg-[#ffd6d6]">
                    Keluar Akun
                </button>
            </form>
        </div>
    </div>
@endsection
