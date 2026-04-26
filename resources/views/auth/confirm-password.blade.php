@extends('layouts.guest')

@section('title', 'Konfirmasi Keamanan - Wiboost Store')

@section('content')
    <div class="mx-auto w-full max-w-md">
        <div class="mb-4">
            <p class="inline-flex rounded-full border-2 border-white bg-[#f4f9ff] px-4 py-1.5 text-[11px] font-black uppercase tracking-[0.3em] text-[#8faaf3] shadow-sm">Keamanan</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-[#2b3a67] sm:text-3xl">Konfirmasi Kata Sandi</h2>
            <p class="mt-1 text-xs font-bold text-slate-500 sm:text-sm">Masukkan kata sandi kamu lagi untuk melanjutkan ke area yang lebih sensitif.</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-3">
            @csrf

            <div>
                <label for="password" class="mb-1 block text-sm font-black text-[#5a76c8]">Kata Sandi</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="w-full rounded-[1.25rem] border-2 border-[#e0ebff] bg-[#f8fbff] px-4 py-3 text-sm font-bold text-[#2b3a67] outline-none transition focus:border-[#5a76c8] focus:bg-white"
                    placeholder="Masukkan kata sandi">
                @error('password')
                    <p class="mt-1 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-[1.35rem] border-2 border-white bg-[#5a76c8] px-5 py-3 text-base font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                Konfirmasi dan Lanjutkan
            </button>
        </form>
    </div>
@endsection
