@extends('layouts.user')

@section('title', 'Order - ' . $category->name)

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .fade-in { animation: fadeIn 0.35s ease-in-out forwards; }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="wiboost-font relative mx-auto mt-4 max-w-4xl overflow-hidden px-4 pb-24">
    @if(session('error'))
        <div class="mb-8 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-sm">
            <span class="text-2xl">!</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-8 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-sm">
            <p class="mb-3">Periksa lagi data pesananmu:</p>
            <ul class="list-disc pl-6 text-sm font-bold">
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

    <div id="page-product-list" class="transition-all duration-300">
        <div class="relative mb-10 flex flex-col items-center gap-6 overflow-hidden rounded-[2.5rem] border-4 border-white bg-white p-6 shadow-lg shadow-[#bde0fe]/30 md:flex-row md:gap-10 md:p-10">
            <div class="absolute -bottom-10 -right-10 text-8xl opacity-10 pointer-events-none">CAT</div>

            <div class="relative z-10 flex w-full items-center gap-6 md:w-auto">
                <a href="{{ $category->parent_id ? route('user.order.category', $category->parent->slug) : route('user.dashboard') }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border-2 border-white bg-[#f0f5ff] text-[#5a76c8] shadow-inner transition-all hover:-translate-y-1 hover:bg-[#e0fbfc]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                </a>

                <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-[1.5rem] border-4 border-white bg-gradient-to-br from-[#f0f5ff] to-[#e0ebff] text-[#5a76c8] shadow-md">
                    @if($category->image)
                        <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                    @else
                        <span class="text-xl font-black">{{ $category->emote ?? 'CAT' }}</span>
                    @endif
                </div>
            </div>

            <div class="relative z-10 flex-1 text-center md:text-left">
                <h2 class="mb-2 text-3xl font-black tracking-tight text-[#2b3a67] md:text-4xl">{{ $category->name }}</h2>
                @if($category->description)
                    <p class="inline-block rounded-2xl border-2 border-white bg-[#f4f9ff] p-4 text-left text-sm font-bold leading-relaxed text-[#4a5f96] md:text-base">
                        {!! nl2br(e($category->description)) !!}
                    </p>
                @else
                    <p class="font-bold text-[#8faaf3]">Pilih layanan di bawah ini lalu lanjutkan ke checkout.</p>
                @endif
            </div>
        </div>

        @if($showSuntikSosmedForm)
            <form action="{{ route('user.checkout.process') }}" method="POST" id="socialCheckoutForm" onsubmit="showSocialLoadingBtn()" class="rounded-[2rem] border-4 border-white bg-white p-5 shadow-lg shadow-[#bde0fe]/25 md:p-8">
                @csrf
                <input type="hidden" name="product_id" id="social_product_id" value="{{ old('product_id') }}">

                <div class="mb-5">
                    <label class="mb-3 ml-1 block text-sm font-black text-[#2b3a67]">Layanan <span class="text-[#ff6b6b]">*</span></label>
                    <select id="social_service_select" required class="w-full appearance-none rounded-xl border border-[#cbd7ea] bg-white px-4 py-3 font-bold text-[#2b3a67] outline-none focus:border-[#5a76c8]">
                        <option value="">Pilih layanan...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ (string) old('product_id') === (string) $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-5">
                    <label class="mb-3 ml-1 block text-sm font-black text-[#2b3a67]">Deskripsi</label>
                    <textarea id="social_description" rows="3" readonly class="w-full resize-none rounded-xl border border-[#cbd7ea] bg-[#eef2f7] px-4 py-3 font-bold text-[#2b3a67] outline-none">-</textarea>
                </div>

                <div class="mb-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                    <div>
                        <label class="mb-3 ml-1 block text-sm font-black text-[#2b3a67]">Minimal Pesanan</label>
                        <input type="text" id="social_min" readonly value="0" class="w-full rounded-xl border border-[#cbd7ea] bg-[#eef2f7] px-4 py-3 font-bold text-[#2b3a67] outline-none">
                    </div>

                    <div>
                        <label class="mb-3 ml-1 block text-sm font-black text-[#2b3a67]">Maksimal Pesanan</label>
                        <input type="text" id="social_max" readonly value="0" class="w-full rounded-xl border border-[#cbd7ea] bg-[#eef2f7] px-4 py-3 font-bold text-[#2b3a67] outline-none">
                    </div>

                    <div>
                        <label class="mb-3 ml-1 block text-sm font-black text-[#2b3a67]">Harga / 1000</label>
                        <div class="flex overflow-hidden rounded-xl border border-[#cbd7ea] bg-[#eef2f7]">
                            <span class="border-r border-[#cbd7ea] px-4 py-3 font-bold text-[#2b3a67]">Rp</span>
                            <input type="text" id="social_price_per_1000" readonly value="0" class="min-w-0 flex-1 bg-transparent px-4 py-3 font-bold text-[#2b3a67] outline-none">
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="mb-3 ml-1 block text-sm font-black text-[#2b3a67]">Jumlah Pesanan <span class="text-[#ff6b6b]">*</span></label>
                    <input type="number" name="order_quantity" id="social_order_quantity" min="1" value="{{ old('order_quantity') }}" required class="w-full rounded-xl border border-[#cbd7ea] bg-white px-4 py-3 font-bold text-[#2b3a67] outline-none focus:border-[#5a76c8]">
                </div>

                <div class="mb-5">
                    <label class="mb-3 ml-1 block text-sm font-black text-[#2b3a67]">Target/Link/Username <span class="text-[#ff6b6b]">*</span></label>
                    <input type="text" name="target_data" value="{{ old('target_data') }}" required class="w-full rounded-xl border border-[#cbd7ea] bg-white px-4 py-3 font-bold text-[#2b3a67] outline-none focus:border-[#5a76c8]">
                </div>

                <div class="mb-5">
                    <label class="mb-3 ml-1 block text-sm font-black text-[#2b3a67]">Waktu Rata-Rata</label>
                    <input type="text" id="social_average_time" readonly value="Pilih layanan dulu" class="w-full rounded-xl border border-[#cbd7ea] bg-[#eef2f7] px-4 py-3 font-bold text-[#2b3a67] outline-none">
                </div>

                <div class="mb-8">
                    <label class="mb-3 ml-1 block text-sm font-black text-[#2b3a67]">Total Harga</label>
                    <div class="flex overflow-hidden rounded-xl border border-[#cbd7ea] bg-[#eef2f7]">
                        <span class="border-r border-[#cbd7ea] px-4 py-3 font-bold text-[#2b3a67]">Rp</span>
                        <input type="text" id="social_total_price" readonly value="0" class="min-w-0 flex-1 bg-transparent px-4 py-3 font-bold text-[#2b3a67] outline-none">
                    </div>
                </div>

                <div class="relative mb-8 rounded-[1.5rem] border-2 border-[#e0fbfc] bg-[#f8fbff] p-5">
                    <h4 class="mb-4 text-lg font-black text-[#2b3a67]">Metode Pembayaran</h4>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label class="group relative flex cursor-pointer items-center gap-4 rounded-[1.25rem] border-2 border-white bg-white p-5 shadow-sm transition-all hover:border-[#bde0fe]">
                            <input type="radio" name="payment_method" value="wallet" class="peer sr-only" required>
                            <div class="h-6 w-6 shrink-0 rounded-full border-2 border-[#8faaf3] bg-white transition-all peer-checked:border-[7px] peer-checked:border-[#5a76c8]"></div>
                            <div class="flex-1">
                                <p class="text-base font-black text-[#2b3a67]">Saldo Wiboost</p>
                                <p class="mt-1 text-sm font-bold text-[#5a76c8]">Sisa: Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}</p>
                            </div>
                            <div class="pointer-events-none absolute inset-0 rounded-[1.25rem] border-4 border-transparent transition-colors peer-checked:border-[#5a76c8]"></div>
                        </label>

                        <label class="group relative flex cursor-pointer items-center gap-4 rounded-[1.25rem] border-2 border-white bg-white p-5 shadow-sm transition-all hover:border-[#bde0fe]">
                            <input type="radio" name="payment_method" value="manual" class="peer sr-only" required>
                            <div class="h-6 w-6 shrink-0 rounded-full border-2 border-[#8faaf3] bg-white transition-all peer-checked:border-[7px] peer-checked:border-[#5a76c8]"></div>
                            <div class="flex-1">
                                <p class="text-base font-black text-[#2b3a67]">E-Wallet & QRIS</p>
                                <p class="mt-1 text-xs font-bold text-[#8faaf3]">Otomatis via Payment Gateway</p>
                            </div>
                            <div class="pointer-events-none absolute inset-0 rounded-[1.25rem] border-4 border-transparent transition-colors peer-checked:border-[#5a76c8]"></div>
                        </label>
                    </div>
                </div>

                <button type="submit" id="btnSocialCheckout" class="flex w-full items-center justify-center rounded-[1.5rem] border-4 border-white bg-[#4bc6b9] py-4 text-xl font-black text-white shadow-xl shadow-[#4bc6b9]/30 transition hover:bg-[#3ba398] active:scale-95">
                    Bayar Pesanan
                </button>
            </form>
        @else
        <div class="mb-6 flex items-center gap-3 pl-2">
            <span class="text-2xl">LIST</span>
            <h3 class="text-2xl font-black text-[#2b3a67]">Daftar Layanan</h3>
        </div>

        <div class="flex flex-col gap-3">
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
                        class="group flex w-full items-center gap-4 rounded-[1.5rem] border-4 border-white bg-white p-4 text-left shadow-lg shadow-[#bde0fe]/20 transition-all active:scale-95 hover:border-[#bde0fe] md:p-5"
                    @else
                        disabled
                        class="relative flex w-full cursor-not-allowed items-center gap-4 overflow-hidden rounded-[1.5rem] border-4 border-white bg-gray-50 p-4 text-left opacity-60 grayscale shadow-sm md:p-5"
                    @endif
                >
                    @if($product->image)
                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-xl border-2 border-[#f0f5ff] bg-[#f4f9ff] shadow-sm md:h-12 md:w-12">
                            <img src="{{ Storage::url($product->image) }}" alt="Logo" class="h-full w-full object-cover">
                        </div>
                    @elseif($product->emote)
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 border-white bg-[#f0f5ff] text-2xl text-[#5a76c8] shadow-inner md:h-12 md:w-12 md:text-3xl">
                            {{ $product->emote }}
                        </div>
                    @else
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 border-white bg-[#f0f5ff] text-sm font-black text-[#8faaf3] shadow-inner md:h-12 md:w-12">
                            {{ strtoupper($product->process_type) }}
                        </div>
                    @endif

                    <div class="min-w-0 flex-1 pr-2">
                        <span class="block text-sm font-black leading-tight text-[#2b3a67] transition-colors group-hover:text-[#5a76c8] md:text-lg">{{ $product->name }}</span>
                        @if($plainDescription !== '')
                            <p class="mt-1 line-clamp-2 text-xs font-bold text-[#8faaf3]">{{ $plainDescription }}</p>
                        @endif
                    </div>

                    @if($isOutOfStock)
                        <span class="shrink-0 rounded-lg border border-white bg-[#ffe5e5] px-3 py-1.5 text-xs font-black text-[#ff6b6b] shadow-sm">HABIS</span>
                    @else
                        <span class="shrink-0 text-base font-black text-[#4bc6b9] md:text-xl">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    @endif
                </button>
            @empty
                <div class="rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] bg-white py-16 text-center">
                    <p class="text-xl font-black text-[#5a76c8]">Layanan sedang kosong.</p>
                </div>
            @endforelse
        </div>
        @endif
    </div>

    <div id="page-checkout" class="hidden transition-all duration-300">
        <button type="button" onclick="goBackToProducts()" class="mb-8 inline-flex items-center gap-2 rounded-full border-2 border-white bg-white px-5 py-3 font-black text-[#5a76c8] shadow-sm transition hover:-translate-x-1 hover:shadow-md">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Daftar Layanan
        </button>

        <div class="mb-8 pl-2">
            <h2 class="text-3xl font-black tracking-tight text-[#2b3a67] md:text-4xl">Checkout Pesanan</h2>
            <p class="mt-1 font-bold text-[#8faaf3]">Lengkapi data buyer sesuai layanan yang dipilih lalu pilih metode pembayaran.</p>
        </div>

        <form action="{{ route('user.checkout.process') }}" method="POST" id="checkoutForm" onsubmit="showLoadingBtn()">
            @csrf
            <input type="hidden" name="product_id" id="checkout_product_id">

            <div class="relative mb-8 flex flex-col justify-between gap-4 overflow-hidden rounded-[2rem] border-4 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] p-6 text-white shadow-lg shadow-[#bde0fe]/40 md:flex-row md:items-center md:p-8">
                <div class="absolute -bottom-4 -right-4 text-7xl opacity-20 pointer-events-none">BOX</div>

                <div class="relative z-10">
                    <span class="mb-3 inline-block rounded-full bg-[#e0fbfc] px-3 py-1 text-[10px] font-black uppercase tracking-widest text-[#5a76c8] shadow-sm">Layanan Terpilih</span>
                    <h3 class="text-2xl font-black drop-shadow-sm md:text-3xl" id="checkout_product_name">Nama Produk</h3>
                    <p id="checkout_product_description" class="mt-3 hidden max-w-2xl rounded-2xl bg-white/15 px-4 py-3 text-sm font-bold leading-relaxed text-white/90 backdrop-blur-sm"></p>
                </div>

                <div class="relative z-10 shrink-0 rounded-[1.5rem] border-2 border-white/50 bg-white/20 px-6 py-4 text-left backdrop-blur-md md:text-right">
                    <p class="mb-1 text-xs font-bold uppercase tracking-widest text-[#e0fbfc]">Total Harga</p>
                    <p class="text-2xl font-black drop-shadow-sm md:text-3xl" id="checkout_product_price">Rp 0</p>
                </div>
            </div>

            <div id="checkout_fields_section" class="relative mb-8 rounded-[2rem] border-4 border-white bg-white p-6 shadow-lg shadow-[#bde0fe]/30 md:p-8">
                <div class="absolute -left-3 -top-3 flex h-12 w-12 items-center justify-center rounded-full border-4 border-[#f4f9ff] bg-[#5a76c8] text-xl font-black text-white shadow-sm">1</div>
                <h4 class="mb-6 ml-6 text-xl font-black text-[#2b3a67]">Informasi Buyer</h4>
                <div id="checkout_fields_wrapper" class="space-y-5"></div>
            </div>

            <div id="no_target_msg" class="relative mb-8 hidden items-center gap-4 rounded-[2rem] border-4 border-white bg-[#e6fff7] p-6 shadow-lg shadow-[#bde0fe]/30 md:p-8">
                <div class="shrink-0 text-5xl">OK</div>
                <div>
                    <h4 class="text-lg font-black text-emerald-500">Kamu Tidak Perlu Mengisi Apapun</h4>
                    <p class="mt-1 text-sm font-bold leading-tight text-[#8faaf3]">Untuk layanan ini, buyer tidak perlu mengisi data tambahan. Detail nomor atau akun akan diproses otomatis oleh sistem atau admin.</p>
                </div>
            </div>

            <div class="relative mb-8 rounded-[2rem] border-4 border-white bg-white p-6 shadow-lg shadow-[#bde0fe]/30 md:p-8">
                <div id="step_number_2" class="absolute -left-3 -top-3 flex h-12 w-12 items-center justify-center rounded-full border-4 border-[#f4f9ff] bg-[#5a76c8] text-xl font-black text-white shadow-sm">2</div>
                <h4 class="mb-6 ml-6 text-xl font-black text-[#2b3a67]">Metode Pembayaran</h4>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <label class="group relative flex cursor-pointer items-center gap-4 rounded-[1.5rem] border-4 border-white bg-gradient-to-r from-[#e0fbfc] to-[#f4f9ff] p-5 shadow-sm transition-all hover:border-[#bde0fe]">
                        <input type="radio" name="payment_method" value="wallet" class="peer sr-only" required>
                        <div class="h-6 w-6 shrink-0 rounded-full border-2 border-[#8faaf3] bg-white transition-all peer-checked:border-[7px] peer-checked:border-[#5a76c8]"></div>
                        <div class="flex-1">
                            <p class="text-lg font-black text-[#2b3a67]">Saldo Wiboost</p>
                            <p class="mt-1 text-sm font-bold text-[#5a76c8]">Sisa: Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-3xl transition-transform group-hover:scale-110">BAL</div>
                        <div class="pointer-events-none absolute inset-0 rounded-[1.5rem] border-4 border-transparent transition-colors peer-checked:border-[#5a76c8]"></div>
                    </label>

                    <label class="group relative flex cursor-pointer items-center gap-4 rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] p-5 shadow-sm transition-all hover:border-[#bde0fe]">
                        <input type="radio" name="payment_method" value="manual" class="peer sr-only" required>
                        <div class="h-6 w-6 shrink-0 rounded-full border-2 border-[#8faaf3] bg-white transition-all peer-checked:border-[7px] peer-checked:border-[#5a76c8]"></div>
                        <div class="flex-1">
                            <p class="text-lg font-black text-[#2b3a67]">E-Wallet & QRIS</p>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">Otomatis via Payment Gateway</p>
                        </div>
                        <div class="text-3xl transition-transform group-hover:scale-110">PAY</div>
                        <div class="pointer-events-none absolute inset-0 rounded-[1.5rem] border-4 border-transparent transition-colors peer-checked:border-[#5a76c8]"></div>
                    </label>
                </div>
            </div>

            <button type="submit" id="btnCheckout" class="flex w-full items-center justify-center gap-3 rounded-[2rem] border-4 border-white bg-[#4bc6b9] py-5 text-2xl font-black text-white shadow-xl shadow-[#4bc6b9]/40 transition-transform hover:bg-[#3ba398] active:scale-95">
                Bayar Pesanan
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

        btn.innerHTML = 'Sedang Memproses...';
        btn.classList.add('cursor-not-allowed', 'opacity-70');
    }

    function checkoutInputClass() {
        return 'w-full rounded-[1.5rem] border-2 border-transparent bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition placeholder-[#a3bbfb] focus:border-[#5a76c8]';
    }

    function renderCheckoutFields(fields, values = {}) {
        checkoutFieldsWrapper.innerHTML = '';

        fields.forEach((field) => {
            const fieldWrapper = document.createElement('div');

            const label = document.createElement('label');
            label.className = 'mb-3 ml-2 block text-sm font-black text-[#8faaf3]';
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
            hint.className = 'mt-3 ml-2 text-xs font-bold text-[#8faaf3]';
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
            stepNumber2.innerText = '1';
        } else {
            renderCheckoutFields(fields, restoreValues);
            checkoutFieldsSection.classList.remove('hidden');
            noTargetMsg.classList.add('hidden');
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
        btn.innerHTML = 'Sedang Memproses...';
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
