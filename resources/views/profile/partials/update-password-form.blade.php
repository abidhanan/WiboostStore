<section>
    <header class="mb-6 text-left">
        <h3 class="text-xl font-black text-[#2b3a67] md:text-2xl">Ubah Kata Sandi</h3>
        <p class="mt-1 text-sm font-bold text-[#8faaf3]">Pastikan akun menggunakan kata sandi yang panjang dan acak agar tetap aman.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6"
          x-data="{ currentPassword: '', newPassword: '', confirmPassword: '', passwordChanged() { return this.currentPassword || this.newPassword || this.confirmPassword; } }">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Sandi Saat Ini</label>
            <input x-model="currentPassword" id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   :readonly="! editingPassword"
                   @click="editingPassword = true"
                   @focus="editingPassword = true"
                   class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]" placeholder="********">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 pl-2 text-xs font-bold text-[#ff6b6b]" />
        </div>

        <div>
            <label for="update_password_password" class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Sandi Baru</label>
            <input x-model="newPassword" id="update_password_password" name="password" type="password" autocomplete="new-password"
                   :readonly="! editingPassword"
                   @click="editingPassword = true"
                   @focus="editingPassword = true"
                   class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]" placeholder="Minimal 8 karakter">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 pl-2 text-xs font-bold text-[#ff6b6b]" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Konfirmasi Sandi Baru</label>
            <input x-model="confirmPassword" id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   :readonly="! editingPassword"
                   @click="editingPassword = true"
                   @focus="editingPassword = true"
                   class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]" placeholder="Ketik ulang sandi baru">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 pl-2 text-xs font-bold text-[#ff6b6b]" />
        </div>

        <div class="grid grid-cols-2 items-center gap-3 pt-2 sm:flex sm:justify-start">
            <button x-show="passwordChanged()" x-cloak type="submit" class="min-w-0 rounded-full border-4 border-white bg-[#4bc6b9] px-4 py-4 font-black text-white shadow-xl shadow-[#4bc6b9]/30 transition-transform active:scale-95 hover:bg-[#3ba398] sm:px-8">
                Perbarui
            </button>
            <button x-show="passwordChanged()" x-cloak type="button" @click="editingPassword = false; currentPassword = ''; newPassword = ''; confirmPassword = ''; $el.closest('form').reset()" class="min-w-0 rounded-full border-4 border-white bg-[#f4f9ff] px-4 py-4 font-black text-[#5a76c8] shadow-sm transition-transform active:scale-95 sm:px-8">
                Batal
            </button>
        </div>

        @if (session('status') === 'password-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="text-center text-sm font-black text-emerald-500 bg-[#e6fff7] px-5 py-3 rounded-full border-2 border-white shadow-sm">
                Sandi berhasil diubah.
            </p>
        @endif
    </form>
</section>
