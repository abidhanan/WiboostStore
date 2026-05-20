<section>
    <header class="mb-6 text-left">
        <h3 class="text-xl font-black text-[#2b3a67] md:text-2xl">Informasi Profil</h3>
        <p class="mt-1 text-sm font-bold text-[#8faaf3]">Perbarui informasi profil dan alamat email akunmu.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Nama Lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name"
                   :readonly="! editingProfile"
                   @click="editingProfile = true"
                   @focus="editingProfile = true"
                   class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]">
            <x-input-error class="mt-2 pl-2 text-xs font-bold text-[#ff6b6b]" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Alamat Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                   :readonly="! editingProfile"
                   @click="editingProfile = true"
                   @focus="editingProfile = true"
                   class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]">
            <x-input-error class="mt-2 pl-2 text-xs font-bold text-[#ff6b6b]" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-4 rounded-[1.5rem] border-4 border-white bg-[#fff5eb] p-5 shadow-sm">
                    <p class="text-sm font-bold text-amber-600">
                        Email kamu belum diverifikasi.
                        <button form="send-verification" class="ml-1 font-black text-amber-500 underline decoration-2 underline-offset-4 transition-colors hover:text-amber-700">
                            Kirim ulang verifikasi.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-3 inline-block rounded-md border border-white bg-[#e6fff7] px-3 py-1 text-xs font-black uppercase tracking-widest text-emerald-500 shadow-sm">
                            Link verifikasi baru telah dikirim.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <label for="whatsapp" class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Nomor WhatsApp</label>
            <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $user->whatsapp) }}" required
                   :readonly="! editingProfile"
                   @click="editingProfile = true"
                   @focus="editingProfile = true"
                   class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]"
                   placeholder="081234567890">
            <x-input-error class="mt-2 pl-2 text-xs font-bold text-[#ff6b6b]" :messages="$errors->get('whatsapp')" />
        </div>

        <div class="flex flex-col items-center justify-center gap-3 pt-2 sm:flex-row">
            <button x-show="editingProfile" x-cloak type="submit" class="rounded-full border-4 border-white bg-[#5a76c8] px-8 py-4 font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9]">
                Simpan Profil
            </button>
            <button x-show="editingProfile" x-cloak type="button" @click="editingProfile = false; $el.closest('form').reset()" class="rounded-full border-4 border-white bg-[#f4f9ff] px-8 py-4 font-black text-[#5a76c8] shadow-sm transition-transform active:scale-95">
                Batal
            </button>
        </div>

        @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="text-center text-sm font-black text-emerald-500 bg-[#e6fff7] px-5 py-3 rounded-full border-2 border-white shadow-sm">
                Profil berhasil disimpan.
            </p>
        @endif
    </form>
</section>
