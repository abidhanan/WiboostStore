@extends('layouts.user')

@section('title', 'Order - ' . $category->name)

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .fade-in { animation: fadeIn 0.35s ease-in-out forwards; }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font relative mx-auto mt-4 max-w-5xl overflow-hidden px-4 pb-24 z-10">
    
    <div class="absolute top-10 right-10 text-4xl animate-float opacity-50 pointer-events-none hidden md:block">☁️</div>
    <div class="absolute top-1/3 left-5 text-3xl animate-float-delayed opacity-50 pointer-events-none hidden md:block">✨</div>
    <div class="absolute bottom-20 left-1/4 text-2xl animate-float-delayed opacity-50 pointer-events-none hidden md:block">⭐</div>
    <div class="absolute bottom-40 right-[10%] text-5xl animate-float opacity-40 pointer-events-none hidden md:block">☁️</div>

    @if(session('error'))
        <div class="mb-8 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 relative z-10">
            <span class="text-3xl drop-shadow-sm">⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-8 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 relative z-10">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-2xl drop-shadow-sm">🛑</span>
                <p>Periksa lagi data pesananmu:</p>
            </div>
            <ul class="list-disc pl-8 text-sm font-bold">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $showSuntikSosmedForm = $isSuntikSosmedOrder ?? false;
        $socialProductsPayload = $showSuntikSosmedForm
            ? $products->map(function ($product) {
                $plainDescription = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $product->description)));

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $plainDescription !== '' ? $plainDescription : '-',
                    'min' => $product->ordersosmed_min_quantity,
                    'max' => $product->ordersosmed_max_quantity,
                    'unit_price' => $product->ordersosmed_unit_price,
                    'price_per_1000' => $product->ordersosmed_price_per_thousand,
                    'average_time' => $product->ordersosmed_average_time,
                ];
            })->values()
            : collect();
    @endphp

    <div id="page-product-list" class="transition-all duration-300 relative z-10">
        <div class="relative mb-10 flex flex-col items-center gap-6 overflow-hidden rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm p-6 shadow-xl shadow-[#bde0fe]/30 md:flex-row md:gap-8 md:p-8">
            <div class="absolute -bottom-10 -right-10 text-9xl opacity-10 pointer-events-none animate-float">📦</div>

            <div class="relative z-10 flex w-full items-center justify-between md:justify-start gap-4 md:w-auto">
                <a href="{{ $category->parent_id ? route('user.order.category', $category->parent->slug) : route('user.dashboard') }}" class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.2rem] border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition-transform hover:scale-110 active:scale-95 hover:bg-[#e0fbfc]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                </a>

                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-[1.5rem] border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-inner p-2">
                    @if($category->image)
                        <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="h-full w-full object-contain drop-shadow-sm">
                    @else
                        <span class="text-4xl font-black drop-shadow-sm">{{ $category->emote ?? '📦' }}</span>
                    @endif
                </div>
            </div>

            <div class="relative z-10 flex-1 text-center md:text-left">
                <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                    Katalog
                </div>
                <h2 class="mb-2 text-3xl font-black tracking-tight text-[#2b3a67] md:text-4xl drop-shadow-sm">{{ $category->name }}</h2>
                @if($category->description)
                    <p class="inline-block rounded-2xl border-2 border-white bg-[#f8faff] p-4 text-left text-sm font-bold leading-relaxed text-[#4a5f96] shadow-inner w-full md:w-auto">
                        {!! nl2br(e($category->description)) !!}
                    </p>
                @else
                    <p class="font-bold text-[#8faaf3]">Pilih layanan di bawah ini lalu lanjutkan ke checkout.</p>
                @endif
            </div>
        </div>

        @if($showSuntikSosmedForm)
            <form action="{{ route('user.checkout.process') }}" method="POST" id="socialCheckoutForm" onsubmit="showSocialLoadingBtn()" class="rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm p-6 shadow-xl shadow-[#bde0fe]/30 md:p-10 relative overflow-hidden">
                @csrf
                <input type="hidden" name="product_id" id="social_product_id" value="{{ old('product_id') }}">

                <div class="flex items-center gap-3 mb-6 border-b-4 border-dashed border-[#f4f9ff] pb-4">
                    <span class="text-3xl">🚀</span>
                    <h3 class="text-2xl font-black text-[#2b3a67]">Detail Pesanan</h3>
                </div>

                <div class="mb-6">
                    <label class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Layanan <span class="text-[#ff6b6b]">*</span></label>
                    <select id="social_service_select" required class="w-full appearance-none rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] outline-none shadow-inner transition focus:border-[#bde0fe]">
                        <option value="">Pilih layanan...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ (string) old('product_id') === (string) $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Deskripsi</label>
                    <textarea id="social_description" rows="3" readonly class="w-full resize-none rounded-[1.5rem] border-4 border-white bg-[#eef2f7] px-5 py-4 font-bold text-[#4a5f96] outline-none shadow-inner">-</textarea>
                </div>

                <div class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-3">
                    <div>
                        <label class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Minimal</label>
                        <input type="text" id="social_min" readonly value="0" class="w-full rounded-[1.5rem] border-4 border-white bg-[#eef2f7] px-5 py-4 font-black text-[#4a5f96] outline-none shadow-inner">
                    </div>

                    <div>
                        <label class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Maksimal</label>
                        <input type="text" id="social_max" readonly value="0" class="w-full rounded-[1.5rem] border-4 border-white bg-[#eef2f7] px-5 py-4 font-black text-[#4a5f96] outline-none shadow-inner">
                    </div>

                    <div>
                        <label class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Harga / 1000</label>
                        <div class="flex overflow-hidden rounded-[1.5rem] border-4 border-white bg-[#eef2f7] shadow-inner">
                            <span class="border-r-2 border-[#cbd7ea] px-5 py-4 font-black text-[#4a5f96] bg-[#e2e8f0]">Rp</span>
                            <input type="text" id="social_price_per_1000" readonly value="0" class="min-w-0 flex-1 bg-transparent px-4 py-4 font-black text-[#4a5f96] outline-none">
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Jumlah Pesanan <span class="text-[#ff6b6b]">*</span></label>
                    <input type="number" name="order_quantity" id="social_order_quantity" min="1" value="{{ old('order_quantity') }}" required class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] outline-none shadow-inner transition focus:border-[#bde0fe]">
                </div>

                <div class="mb-6">
                    <label class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Target/Link/Username <span class="text-[#ff6b6b]">*</span></label>
                    <input type="text" name="target_data" value="{{ old('target_data') }}" required class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] outline-none shadow-inner transition focus:border-[#bde0fe]">
                </div>

                <div class="mb-6">
                    <label class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Waktu Rata-Rata</label>
                    <input type="text" id="social_average_time" readonly value="Pilih layanan dulu" class="w-full rounded-[1.5rem] border-4 border-white bg-[#eef2f7] px-5 py-4 font-black text-[#4a5f96] outline-none shadow-inner">
                </div>

                <div class="mb-10 bg-[#f8faff] p-5 rounded-[1.5rem] border-4 border-white shadow-inner">
                    <label class="mb-2 ml-2 block text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Total Harga</label>
                    <div class="flex overflow-hidden rounded-[1.2rem] border-2 border-white bg-white shadow-sm">
                        <span class="px-5 py-3 font-black text-[#4bc6b9] text-xl bg-[#e6fff7]">Rp</span>
                        <input type="text" id="social_total_price" readonly value="0" class="min-w-0 flex-1 bg-transparent px-4 py-3 font-black text-xl text-[#2b3a67] outline-none">
                    </div>
                </div>

                <div class="relative mb-8 rounded-[2rem] border-4 border-white bg-white p-6 shadow-md shadow-[#bde0fe]/20">
                    <h4 class="mb-5 ml-2 text-xl font-black text-[#2b3a67]">Metode Pembayaran</h4>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label class="group relative flex cursor-pointer items-center gap-4 rounded-[1.5rem] border-4 border-white bg-gradient-to-r from-[#e0fbfc] to-[#f4f9ff] p-5 shadow-sm transition-all hover:border-[#bde0fe]">
                            <input type="radio" name="payment_method" value="wallet" class="peer sr-only" required>
                            <div class="h-6 w-6 shrink-0 rounded-full border-2 border-[#8faaf3] bg-white transition-all peer-checked:border-[7px] peer-checked:border-[#5a76c8]"></div>
                            <div class="flex-1">
                                <p class="text-base font-black text-[#2b3a67]">Saldo Wiboost</p>
                                <p class="mt-1 text-sm font-bold text-[#5a76c8]">Sisa: Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-2xl transition-transform group-hover:scale-110 opacity-50">💰</div>
                            <div class="pointer-events-none absolute inset-0 rounded-[1.5rem] border-4 border-transparent transition-colors peer-checked:border-[#5a76c8]"></div>
                        </label>

                        <label class="group relative flex cursor-pointer items-center gap-4 rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] p-5 shadow-sm transition-all hover:border-[#bde0fe]">
                            <input type="radio" name="payment_method" value="manual" class="peer sr-only" required>
                            <div class="h-6 w-6 shrink-0 rounded-full border-2 border-[#8faaf3] bg-white transition-all peer-checked:border-[7px] peer-checked:border-[#5a76c8]"></div>
                            <div class="flex-1">
                                <p class="text-base font-black text-[#2b3a67]">E-Wallet & QRIS</p>
                                <p class="mt-1 text-xs font-bold text-[#8faaf3]">Otomatis via Gateway</p>
                            </div>
                            <div class="text-2xl transition-transform group-hover:scale-110 opacity-50">💳</div>
                            <div class="pointer-events-none absolute inset-0 rounded-[1.5rem] border-4 border-transparent transition-colors peer-checked:border-[#5a76c8]"></div>
                        </label>
                    </div>
                </div>

                <button type="submit" id="btnSocialCheckout" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#4bc6b9] py-5 text-xl font-black text-white shadow-xl shadow-[#4bc6b9]/40 transition-transform hover:bg-[#3ba398] active:scale-95">
                    Bayar Pesanan 🚀
                </button>
            </form>
        @else
        <div class="mb-6 flex items-center gap-3 pl-2">
            <h3 class="text-2xl font-black text-[#2b3a67]">Daftar Layanan</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($products as $product)
                @php
                    $isOutOfStock = in_array($product->process_type, ['account', 'number'], true) && ($product->available_stock ?? 0) <= 0;
                    $plainDescription = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $product->description)));
                @endphp

                <button
                    type="button"
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-price="{{ $product->price }}"
                    data-description="{{ $plainDescription }}"
                    data-checkout-fields='{{ json_encode($product->checkout_fields, JSON_UNESCAPED_UNICODE) }}'
                    @if(!$isOutOfStock)
                        onclick="goToCheckout(this)"
                        class="group flex flex-col justify-between w-full rounded-[2rem] border-4 border-white bg-white/95 backdrop-blur-sm p-6 text-left shadow-xl shadow-[#bde0fe]/20 transition-all hover:scale-[1.02] hover:border-[#bde0fe] active:scale-95 min-h-[160px]"
                    @else
                        disabled
                        class="relative flex flex-col justify-between w-full cursor-not-allowed overflow-hidden rounded-[2rem] border-4 border-white bg-gray-50 p-6 text-left opacity-60 grayscale shadow-sm min-h-[160px]"
                    @endif
                >
                    <div class="flex items-start justify-between gap-3 w-full mb-4">
                        <div class="min-w-0 flex-1">
                            <span class="block text-base font-black leading-tight text-[#2b3a67] transition-colors group-hover:text-[#5a76c8]">{{ $product->name }}</span>
                        </div>
                        @if($product->image)
                            <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl border-2 border-white bg-[#f4f9ff] shadow-sm">
                                <img src="{{ Storage::url($product->image) }}" alt="Logo" class="h-full w-full object-cover">
                            </div>
                        @elseif($product->emote)
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border-2 border-white bg-[#f4f9ff] text-2xl drop-shadow-sm shadow-inner">
                                {{ $product->emote }}
                            </div>
                        @else
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border-2 border-white bg-[#f4f9ff] text-[10px] font-black text-[#8faaf3] shadow-inner uppercase">
                                {{ $product->process_type }}
                            </div>
                        @endif
                    </div>
                    
                    @if($plainDescription !== '')
                        <p class="mb-4 line-clamp-2 text-xs font-bold text-[#8faaf3] flex-1">{{ $plainDescription }}</p>
                    @endif

                    <div class="mt-auto flex items-center justify-between w-full pt-4 border-t-2 border-dashed border-[#f4f9ff]">
                        @if($isOutOfStock)
                            <span class="shrink-0 rounded-lg border border-white bg-[#ffe5e5] px-3 py-1.5 text-xs font-black text-[#ff6b6b] shadow-sm">HABIS</span>
                        @else
                            <span class="text-xs font-black uppercase tracking-widest text-[#8faaf3]">Harga</span>
                            <span class="shrink-0 text-lg font-black text-[#4bc6b9]">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </button>
            @empty
                <div class="col-span-full rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] bg-white py-20 text-center shadow-lg shadow-[#bde0fe]/20">
                    <div class="text-6xl mb-4 opacity-50 animate-float">📦</div>
                    <p class="text-2xl font-black text-[#5a76c8]">Layanan sedang kosong.</p>
                </div>
            @endforelse
        </div>
        @endif
    </div>

    <div id="page-checkout" class="hidden transition-all duration-300 relative z-10">
        <button type="button" onclick="goBackToProducts()" class="mb-8 inline-flex items-center gap-2 rounded-full border-4 border-white bg-white/95 backdrop-blur-sm px-6 py-3 font-black text-[#5a76c8] shadow-lg hover:shadow-[#bde0fe]/50 transition-transform active:scale-95">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Daftar
        </button>

        <div class="mb-8 pl-2">
            <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
                Checkout
            </div>
            <h2 class="text-3xl sm:text-4xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm">Pesanan Anda 🛒</h2>
            <p class="mt-2 font-bold text-[#8faaf3]">Lengkapi data sesuai layanan yang dipilih lalu pilih metode pembayaran.</p>
        </div>

        <form action="{{ route('user.checkout.process') }}" method="POST" id="checkoutForm" onsubmit="showLoadingBtn()">
            @csrf
            <input type="hidden" name="product_id" id="checkout_product_id">

            <div class="relative mb-8 flex flex-col justify-between gap-6 overflow-hidden rounded-[2.5rem] border-4 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] p-8 text-white shadow-xl shadow-[#bde0fe]/40 md:flex-row md:items-center">
                <div class="absolute -bottom-6 -right-6 text-9xl opacity-20 pointer-events-none animate-float">🛍️</div>

                <div class="relative z-10">
                    <span class="mb-3 inline-block rounded-full bg-white/20 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-white shadow-inner border border-white/50">Layanan Terpilih</span>
                    <h3 class="text-3xl font-black drop-shadow-md leading-tight" id="checkout_product_name">Nama Produk</h3>
                    <p id="checkout_product_description" class="mt-4 hidden max-w-2xl rounded-2xl bg-white/10 px-5 py-4 text-sm font-bold leading-relaxed text-white backdrop-blur-md shadow-inner border border-white/20"></p>
                </div>

                <div class="relative z-10 shrink-0 rounded-[2rem] border-4 border-white/50 bg-white/20 px-8 py-6 text-left backdrop-blur-md md:text-right shadow-lg">
                    <p class="mb-1 text-[10px] font-black uppercase tracking-widest text-[#e0fbfc]">Total Harga</p>
                    <p class="text-3xl font-black drop-shadow-md" id="checkout_product_price">Rp 0</p>
                </div>
            </div>

            <div id="checkout_fields_section" class="relative mb-8 rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm p-8 shadow-xl shadow-[#bde0fe]/30">
                <div class="absolute -left-4 -top-4 flex h-14 w-14 items-center justify-center rounded-[1.2rem] border-4 border-white bg-[#5a76c8] text-2xl font-black text-white shadow-lg transform -rotate-6">1</div>
                <h4 class="mb-6 ml-8 text-2xl font-black text-[#2b3a67]">Informasi Buyer</h4>
                <div id="checkout_fields_wrapper" class="space-y-6"></div>
            </div>

            <div id="no_target_msg" class="relative mb-8 hidden items-center gap-5 rounded-[2.5rem] border-4 border-white bg-[#e6fff7] p-8 shadow-xl shadow-emerald-100/50">
                <div class="shrink-0 text-6xl drop-shadow-sm animate-bounce">✅</div>
                <div>
                    <h4 class="text-xl font-black text-emerald-500 mb-1">Tidak Perlu Isi Form</h4>
                    <p class="text-sm font-bold leading-relaxed text-[#4bc6b9]">Untuk layanan ini, kamu tidak perlu mengisi data tambahan. Detail nomor atau akun akan diproses otomatis oleh sistem secara instan.</p>
                </div>
            </div>

            <div class="relative mb-8 rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm p-8 shadow-xl shadow-[#bde0fe]/30">
                <div id="step_number_2" class="absolute -left-4 -top-4 flex h-14 w-14 items-center justify-center rounded-[1.2rem] border-4 border-white bg-[#5a76c8] text-2xl font-black text-white shadow-lg transform rotate-6">2</div>
                <h4 class="mb-6 ml-8 text-2xl font-black text-[#2b3a67]">Metode Pembayaran</h4>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <label class="group relative flex cursor-pointer items-center gap-4 rounded-[2rem] border-4 border-white bg-gradient-to-r from-[#e0fbfc] to-[#f4f9ff] p-6 shadow-md transition-transform hover:scale-[1.02]">
                        <input type="radio" name="payment_method" value="wallet" class="peer sr-only" required>
                        <div class="h-7 w-7 shrink-0 rounded-full border-2 border-[#8faaf3] bg-white transition-all peer-checked:border-[8px] peer-checked:border-[#5a76c8]"></div>
                        <div class="flex-1">
                            <p class="text-xl font-black text-[#2b3a67]">Saldo Wiboost</p>
                            <p class="mt-1 text-sm font-bold text-[#5a76c8]">Sisa: Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-4xl transition-transform group-hover:scale-110 drop-shadow-sm opacity-50">💰</div>
                        <div class="pointer-events-none absolute inset-0 rounded-[2rem] border-4 border-transparent transition-colors peer-checked:border-[#5a76c8]"></div>
                    </label>

                    <label class="group relative flex cursor-pointer items-center gap-4 rounded-[2rem] border-4 border-white bg-[#f4f9ff] p-6 shadow-md transition-transform hover:scale-[1.02]">
                        <input type="radio" name="payment_method" value="manual" class="peer sr-only" required>
                        <div class="h-7 w-7 shrink-0 rounded-full border-2 border-[#8faaf3] bg-white transition-all peer-checked:border-[8px] peer-checked:border-[#5a76c8]"></div>
                        <div class="flex-1">
                            <p class="text-xl font-black text-[#2b3a67]">E-Wallet & QRIS</p>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">Otomatis via Gateway</p>
                        </div>
                        <div class="text-4xl transition-transform group-hover:scale-110 drop-shadow-sm opacity-50">💳</div>
                        <div class="pointer-events-none absolute inset-0 rounded-[2rem] border-4 border-transparent transition-colors peer-checked:border-[#5a76c8]"></div>
                    </label>
                </div>
            </div>

            <button type="submit" id="btnCheckout" class="flex w-full items-center justify-center gap-3 rounded-full border-4 border-white bg-[#4bc6b9] py-5 text-2xl font-black text-white shadow-xl shadow-[#4bc6b9]/40 transition-transform hover:bg-[#3ba398] active:scale-95">
                Bayar Pesanan 🚀
            </button>
        </form>
    </div>
</div>

<script>
    const pageProductList = document.getElementById('page-product-list');
    const pageCheckout = document.getElementById('page-checkout');
    const checkoutFieldsSection = document.getElementById('checkout_fields_section');
    const checkoutFieldsWrapper = document.getElementById('checkout_fields_wrapper');
    const noTargetMsg = document.getElementById('no_target_msg');
    const stepNumber2 = document.getElementById('step_number_2');
    const oldCheckoutInput = @json(old());
    const oldProductId = @json(old('product_id'));
    const socialServices = @json($socialProductsPayload);
    const socialServiceSelect = document.getElementById('social_service_select');
    const socialQuantityInput = document.getElementById('social_order_quantity');
    const rupiahFormatter = new Intl.NumberFormat('id-ID');

    function findSocialService() {
        if (!socialServiceSelect) {
            return null;
        }

        return socialServices.find((service) => String(service.id) === String(socialServiceSelect.value)) || null;
    }

    function syncSocialService() {
        const service = findSocialService();
        const productInput = document.getElementById('social_product_id');

        if (productInput) {
            productInput.value = service ? service.id : '';
        }

        document.getElementById('social_description').value = service ? service.description : '-';
        document.getElementById('social_min').value = service ? rupiahFormatter.format(service.min) : '0';
        document.getElementById('social_max').value = service ? rupiahFormatter.format(service.max) : '0';
        document.getElementById('social_price_per_1000').value = service ? rupiahFormatter.format(Math.ceil(service.price_per_1000)) : '0';
        document.getElementById('social_average_time').value = service ? service.average_time : 'Pilih layanan dulu';

        if (socialQuantityInput && service) {
            socialQuantityInput.min = service.min;
            socialQuantityInput.max = service.max;
            socialQuantityInput.placeholder = `${service.min} - ${service.max}`;
        }

        updateSocialTotal();
    }

    function updateSocialTotal() {
        const service = findSocialService();
        const quantity = parseInt(socialQuantityInput?.value || '0', 10);
        const total = service && quantity > 0
            ? Math.ceil((quantity * service.unit_price) / 100) * 100
            : 0;

        const totalInput = document.getElementById('social_total_price');
        if (totalInput) {
            totalInput.value = rupiahFormatter.format(total);
        }
    }

    function showSocialLoadingBtn() {
        const btn = document.getElementById('btnSocialCheckout');

        if (!btn) {
            return;
        }

        btn.innerHTML = 'Memproses... ⏳';
        btn.classList.add('cursor-not-allowed', 'opacity-70');
    }

    function checkoutInputClass() {
        return 'w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] placeholder-[#a3bbfb]';
    }

    function renderCheckoutFields(fields, values = {}) {
        checkoutFieldsWrapper.innerHTML = '';

        fields.forEach((field) => {
            const fieldWrapper = document.createElement('div');

            const label = document.createElement('label');
            label.className = 'mb-2 ml-2 block text-sm font-black text-[#5a76c8]';
            label.textContent = field.label;

            let input;
            if (field.type === 'textarea') {
                input = document.createElement('textarea');
                input.rows = 5;
            } else {
                input = document.createElement('input');
                input.type = field.type || 'text';
            }

            input.name = field.name;
            input.id = 'checkout_' + field.name;
            input.className = checkoutInputClass();
            input.placeholder = field.placeholder || '';
            input.value = values[field.name] || '';

            if (field.type === 'tel') {
                input.inputMode = 'tel';
            }

            if (field.required !== false) {
                input.required = true;
            }

            const hint = document.createElement('p');
            hint.className = 'mt-2 ml-2 text-xs font-bold text-[#8faaf3]';
            hint.textContent = field.hint || '';

            fieldWrapper.appendChild(label);
            fieldWrapper.appendChild(input);

            if (field.hint) {
                fieldWrapper.appendChild(hint);
            }

            checkoutFieldsWrapper.appendChild(fieldWrapper);
        });
    }

    function goToCheckout(button, restoreValues = {}) {
        const { id, name, price, description } = button.dataset;
        const fields = JSON.parse(button.dataset.checkoutFields || '[]');
        const descriptionNode = document.getElementById('checkout_product_description');

        document.getElementById('checkout_product_id').value = id;
        document.getElementById('checkout_product_name').innerText = name;
        document.getElementById('checkout_product_price').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);

        if (description && description.trim() !== '') {
            descriptionNode.innerText = description;
            descriptionNode.classList.remove('hidden');
        } else {
            descriptionNode.innerText = '';
            descriptionNode.classList.add('hidden');
        }

        if (fields.length === 0) {
            checkoutFieldsSection.classList.add('hidden');
            checkoutFieldsWrapper.innerHTML = '';
            noTargetMsg.classList.remove('hidden');
            noTargetMsg.classList.add('flex');
            stepNumber2.innerText = '1';
        } else {
            renderCheckoutFields(fields, restoreValues);
            checkoutFieldsSection.classList.remove('hidden');
            noTargetMsg.classList.add('hidden');
            noTargetMsg.classList.remove('flex');
            stepNumber2.innerText = '2';
        }

        pageProductList.classList.add('hidden');
        pageCheckout.classList.remove('hidden');
        pageCheckout.classList.add('fade-in');

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function goBackToProducts() {
        pageCheckout.classList.add('hidden');
        pageProductList.classList.remove('hidden');
        pageProductList.classList.add('fade-in');
        document.getElementById('checkoutForm').reset();
        checkoutFieldsWrapper.innerHTML = '';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showLoadingBtn() {
        const btn = document.getElementById('btnCheckout');
        btn.innerHTML = 'Memproses... ⏳';
        btn.classList.add('cursor-not-allowed', 'opacity-70');
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (socialServiceSelect) {
            socialServiceSelect.addEventListener('change', syncSocialService);
            socialQuantityInput?.addEventListener('input', updateSocialTotal);
            syncSocialService();
        }

        if (!oldProductId) {
            return;
        }

        const productButton = document.querySelector(`[data-id="${oldProductId}"]`);
        if (productButton) {
            goToCheckout(productButton, oldCheckoutInput || {});
        }
    });
</script>
@endsection