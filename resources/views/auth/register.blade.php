@extends('layouts.guest')

@section('title', 'Daftar - Wiboost Store')
@section('hero_badge', 'Registrasi baru')
@section('hero_title', 'Buat akun baru untuk mulai checkout, simpan saldo, dan pantau order lebih cepat.')
@section('hero_copy', 'Flow daftar sekarang memakai shell guest yang sama, jadi pengalaman mobile dan desktop lebih konsisten sejak langkah pertama.')

@section('content')
    <div class="mx-auto w-full max-w-md">
        <div class="mb-8">
            <p class="text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Daftar</p>
            <h2 class="mt-2 text-3xl font-black tracking-tight text-[#2b3a67]">Buat akun Wiboost</h2>
            <p class="mt-2 text-sm font-bold text-slate-500">Gabung sekarang untuk akses top up, akun premium, layanan manual, dan promo terbaru.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="mb-2 block text-sm font-black text-[#5a76c8]">Nama Panggilan</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full rounded-[1.4rem] border-2 border-[#e0ebff] bg-[#f8fbff] px-5 py-4 text-sm font-bold text-[#2b3a67] outline-none transition focus:border-[#5a76c8] focus:bg-white"
                    placeholder="Ketik namamu">
                @error('name')
                    <p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="mb-2 block text-sm font-black text-[#5a76c8]">Alamat Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    class="w-full rounded-[1.4rem] border-2 border-[#e0ebff] bg-[#f8fbff] px-5 py-4 text-sm font-bold text-[#2b3a67] outline-none transition focus:border-[#5a76c8] focus:bg-white"
                    placeholder="nama@email.com">
                @error('email')
                    <p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="whatsapp" class="mb-2 block text-sm font-black text-[#5a76c8]">Nomor WhatsApp</label>
                <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp') }}" required
                    class="w-full rounded-[1.4rem] border-2 border-[#e0ebff] bg-[#f8fbff] px-5 py-4 text-sm font-bold text-[#2b3a67] outline-none transition focus:border-[#5a76c8] focus:bg-white"
                    placeholder="Contoh: 081234567890">
                @error('whatsapp')
                    <p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="mb-2 block text-sm font-black text-[#5a76c8]">Kata Sandi</label>
                    <input id="password" type="password" name="password" required
                        class="w-full rounded-[1.4rem] border-2 border-[#e0ebff] bg-[#f8fbff] px-5 py-4 text-sm font-bold text-[#2b3a67] outline-none transition focus:border-[#5a76c8] focus:bg-white"
                        placeholder="Minimal 8 karakter">
                    @error('password')
                        <p class="mt-2 text-xs font-bold text-[#ff6b6b]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-black text-[#5a76c8]">Konfirmasi Sandi</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        class="w-full rounded-[1.4rem] border-2 border-[#e0ebff] bg-[#f8fbff] px-5 py-4 text-sm font-bold text-[#2b3a67] outline-none transition focus:border-[#5a76c8] focus:bg-white"
                        placeholder="Ulangi sandi">
                </div>
            </div>

            <button type="submit" class="w-full rounded-[1.5rem] border-2 border-white bg-[#5a76c8] px-5 py-4 text-base font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                Daftar Sekarang
            </button>
        </form>

        <p class="mt-6 text-center text-sm font-bold text-slate-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-[#5a76c8] transition hover:text-[#4760a9]">Masuk di sini</a>
        </p>
    </div>
@endsection
