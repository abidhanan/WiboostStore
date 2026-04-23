@php
    $currentProduct = $product ?? null;
    $currentType = old('process_type', $currentProduct->process_type ?? 'manual');
    $currentProvider = old('provider_source', $currentProduct->provider_source ?? $currentProduct->provider_id ?? '');
    $requiresBuyerEmail = old('requires_buyer_email', $currentProduct->requires_buyer_email ?? false);
@endphp

@if ($errors->any())
    <div class="mb-8 flex items-start gap-4 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-sm">
        <span class="mt-1 text-2xl">!</span>
        <div>
            <p class="mb-2">Masih ada data yang perlu diperbaiki:</p>
            <ul class="list-disc list-inside text-sm font-bold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="rounded-[2rem] border-4 border-white bg-white p-6 shadow-lg shadow-[#bde0fe]/20 md:p-8">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-8">
        <section class="space-y-6">
            <div>
                <p class="mb-1 text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Info Utama</p>
                <h4 class="text-2xl font-black text-[#2b3a67]">Identitas Produk</h4>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Nama Produk / Layanan</label>
                    <input type="text" name="name" required value="{{ old('name', $currentProduct->name ?? '') }}"
                        class="w-full rounded-[1.5rem] border-2 border-transparent bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]"
                        placeholder="Contoh: 1000 Followers Instagram HQ / Netflix Premium 1 Bulan">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Deskripsi Layanan</label>
                    <textarea name="description" rows="5"
                        class="w-full rounded-[1.5rem] border-2 border-transparent bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]"
                        placeholder="Jelaskan detail layanan, ketentuan, manfaat, atau instruksi penting yang perlu dibaca pembeli.">{{ old('description', $currentProduct->description ?? '') }}</textarea>
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Kategori</label>
                    <select name="category_id" required class="w-full appearance-none rounded-[1.5rem] border-2 border-transparent bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]">
                        <option value="" disabled {{ old('category_id', $currentProduct->category_id ?? '') === '' ? 'selected' : '' }}>-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('category_id', $currentProduct->category_id ?? '') === (string) $category->id ? 'selected' : '' }}>
                                {{ collect([$category->parent?->parent?->name, $category->parent?->name, $category->name])->filter()->implode(' / ') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Tipe Proses Order</label>
                    <select name="process_type" id="process_type" required class="w-full appearance-none rounded-[1.5rem] border-2 border-transparent bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]">
                        <option value="api" {{ $currentType === 'api' ? 'selected' : '' }}>API Otomatis</option>
                        <option value="account" {{ $currentType === 'account' ? 'selected' : '' }}>Akun Premium</option>
                        <option value="number" {{ $currentType === 'number' ? 'selected' : '' }}>Nomor OTP / Nomor Luar</option>
                        <option value="manual" {{ $currentType === 'manual' ? 'selected' : '' }}>Manual Admin</option>
                    </select>
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Harga Jual</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-6 font-black text-[#5a76c8]">Rp</span>
                        <input type="number" name="price" min="0" required value="{{ old('price', $currentProduct->price ?? '') }}"
                            class="w-full rounded-[1.5rem] border-2 border-transparent bg-[#f4f9ff] py-4 pl-14 pr-6 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]"
                            placeholder="15000">
                    </div>
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Status Layanan</label>
                    <select name="is_active" required class="w-full appearance-none rounded-[1.5rem] border-2 border-transparent bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]">
                        <option value="1" {{ (string) old('is_active', $currentProduct->is_active ?? '1') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ (string) old('is_active', $currentProduct->is_active ?? '1') === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>
        </section>

        <section id="provider_section" class="space-y-6 rounded-[2rem] border-2 border-white bg-[#f8fbff] p-5 shadow-inner md:p-6">
            <div>
                <p class="mb-1 text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Integrasi API</p>
                <h4 class="text-2xl font-black text-[#2b3a67]">Provider Otomatis</h4>
                <p class="mt-2 max-w-2xl text-sm font-bold text-[#6b84bf]">Gunakan bagian ini hanya untuk produk yang diproses otomatis ke provider seperti Digiflazz atau OrderSosmed.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Sumber Provider</label>
                    <select name="provider_source" id="provider_source" class="w-full appearance-none rounded-[1.5rem] border-2 border-transparent bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]">
                        <option value="">-- Pilih Provider --</option>
                        <option value="ordersosmed" {{ $currentProvider === 'ordersosmed' ? 'selected' : '' }}>OrderSosmed</option>
                        <option value="digiflazz" {{ $currentProvider === 'digiflazz' ? 'selected' : '' }}>Digiflazz</option>
                    </select>
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Kode Layanan Provider</label>
                    <input type="text" name="provider_product_id" value="{{ old('provider_product_id', $currentProduct->provider_product_id ?? '') }}"
                        class="w-full rounded-[1.5rem] border-2 border-transparent bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]"
                        placeholder="SKU Digiflazz atau service ID OrderSosmed">
                </div>

                <div id="provider_quantity_group">
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Quantity Provider</label>
                    <input type="number" name="provider_quantity" min="1" value="{{ old('provider_quantity', $currentProduct->provider_quantity ?? 1) }}"
                        class="w-full rounded-[1.5rem] border-2 border-transparent bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]"
                        placeholder="1000">
                    <p class="mt-2 ml-2 text-[11px] font-bold text-[#8faaf3]">Untuk OrderSosmed, isi jumlah order yang akan dikirim ke provider.</p>
                </div>
            </div>
        </section>

        <section id="target_section" class="space-y-6 rounded-[2rem] border-2 border-white bg-[#fffdf7] p-5 shadow-inner md:p-6">
            <div>
                <p class="mb-1 text-xs font-black uppercase tracking-[0.3em] text-[#d7a431]">Checkout User</p>
                <h4 class="text-2xl font-black text-[#2b3a67]">Instruksi Input Target</h4>
                <p class="mt-2 max-w-2xl text-sm font-bold text-[#8d7b54]">Bagian ini mengatur label, placeholder, dan petunjuk yang dilihat pelanggan saat checkout.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Label Field</label>
                    <input type="text" name="target_label" value="{{ old('target_label', $currentProduct->target_label ?? '') }}"
                        class="w-full rounded-[1.5rem] border-2 border-transparent bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]"
                        placeholder="Contoh: User ID & Zone ID">
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Placeholder Field</label>
                    <input type="text" name="target_placeholder" value="{{ old('target_placeholder', $currentProduct->target_placeholder ?? '') }}"
                        class="w-full rounded-[1.5rem] border-2 border-transparent bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]"
                        placeholder="Contoh: 12345678 (1234)">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Petunjuk Tambahan</label>
                    <textarea name="target_hint" rows="4"
                        class="w-full rounded-[1.5rem] border-2 border-transparent bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]"
                        placeholder="Contoh: Pastikan ID dan zone sudah benar agar pesanan tidak gagal.">{{ old('target_hint', $currentProduct->target_hint ?? '') }}</textarea>
                </div>
            </div>
        </section>

        <section id="inventory_section" class="space-y-6 rounded-[2rem] border-2 border-white bg-[#f6fffb] p-5 shadow-inner md:p-6">
            <div>
                <p class="mb-1 text-xs font-black uppercase tracking-[0.3em] text-emerald-500">Stok</p>
                <h4 class="text-2xl font-black text-[#2b3a67]">Pengingat Inventory</h4>
                <p class="mt-2 max-w-2xl text-sm font-bold text-emerald-600/80">Khusus untuk produk akun premium atau nomor OTP yang stoknya berasal dari gudang kredensial.</p>
            </div>

            <div class="max-w-md">
                <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Batas Minimal Stok</label>
                <input type="number" name="stock_reminder" min="0" value="{{ old('stock_reminder', $currentProduct->stock_reminder ?? 0) }}"
                    class="w-full rounded-[1.5rem] border-2 border-transparent bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]"
                    placeholder="Contoh: 5">
            </div>
        </section>

        <section id="premium_checkout_section" class="space-y-6 rounded-[2rem] border-2 border-white bg-[#fff7fb] p-5 shadow-inner md:p-6">
            <div>
                <p class="mb-1 text-xs font-black uppercase tracking-[0.3em] text-pink-500">Aplikasi Premium</p>
                <h4 class="text-2xl font-black text-[#2b3a67]">Kebutuhan Input Buyer</h4>
                <p class="mt-2 max-w-2xl text-sm font-bold text-[#a35b85]">Aktifkan hanya jika produk premium ini memang perlu buyer memasukkan email khusus aplikasi saat checkout.</p>
            </div>

            <label class="flex items-start gap-4 rounded-[1.5rem] border-2 border-white bg-white px-5 py-4 shadow-sm">
                <input type="hidden" name="requires_buyer_email" value="0">
                <input type="checkbox" name="requires_buyer_email" value="1" {{ (string) $requiresBuyerEmail === '1' || $requiresBuyerEmail === true ? 'checked' : '' }}
                    class="mt-1 h-5 w-5 rounded border-pink-200 text-pink-500 focus:ring-pink-400">
                <div>
                    <p class="font-black text-[#2b3a67]">Buyer wajib mengisi email khusus aplikasi</p>
                    <p class="mt-1 text-sm font-bold text-[#8faaf3]">Kalau dimatikan, checkout produk ini langsung lanjut tanpa field email tambahan.</p>
                </div>
            </label>
        </section>

        <section class="space-y-6">
            <div>
                <p class="mb-1 text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Branding</p>
                <h4 class="text-2xl font-black text-[#2b3a67]">Ikon Produk</h4>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Emoji Produk</label>
                    <input type="text" name="emote" value="{{ old('emote', $currentProduct->emote ?? '') }}"
                        class="w-full rounded-[1.5rem] border-2 border-transparent bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition focus:border-[#5a76c8]"
                        placeholder="Contoh: 🎮 atau 📱">
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#8faaf3]">Upload Gambar</label>
                    @if($currentProduct?->image)
                        <div class="mb-3 flex items-center justify-between rounded-2xl border border-white bg-white/80 p-3 shadow-sm">
                            <div class="flex items-center gap-3">
                                <img src="{{ Storage::url($currentProduct->image) }}" class="h-12 w-12 rounded-xl object-cover">
                                <span class="text-xs font-black uppercase tracking-widest text-[#5a76c8]">Gambar aktif</span>
                            </div>
                            <label class="flex items-center gap-2 text-xs font-black text-rose-500">
                                <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300 text-rose-500 focus:ring-rose-500">
                                Hapus
                            </label>
                        </div>
                    @endif
                    <input type="file" name="image" accept="image/*"
                        class="w-full cursor-pointer rounded-[1.5rem] border-2 border-dashed border-[#bde0fe] bg-white px-4 py-3 font-black text-[#2b3a67] transition file:mr-4 file:rounded-lg file:border-0 file:bg-[#5a76c8] file:px-4 file:py-2 file:text-xs file:font-black file:text-white hover:border-[#5a76c8] hover:file:bg-[#4760a9]">
                </div>
            </div>
        </section>

        <div class="flex flex-col gap-4 pt-4 sm:flex-row">
            <button type="submit" class="flex-1 rounded-[1.5rem] border-2 border-white bg-[#5a76c8] py-4 text-lg font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9] active:scale-95">
                {{ $submitLabel }}
            </button>
            <a href="{{ route('admin.products.index') }}" class="rounded-[1.5rem] border-4 border-[#f0f5ff] bg-white px-8 py-4 text-center text-lg font-black text-[#8faaf3] transition hover:bg-[#f4f9ff]">
                Batal
            </a>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const processTypeSelect = document.getElementById('process_type');
        const categorySelect = document.querySelector('select[name="category_id"]');
        const providerSourceSelect = document.getElementById('provider_source');
        const providerSection = document.getElementById('provider_section');
        const providerQuantityGroup = document.getElementById('provider_quantity_group');
        const targetSection = document.getElementById('target_section');
        const inventorySection = document.getElementById('inventory_section');
        const premiumCheckoutSection = document.getElementById('premium_checkout_section');

        function syncSections() {
            const type = processTypeSelect.value;
            const providerSource = providerSourceSelect.value;

            providerSection.style.display = type === 'api' ? 'block' : 'none';
            targetSection.style.display = (type === 'api' || type === 'manual') ? 'block' : 'none';
            inventorySection.style.display = (type === 'account' || type === 'number') ? 'block' : 'none';
            premiumCheckoutSection.style.display = type === 'account' ? 'block' : 'none';
            providerQuantityGroup.style.display = (type === 'api' && providerSource === 'ordersosmed') ? 'block' : 'none';
        }

        processTypeSelect.addEventListener('change', syncSections);
        providerSourceSelect.addEventListener('change', syncSections);
        categorySelect.addEventListener('change', syncSections);
        syncSections();
    });
</script>
