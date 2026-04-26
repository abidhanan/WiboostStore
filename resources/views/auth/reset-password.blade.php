@extends('layouts.guest')

@section('title', 'Sandi Baru - Wiboost Store')

@section('content')
    <div class="mx-auto w-full max-w-md">
        <div class="mb-4">
            <p class="inline-flex rounded-full border-2 border-white bg-[#f4f9ff] px-4 py-1.5 text-[11px] font-black uppercase tracking-[0.3em] text-[#8faaf3] shadow-sm">Sandi Baru</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-[#2b3a67] sm:text-3xl">Atur Ulang Kata Sandi</h2>
            <p class="mt-1 text-xs font-bold text-slate-500 sm:text-sm">Gunakan kata sandi baru yang aman agar akun bisa dipakai lagi seperti biasa.</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="mb-1 block text-sm font-black text-[#5a76c8]">Alamat Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="username"
                    class="w-full rounded-[1.25rem] border-2 border-[#e0ebff] bg-[#f8fbff] px-4 py-3 text-sm font-bold text-[#2b3a67] outline-none transition focus:border-[#5a76c8] focus:bg-white"
                    placeholder="nama@email.com">
                @error('email')
                    <p class="mt-1 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-black text-[#5a76c8]">Sandi Baru</label>
                <input id="password" type="password" name="password" required autofocus autocomplete="new-password"
                    class="w-full rounded-[1.25rem] border-2 border-[#e0ebff] bg-[#f8fbff] px-4 py-3 text-sm font-bold text-[#2b3a67] outline-none transition focus:border-[#5a76c8] focus:bg-white"
                    placeholder="Masukkan sandi baru">
                @error('password')
                    <p class="mt-1 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-black text-[#5a76c8]">Konfirmasi Sandi Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                    class="w-full rounded-[1.25rem] border-2 border-[#e0ebff] bg-[#f8fbff] px-4 py-3 text-sm font-bold text-[#2b3a67] outline-none transition focus:border-[#5a76c8] focus:bg-white"
                    placeholder="Ulangi sandi baru">
            </div>

            <button type="submit" class="w-full rounded-[1.35rem] border-2 border-white bg-[#5a76c8] px-5 py-3 text-base font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                Simpan Sandi Baru
            </button>
        </form>
    </div>
@endsection
