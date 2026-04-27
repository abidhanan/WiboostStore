<section class="space-y-6">
    <header class="mb-6">
        <h3 class="text-xl md:text-2xl font-black text-[#ff6b6b] flex items-center gap-2">
            <span class="text-3xl drop-shadow-sm">⚠️</span> Zona Berbahaya: Hapus Akun
        </h3>
        <p class="text-sm font-bold text-[#ff9999] mt-2 ml-1">Sekali akun dihapus, semua data riwayat pesanan dan sisa saldo <span class="font-black underline">tidak dapat dikembalikan</span>.</p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-[#ff6b6b] hover:bg-[#e55050] text-white font-black px-8 py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#ff6b6b]/30 border-4 border-white flex items-center gap-2"
    >
        Hapus Akun Permanen 🗑️
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-8 md:p-10 bg-white/95 backdrop-blur-sm rounded-[2.5rem] border-4 border-white shadow-2xl relative overflow-hidden">
            @csrf
            @method('delete')

            <div class="absolute -right-6 -bottom-6 text-9xl opacity-10 pointer-events-none">💔</div>

            <div class="relative z-10">
                <h2 class="text-2xl md:text-3xl font-black text-[#2b3a67] mb-2 tracking-tight">
                    Yakin ingin menghapus akun?
                </h2>

                <p class="text-sm font-bold text-[#8faaf3] mb-8 bg-[#f4f9ff] p-4 rounded-2xl border-2 border-white shadow-inner">
                    Masukkan kata sandi kamu untuk mengonfirmasi penghapusan ini. Semua data akan hilang selamanya.
                </p>

                <div class="mb-8">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#ff6b6b] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition shadow-inner placeholder-[#a3bbfb]"
                        placeholder="Ketik sandi kamu untuk konfirmasi..."
                    />
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2" />
                </div>

                <div class="flex flex-col-reverse md:flex-row justify-end gap-4 pt-2">
                    <button type="button" x-on:click="$dispatch('close')" class="bg-white border-4 border-[#e0fbfc] text-[#8faaf3] hover:bg-[#f4f9ff] font-black px-8 py-4 rounded-full transition-colors w-full md:w-auto">
                        Batal
                    </button>

                    <button type="submit" class="bg-[#ff6b6b] hover:bg-[#e55050] text-white font-black px-8 py-4 rounded-full transition-transform active:scale-95 shadow-lg shadow-[#ff6b6b]/30 border-4 border-white w-full md:w-auto flex justify-center items-center gap-2">
                        Ya, Hapus Akun 💥
                    </button>
                </div>
            </div>
        </form>
    </x-modal>
</section>