@extends('layouts.guest')

@section('title', 'Konfirmasi Keamanan - Wiboost Store')
@section('hero_badge', 'Akses aman')
@section('hero_title', 'Konfirmasi kata sandi sebelum membuka area sensitif di dashboard.')
@section('hero_copy', 'Lapisan konfirmasi ini membantu menjaga tindakan penting tetap aman tanpa memecah alur visual guest.')

@section('content')
    <div class="mx-auto w-full max-w-md">
        <div class="mb-8">
            <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Keamanan</p>
            <h2 class="mt-2 text-3xl font-black tracking-tight text-[#2b3a67]">Konfirmasi kata sandi</h2>
            <p class="mt-2 text-sm font-bold text-slate-500">Masukkan kata sandi kamu lagi untuk melanjutkan ke area yang lebih sensitif.</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf

            <div>
                <label for="password" class="mb-2 block text-sm font-black text-[#5a76c8]">Kata Sandi</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="w-full rounded-[1.4rem] border-2 border-[#e0ebff] bg-[#f8fbff] px-5 py-4 text-sm font-bold text-[#2b3a67] outline-none transition focus:border-[#5a76c8] focus:bg-white"
                    placeholder="Masukkan kata sandi">
                @error('password')
                    <p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-[1.5rem] border-2 border-white bg-[#5a76c8] px-5 py-4 text-base font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                Konfirmasi dan Lanjutkan
            </button>
        </form>
    </div>
@endsection
