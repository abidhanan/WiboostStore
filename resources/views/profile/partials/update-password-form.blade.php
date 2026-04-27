<section>
    <header class="mb-6">
        <h3 class="text-xl md:text-2xl font-black text-[#2b3a67] flex items-center gap-2">
            <span class="text-3xl drop-shadow-sm">🔒</span> Ubah Kata Sandi
        </h3>
        <p class="mt-1 text-sm font-bold text-[#8faaf3] ml-1">Pastikan akun kamu menggunakan kata sandi yang panjang dan acak agar tetap aman.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-black text-[#5a76c8] mb-2 ml-2">Sandi Saat Ini</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition shadow-inner placeholder-[#a3bbfb]" placeholder="••••••••">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2" />
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-black text-[#5a76c8] mb-2 ml-2">Sandi Baru</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                   class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition shadow-inner placeholder-[#a3bbfb]" placeholder="Minimal 8 Karakter">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-black text-[#5a76c8] mb-2 ml-2">Konfirmasi Sandi Baru</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition shadow-inner placeholder-[#a3bbfb]" placeholder="Ketik ulang sandi baru">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black px-8 py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#4bc6b9]/30 border-4 border-white flex items-center gap-2">
                Perbarui Sandi 🚀
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="text-sm font-black text-emerald-500 bg-[#e6fff7] px-5 py-2.5 rounded-full border-2 border-white shadow-sm">
                    Sandi Diubah! ✅
                </p>
            @endif
        </div>
    </form>
</section>