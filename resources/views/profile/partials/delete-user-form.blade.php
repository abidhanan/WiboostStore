<section class="space-y-6">
    <header class="mb-6">
        <h3 class="text-xl font-black text-[#ff6b6b]">Zona Berbahaya: Hapus Akun ⚠️</h3>
        <p class="text-sm font-bold text-[#ff9999] mt-1">Sekali akun dihapus, semua data riwayat pesanan dan saldo tidak dapat dikembalikan.</p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-[#ff6b6b] hover:bg-[#e55050] text-white font-black px-8 py-3.5 rounded-full transition-transform active:scale-95 shadow-lg shadow-[#ff6b6b]/30 border-2 border-white"
    >
        Hapus Akun Permanen
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-8 bg-white rounded-[2.5rem] border-4 border-white shadow-2xl">
            @csrf
            @method('delete')

            <h2 class="text-2xl font-black text-[#2b3a67] mb-2">
                Yakin ingin menghapus akun? 🥺
            </h2>

            <p class="text-sm font-bold text-[#8faaf3] mb-6">
                Masukkan kata sandi kamu untuk mengonfirmasi penghapusan secara permanen.
            </p>

            <div class="mb-6">
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#ff6b6b] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]"
                    placeholder="Sandi kamu..."
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-[#ff6b6b] text-xs font-bold pl-2" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" x-on:click="$dispatch('close')" class="bg-white border-2 border-[#e0fbfc] text-[#8faaf3] hover:bg-[#f4f9ff] font-black px-6 py-3 rounded-[1.5rem] transition-colors">
                    Batal
                </button>

                <button type="submit" class="bg-[#ff6b6b] hover:bg-[#e55050] text-white font-black px-6 py-3 rounded-[1.5rem] transition-transform active:scale-95 shadow-md shadow-[#ff6b6b]/30 border-2 border-white">
                    Ya, Hapus Akun
                </button>
            </div>
        </form>
    </x-modal>
</section>