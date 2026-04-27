@php
    $currentProduct = $product ?? null;
    $currentType = old('process_type', $currentProduct->process_type ?? 'manual');
    $currentProvider = old('provider_source', $currentProduct->provider_source ?? $currentProduct->provider_id ?? '');
    $requiresBuyerEmail = old('requires_buyer_email', $currentProduct->requires_buyer_email ?? false);
@endphp

@if ($errors->any())
    <div class="mb-8 flex items-start gap-4 rounded-[2.5rem] border-4 border-white bg-[#ffe5e5] px-6 py-5 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20">
        <span class="mt-1 text-3xl drop-shadow-sm">⚠️</span>
        <div>
            <p class="mb-3 text-lg">Masih ada data yang perlu diperbaiki:</p>
            <ul class="list-disc list-inside text-sm font-bold bg-white/50 p-4 rounded-2xl border border-white">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm p-6 shadow-xl shadow-[#bde0fe]/30 md:p-10 relative overflow-hidden">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-8 relative z-10">
        <section class="space-y-6">
            <div>
                <p class="mb-1 text-xs font-black uppercase tracking-[0.3em] text-[#8faaf3]">Info Utama</p>
                <h4 class="text-2xl md:text-3xl font-black text-[#2b3a67] tracking-tight">Identitas Produk</h4>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Nama Produk / Layanan</label>
                    <input type="text" name="name" required value="{{ old('name', $currentProduct->name ?? '') }}"
                        class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] placeholder-[#a3bbfb]"
                        placeholder="Contoh: 1000 Followers Instagram HQ / Netflix Premium">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Deskripsi Layanan</label>
                    <textarea name="description" rows="5"
                        class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] placeholder-[#a3bbfb]"
                        placeholder="Jelaskan detail layanan, ketentuan, manfaat, atau instruksi penting yang perlu dibaca pembeli.">{{ old('description', $currentProduct->description ?? '') }}</textarea>
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Kategori</label>
                    <select name="category_id" required class="w-full appearance-none rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] cursor-pointer">
                        <option value="" disabled {{ old('category_id', $currentProduct->category_id ?? '') === '' ? 'selected' : '' }}>-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('category_id', $currentProduct->category_id ?? '') === (string) $category->id ? 'selected' : '' }}>
                                {{ collect([$category->parent?->parent?->name, $category->parent?->name, $category->name])->filter()->implode(' / ') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Tipe Proses Order</label>
                    <select name="process_type" id="process_type" required class="w-full appearance-none rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] cursor-pointer">
                        <option value="api" {{ $currentType === 'api' ? 'selected' : '' }}>API Otomatis</option>
                        <option value="account" {{ $currentType === 'account' ? 'selected' : '' }}>Akun Premium</option>
                        <option value="number" {{ $currentType === 'number' ? 'selected' : '' }}>Nomor OTP / Nomor Luar</option>
                        <option value="manual" {{ $currentType === 'manual' ? 'selected' : '' }}>Manual Admin</option>
                    </select>
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Harga Jual</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-6 font-black text-[#5a76c8]">Rp</span>
                        <input type="number" name="price" min="0" required value="{{ old('price', $currentProduct->price ?? '') }}"
                            class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-4 pl-14 pr-6 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] placeholder-[#a3bbfb]"
                            placeholder="15000">
                    </div>
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Status Layanan</label>
                    <select name="is_active" required class="w-full appearance-none rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] cursor-pointer">
                        <option value="1" {{ (string) old('is_active', $currentProduct->is_active ?? '1') === '1' ? 'selected' : '' }}>🟢 Aktif</option>
                        <option value="0" {{ (string) old('is_active', $currentProduct->is_active ?? '1') === '0' ? 'selected' : '' }}>🔴 Nonaktif</option>
                    </select>
                </div>
            </div>
        </section>

        <section id="provider_section" class="space-y-6 rounded-[2rem] border-4 border-white bg-[#f8fbff] p-6 shadow-md md:p-8">
            <div class="flex items-start gap-4">
                <div class="text-4xl drop-shadow-sm animate-bounce">🤖</div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Integrasi API</p>
                    <h4 class="text-2xl font-black text-[#2b3a67]">Provider Otomatis</h4>
                    <p class="mt-2 max-w-2xl text-sm font-bold text-[#6b84bf] bg-white/50 px-4 py-2 rounded-xl border border-white">Gunakan bagian ini hanya untuk produk yang diproses otomatis ke provider seperti Digiflazz atau OrderSosmed.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Sumber Provider</label>
                    <select name="provider_source" id="provider_source" class="w-full appearance-none rounded-[1.5rem] border-4 border-white bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] cursor-pointer">
                        <option value="">-- Pilih Provider --</option>
                        <option value="ordersosmed" {{ $currentProvider === 'ordersosmed' ? 'selected' : '' }}>OrderSosmed</option>
                        <option value="digiflazz" {{ $currentProvider === 'digiflazz' ? 'selected' : '' }}>Digiflazz</option>
                    </select>
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Kode Layanan Provider</label>
                    <input type="text" name="provider_product_id" value="{{ old('provider_product_id', $currentProduct->provider_product_id ?? '') }}"
                        class="w-full rounded-[1.5rem] border-4 border-white bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] placeholder-[#a3bbfb]"
                        placeholder="SKU Digiflazz / service ID OrderSosmed">
                </div>

                <div id="provider_quantity_group" class="md:col-span-2">
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Quantity Provider (Khusus Sosmed)</label>
                    <input type="number" name="provider_quantity" min="1" value="{{ old('provider_quantity', $currentProduct->provider_quantity ?? 1) }}"
                        class="w-full rounded-[1.5rem] border-4 border-white bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] placeholder-[#a3bbfb]"
                        placeholder="1000">
                    <p class="mt-3 ml-2 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] bg-white inline-block px-3 py-1 rounded-md border border-[#f0f5ff] shadow-sm">Isi jumlah order yang dikirim ke provider (cth: 1000 untuk Followers).</p>
                </div>
            </div>
        </section>

        <section id="target_section" class="space-y-6 rounded-[2rem] border-4 border-white bg-[#fffdf7] p-6 shadow-md md:p-8">
            <div class="flex items-start gap-4">
                <div class="text-4xl drop-shadow-sm">🎯</div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-[0.3em] text-amber-500">Checkout User</p>
                    <h4 class="text-2xl font-black text-[#2b3a67]">Instruksi Input Target</h4>
                    <p class="mt-2 max-w-2xl text-sm font-bold text-amber-700 bg-amber-50 px-4 py-2 rounded-xl border border-amber-100">Bagian ini mengatur label, placeholder, dan petunjuk yang dilihat pelanggan saat mengisi ID pesanan.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-amber-600">Label Field</label>
                    <input type="text" name="target_label" value="{{ old('target_label', $currentProduct->target_label ?? '') }}"
                        class="w-full rounded-[1.5rem] border-4 border-white bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-amber-200 placeholder-[#d1d5db]"
                        placeholder="Contoh: User ID & Zone ID">
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-amber-600">Placeholder Field</label>
                    <input type="text" name="target_placeholder" value="{{ old('target_placeholder', $currentProduct->target_placeholder ?? '') }}"
                        class="w-full rounded-[1.5rem] border-4 border-white bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-amber-200 placeholder-[#d1d5db]"
                        placeholder="Contoh: 12345678 (1234)">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-3 ml-2 block text-sm font-black text-amber-600">Petunjuk Tambahan</label>
                    <textarea name="target_hint" rows="3"
                        class="w-full rounded-[1.5rem] border-4 border-white bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-amber-200 placeholder-[#d1d5db]"
                        placeholder="Contoh: Pastikan ID dan zone sudah benar agar pesanan tidak gagal.">{{ old('target_hint', $currentProduct->target_hint ?? '') }}</textarea>
                </div>
            </div>
        </section>

        <section id="inventory_section" class="space-y-6 rounded-[2rem] border-4 border-white bg-[#f6fffb] p-6 shadow-md md:p-8">
            <div class="flex items-start gap-4">
                <div class="text-4xl drop-shadow-sm">🔔</div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-[0.3em] text-emerald-500">Stok</p>
                    <h4 class="text-2xl font-black text-[#2b3a67]">Pengingat Inventory</h4>
                    <p class="mt-2 max-w-2xl text-sm font-bold text-emerald-700 bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-100">Peringatan khusus untuk produk akun premium atau nomor yang stoknya terbatas di gudang kredensial.</p>
                </div>
            </div>

            <div class="max-w-md">
                <label class="mb-3 ml-2 block text-sm font-black text-emerald-600">Batas Minimal Stok</label>
                <input type="number" name="stock_reminder" min="0" value="{{ old('stock_reminder', $currentProduct->stock_reminder ?? 0) }}"
                    class="w-full rounded-[1.5rem] border-4 border-white bg-white px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-emerald-200 placeholder-[#d1d5db]"
                    placeholder="Contoh: 5">
            </div>
        </section>

        <section id="premium_checkout_section" class="space-y-6 rounded-[2rem] border-4 border-white bg-[#fff7fb] p-6 shadow-md md:p-8">
            <div class="flex items-start gap-4">
                <div class="text-4xl drop-shadow-sm">🔑</div>
                <div>
                    <p class="mb-1 text-[10px] font-black uppercase tracking-[0.3em] text-pink-500">Aplikasi Premium</p>
                    <h4 class="text-2xl font-black text-[#2b3a67]">Kebutuhan Input Buyer</h4>
                    <p class="mt-2 max-w-2xl text-sm font-bold text-pink-700 bg-pink-50 px-4 py-2 rounded-xl border border-pink-100">Aktifkan jika produk ini perlu buyer memasukkan email pribadinya saat checkout.</p>
                </div>
            </div>

            <label class="group flex cursor-pointer items-center gap-4 rounded-[1.5rem] border-4 border-white bg-white px-6 py-5 shadow-sm transition-transform hover:scale-[1.01]">
                <input type="hidden" name="requires_buyer_email" value="0">
                <input type="checkbox" name="requires_buyer_email" value="1" {{ (string) $requiresBuyerEmail === '1' || $requiresBuyerEmail === true ? 'checked' : '' }}
                    class="h-6 w-6 rounded border-2 border-pink-200 text-pink-500 shadow-inner focus:ring-pink-400">
                <div>
                    <p class="font-black text-[#2b3a67] text-lg">Buyer wajib mengisi email khusus aplikasi</p>
                    <p class="mt-1 text-sm font-bold text-pink-400">Kalau dimatikan, checkout langsung lanjut tanpa field tambahan.</p>
                </div>
            </label>
        </section>

        <section class="space-y-6 rounded-[2rem] border-4 border-[#f0f5ff] bg-white p-6 shadow-sm md:p-8">
            <div>
                <p class="mb-1 text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Visual</p>
                <h4 class="text-2xl font-black text-[#2b3a67]">Branding & Ikon Produk</h4>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Emoji Produk</label>
                    <input type="text" name="emote" value="{{ old('emote', $currentProduct->emote ?? '') }}"
                        class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] text-2xl placeholder-[#a3bbfb]"
                        placeholder="🎮 atau 📱">
                </div>

                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Upload Gambar Ikon</label>
                    @if($currentProduct?->image)
                        <div class="mb-4 flex items-center justify-between rounded-2xl border-4 border-white bg-[#f8faff] p-3 shadow-sm max-w-sm">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-white rounded-xl shadow-inner flex items-center justify-center overflow-hidden border-2 border-[#bde0fe]">
                                    <img src="{{ Storage::url($currentProduct->image) }}" class="max-w-full max-h-full object-cover">
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-[#5a76c8] bg-white px-2 py-1 border border-[#e0fbfc] rounded-md">Gambar Aktif</span>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer bg-[#ffe5e5] px-3 py-2 rounded-xl border-2 border-white hover:bg-[#ffcccc] transition-colors shadow-sm">
                                <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300 text-rose-500 focus:ring-rose-500 shadow-inner">
                                <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Hapus</span>
                            </label>
                        </div>
                    @endif
                    <input type="file" name="image" accept="image/*"
                        class="w-full text-sm font-bold text-[#8faaf3] file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:bg-[#e0fbfc] file:text-[#4bc6b9] file:shadow-sm bg-[#f4f9ff] border-4 border-white shadow-inner rounded-[1.5rem] p-2 cursor-pointer hover:file:bg-[#bde0fe] file:transition-colors file:font-black">
                </div>
            </div>
        </section>

        <div class="flex flex-col gap-4 pt-6 sm:flex-row border-t-4 border-dashed border-[#f4f9ff]">
            <button type="submit" class="flex-1 rounded-full border-4 border-white bg-[#5a76c8] py-5 text-xl font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform hover:bg-[#4760a9] active:scale-95 flex justify-center items-center gap-2">
                {{ $submitLabel }}
            </button>
            <a href="{{ route('admin.products.index') }}" class="rounded-full border-4 border-white bg-[#f8faff] px-10 py-5 text-center text-lg font-black text-[#8faaf3] transition-transform hover:bg-[#f0f5ff] active:scale-95 shadow-md">
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

            // Reset animations class
            [providerSection, targetSection, inventorySection, premiumCheckoutSection].forEach(el => {
                el.classList.remove('animate-pulse');
            });

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