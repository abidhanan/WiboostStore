<section>
    <header class="mb-6">
        <h3 class="text-xl md:text-2xl font-black text-[#2b3a67] flex items-center gap-2">
            <span class="text-3xl drop-shadow-sm">👤</span> Informasi Profil
        </h3>
        <p class="mt-1 text-sm font-bold text-[#8faaf3] ml-1">Perbarui informasi profil dan alamat email akunmu.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-sm font-black text-[#5a76c8] mb-2 ml-2">Nama Lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                   class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition shadow-inner placeholder-[#a3bbfb]">
            <x-input-error class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="block text-sm font-black text-[#5a76c8] mb-2 ml-2">Alamat Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                   class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition shadow-inner placeholder-[#a3bbfb]">
            <x-input-error class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-4 bg-[#fff5eb] border-4 border-white p-5 rounded-[1.5rem] shadow-sm">
                    <p class="text-sm font-bold text-amber-600">
                        Email kamu belum diverifikasi.
                        <button form="send-verification" class="underline decoration-2 underline-offset-4 text-amber-500 hover:text-amber-700 font-black ml-1 transition-colors">
                            Klik di sini untuk mengirim ulang.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-3 font-black text-xs text-emerald-500 uppercase tracking-widest bg-[#e6fff7] inline-block px-3 py-1 rounded-md border border-white shadow-sm">
                            Link verifikasi baru telah dikirim! 📨
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <label for="whatsapp" class="block text-sm font-black text-[#5a76c8] mb-2 ml-2">Nomor WhatsApp</label>
            <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $user->whatsapp) }}" required
                   class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition shadow-inner placeholder-[#a3bbfb]"
                   placeholder="Contoh: 081234567890">
            <x-input-error class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2" :messages="$errors->get('whatsapp')" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black px-8 py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex items-center gap-2">
                Simpan Profil ✨
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="text-sm font-black text-emerald-500 bg-[#e6fff7] px-5 py-2.5 rounded-full border-2 border-white shadow-sm">
                    Tersimpan! ✅
                </p>
            @endif
        </div>
    </form>
</section>