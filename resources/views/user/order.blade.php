@extends(($isGuestCatalog ?? false) ? 'layouts.catalog_guest' : 'layouts.user')

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

    .checkout-step-heading {
        display: flex !important;
        align-items: center;
        gap: 1rem;
        min-width: 0;
        position: relative;
        z-index: 2;
    }

    @media (max-width: 640px) {
        .checkout-step-heading {
            gap: 0.8rem;
        }
    }

    #page-checkout {
        scroll-margin-top: 1rem;
    }

    .discount-corner-ribbon {
        pointer-events: none;
        position: absolute;
        right: -1px;
        top: -1px;
        z-index: 20;
        width: 7.5rem;
        height: 7.5rem;
        overflow: hidden;
        border-top-right-radius: 1.7rem;
    }

    .discount-corner-ribbon__text {
        position: absolute;
        right: -2.65rem;
        top: 1.35rem;
        display: block;
        width: 10rem;
        transform: rotate(45deg);
        transform-origin: center;
        background: #5a76c8;
        padding: 0.45rem 0;
        text-align: center;
        font-size: 0.55rem;
        font-weight: 900;
        letter-spacing: 0.14em;
        color: white;
        text-transform: uppercase;
        box-shadow: 0 12px 26px rgba(90, 118, 200, 0.28);
    }

    .discount-corner-ribbon--checkout {
        width: 7.75rem;
        height: 7.75rem;
        border-top-right-radius: 1.75rem;
    }

    .discount-corner-ribbon--checkout .discount-corner-ribbon__text {
        right: -2.75rem;
        top: 1.45rem;
        width: 10.25rem;
        padding: 0.45rem 0;
        font-size: 0.55rem;
    }

    .discount-corner-ribbon--sold-out .discount-corner-ribbon__text {
        background: #ff6b6b;
        box-shadow: 0 12px 26px rgba(255, 107, 107, 0.28);
    }

    .discount-corner-ribbon--member .discount-corner-ribbon__text {
        background: #8b5cf6 !important;
        box-shadow: 0 12px 26px rgba(139, 92, 246, 0.30) !important;
    }

    .discount-old-price {
        color: #111827 !important;
        text-decoration-line: line-through !important;
        text-decoration-thickness: 2px !important;
        text-decoration-color: #111827 !important;
    }

    .discount-old-price-light {
        text-decoration-line: line-through !important;
        text-decoration-thickness: 2px !important;
        text-decoration-color: rgba(255, 255, 255, 0.85) !important;
    }

    .wiboost-square-logo-img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        border-radius: 1rem;
    }

    .wiboost-header-logo-img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        border-radius: 1rem;
    }

    .wiboost-header-logo-frame {
        aspect-ratio: 1 / 1;
        flex: 0 0 4.25rem;
        height: 4.25rem !important;
        min-height: 4.25rem;
        min-width: 4.25rem;
        width: 4.25rem !important;
    }

    .wiboost-order-header-title {
        font-size: clamp(1.05rem, 5.5vw, 1.55rem);
        line-height: 1.08;
        max-width: 100%;
        overflow: visible;
        text-overflow: clip;
        white-space: nowrap;
    }

    .wiboost-service-select-wrap {
        position: relative;
    }

    .wiboost-service-native-select {
        clip: rect(0 0 0 0) !important;
        clip-path: inset(50%) !important;
        height: 1px !important;
        opacity: 0 !important;
        overflow: hidden !important;
        pointer-events: none !important;
        position: absolute !important;
        white-space: nowrap !important;
        width: 1px !important;
    }

    .wiboost-service-trigger {
        align-items: center;
        background: #f4f9ff;
        border: 4px solid #ffffff;
        border-radius: 1.5rem;
        box-shadow: inset 0 2px 8px rgba(90, 118, 200, 0.08), 0 12px 28px rgba(189, 224, 254, 0.22);
        color: #2b3a67;
        display: flex;
        font-weight: 900;
        gap: 1rem;
        justify-content: space-between;
        min-height: 4.25rem;
        padding: 1rem 1.25rem;
        text-align: left;
        transition: border-color 160ms ease, box-shadow 160ms ease, background 160ms ease;
        width: 100%;
    }

    .wiboost-service-trigger:hover,
    .wiboost-service-trigger:focus {
        background: #f8faff;
        border-color: #bde0fe;
        box-shadow: inset 0 2px 8px rgba(90, 118, 200, 0.08), 0 16px 34px rgba(189, 224, 254, 0.34);
        outline: 0;
    }

    .wiboost-service-select-wrap.is-open .wiboost-service-trigger {
        background: #ffffff;
        border-color: #8faaf3;
    }

    .wiboost-service-select-wrap.has-error .wiboost-service-trigger {
        border-color: #ffb3b3;
        box-shadow: 0 0 0 4px rgba(255, 107, 107, 0.12), inset 0 2px 8px rgba(90, 118, 200, 0.08);
    }

    .wiboost-service-trigger-text {
        display: block;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .wiboost-service-select-arrow {
        flex: 0 0 auto;
        height: 1.15rem;
        width: 1.15rem;
        color: #5a76c8;
        transform: rotate(0deg);
        transition: transform 0.18s ease;
    }

    .wiboost-service-select-wrap.is-open .wiboost-service-select-arrow {
        transform: rotate(180deg);
    }

    .wiboost-service-dropdown {
        background: rgba(255, 255, 255, 0.98);
        border: 4px solid #ffffff;
        border-radius: 1.75rem;
        box-shadow: 0 24px 60px rgba(90, 118, 200, 0.22);
        contain: layout paint;
        display: none;
        left: 0;
        max-height: min(24rem, 52vh);
        overflow-y: auto;
        padding: 0.5rem;
        position: absolute;
        right: 0;
        top: calc(100% + 0.65rem);
        z-index: 80;
    }

    .wiboost-service-select-wrap.is-open .wiboost-service-dropdown {
        display: block;
    }

    .wiboost-service-dropdown::-webkit-scrollbar {
        width: 0.55rem;
    }

    .wiboost-service-dropdown::-webkit-scrollbar-thumb {
        background: #bde0fe;
        border-radius: 9999px;
    }

    .wiboost-service-option {
        align-items: center;
        background: transparent;
        border: 2px solid transparent;
        border-radius: 1.15rem;
        color: #2b3a67;
        display: flex;
        font-weight: 800;
        gap: 0.75rem;
        justify-content: space-between;
        line-height: 1.25;
        padding: 0.85rem 1rem;
        text-align: left;
        transition: background 120ms ease, border-color 120ms ease, color 120ms ease;
        width: 100%;
    }

    .wiboost-service-option:hover,
    .wiboost-service-option:focus {
        background: #f4f9ff;
        border-color: #e0ebff;
        outline: 0;
    }

    .wiboost-service-option.is-selected {
        background: #e0fbfc;
        border-color: #bde0fe;
        color: #5a76c8;
        font-weight: 900;
    }

    .wiboost-service-option-label {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .wiboost-service-option-check {
        align-items: center;
        background: #5a76c8;
        border-radius: 9999px;
        color: #ffffff;
        display: none;
        flex: 0 0 auto;
        height: 1.4rem;
        justify-content: center;
        width: 1.4rem;
    }

    .wiboost-service-option.is-selected .wiboost-service-option-check {
        display: inline-flex;
    }

    .wiboost-service-load-more {
        border-radius: 1rem;
        color: #5a76c8;
        display: block;
        font-size: 0.78rem;
        font-weight: 900;
        padding: 0.8rem 1rem;
        text-align: center;
    }

    @media (max-width: 640px) {
        .wiboost-service-dropdown {
            max-height: 20rem;
        }

        .wiboost-service-option {
            font-size: 0.86rem;
            padding: 0.8rem 0.85rem;
        }
    }

    .wiboost-money-field {
        isolation: isolate;
    }

    .wiboost-money-field input,
    .wiboost-money-input {
        -webkit-appearance: none !important;
        appearance: none !important;
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        outline: 0 !important;
    }

    .wiboost-money-field input:focus,
    .wiboost-money-input:focus {
        border: 0 !important;
        box-shadow: none !important;
        outline: 0 !important;
    }

    .wiboost-order-header-description {
        font-size: clamp(0.78rem, 3.25vw, 0.9rem);
        line-height: 1.45;
    }

    .wiboost-product-square {
        aspect-ratio: 1 / 1.18;
        align-content: stretch !important;
        display: grid !important;
        grid-template-rows: minmax(0, 1fr) auto;
        height: auto !important;
        min-height: clamp(12.75rem, 56vw, 14.25rem) !important;
    }

    .wiboost-product-square--with-description {
        min-height: clamp(13.5rem, 60vw, 15.25rem) !important;
    }

    .wiboost-compact-logo {
        height: clamp(4.15rem, 18vw, 4.75rem) !important;
        margin-bottom: 0.5rem;
        width: clamp(4.15rem, 18vw, 4.75rem) !important;
    }

    .wiboost-compact-price-zone {
        align-self: end;
        min-height: 3rem;
        width: 100%;
    }

    .wiboost-compact-card-main {
        justify-content: center;
        min-height: 0;
        overflow: visible;
    }

    .wiboost-compact-card-title {
        display: block;
        font-size: clamp(0.7rem, 2.72vw, 0.9rem);
        line-height: 1.16;
        min-height: 0;
        overflow: visible;
        overflow-wrap: anywhere;
        white-space: normal;
        word-break: normal;
    }

    .wiboost-member-product-card:hover {
        border-color: #c4b5fd !important;
        box-shadow: 0 22px 48px rgba(139, 92, 246, 0.30) !important;
    }

    .wiboost-member-product-card:hover .wiboost-product-title {
        color: #8b5cf6 !important;
    }

    .wiboost-member-product-card:hover .wiboost-compact-logo,
    .wiboost-member-product-card:hover .wiboost-square-logo-img {
        filter: drop-shadow(0 12px 18px rgba(139, 92, 246, 0.24));
    }

    @media (max-width: 380px) {
        .wiboost-compact-logo {
            height: clamp(3.85rem, 17vw, 4.35rem) !important;
            width: clamp(3.85rem, 17vw, 4.35rem) !important;
        }

        .wiboost-compact-card-title {
            font-size: clamp(0.66rem, 2.85vw, 0.78rem);
        }
    }

    @media (min-width: 640px) {
        .wiboost-header-logo-frame {
            flex-basis: 6rem;
            height: 6rem !important;
            min-height: 6rem;
            min-width: 6rem;
            width: 6rem !important;
        }

        .wiboost-order-header-title {
            font-size: clamp(1.75rem, 2.6vw, 2.25rem);
        }

        .wiboost-order-header-description {
            font-size: 0.95rem;
        }

        .wiboost-product-square {
            aspect-ratio: 1 / 1;
            align-content: stretch !important;
            min-height: 0 !important;
        }

        .wiboost-product-square--with-description {
            min-height: 0 !important;
        }

        .wiboost-compact-logo {
            height: 6rem !important;
            margin-bottom: 1rem;
            width: 6rem !important;
        }

        .wiboost-compact-price-zone {
            min-height: 0;
        }

        .wiboost-compact-card-title {
            font-size: 0.9rem;
            min-height: 0;
        }
    }

    #checkout_product_price {
        color: #ffffff !important;
    }

    #checkout_original_price {
        color: rgba(255, 255, 255, 0.78) !important;
    }

</style>

<div class="wiboost-font relative mx-auto mt-0 w-full overflow-visible pt-0 pb-24 z-10">
    
    @if(session('error'))
        <div class="mb-8 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 relative z-10">
            <span class="drop-shadow-sm">@include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'h-8 w-8'])</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-8 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 relative z-10">
            <div class="flex items-center gap-2 mb-3">
                <span class="drop-shadow-sm">@include('partials.wiboost-icon', ['name' => 'stop', 'class' => 'h-7 w-7'])</span>
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
        $isGuestCatalog = $isGuestCatalog ?? false;
        $categoryRouteName = $isGuestCatalog ? 'guest.order.category' : 'user.order.category';
        $categoryHomeUrl = $isGuestCatalog ? route('home') . '#kategori' : route('user.dashboard');
        $loginUrl = route('login');
        $showSuntikSosmedForm = $isSuntikSosmedOrder ?? false;
        $showSuntikSosmedForm = $isGuestCatalog ? false : $showSuntikSosmedForm;
        $showTopUpGameCompactCards = ($isTopUpGameOrder ?? false) && ! $showSuntikSosmedForm;
        $showCompactServiceCards = ($isCompactServiceCardOrder ?? $showTopUpGameCompactCards) && ! $showSuntikSosmedForm;
        $rootCategorySlugForCards = $rootCategorySlug ?? null;
        $categoryPricePaletteSegments = [$rootCategorySlugForCards, $category->slug ?? null, $category->name ?? null];
        $categoryPricePaletteCursor = $category->parent ?? null;

        while($categoryPricePaletteCursor) {
            $categoryPricePaletteSegments[] = $categoryPricePaletteCursor->slug ?? null;
            $categoryPricePaletteSegments[] = $categoryPricePaletteCursor->name ?? null;
            $categoryPricePaletteCursor = $categoryPricePaletteCursor->parent ?? null;
        }

        $categoryPricePaletteText = \Illuminate\Support\Str::lower(trim(implode(' ', array_filter($categoryPricePaletteSegments))));
        $usesRolePricePalette = \Illuminate\Support\Str::contains($categoryPricePaletteText, [
            'top-up-game',
            'top up game',
            'paket-data',
            'paket data',
            'kuota-murah',
            'kuota murah',
            'aplikasi-premium',
            'aplikasi premium',
        ]);
        $currentUserIsMemberForPrice = (! $isGuestCatalog) && (auth()->user()?->isMember() ?? false);
        $regularPriceTextColor = $usesRolePricePalette
            ? ($currentUserIsMemberForPrice ? '#8b5cf6' : '#5a76c8')
            : '#4bc6b9';
        $discountPriceTextColor = $usesRolePricePalette && $currentUserIsMemberForPrice
            ? '#8b5cf6'
            : '#5a76c8';
        $memberProductHoverClass = $currentUserIsMemberForPrice ? ' wiboost-member-product-card' : '';
        $memberFridayDiscountRibbonClass = $currentUserIsMemberForPrice && now()->isFriday() ? ' discount-corner-ribbon--member' : '';
        $forceEmoteLogoOnly = false;
        $forceTopUpGameProductEmoji = $rootCategorySlugForCards === 'top-up-game';
        $shouldShowCompactServiceDescription = false;
        $compactProductGridClass = 'grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 lg:grid-cols-4';
        $checkoutNonce = (string) \Illuminate\Support\Str::uuid();

        $socialServiceDetailUrlTemplate = $showSuntikSosmedForm
            ? route('user.order.ordersosmed-service', ['product' => '__PRODUCT_ID__'])
            : null;
        $socialServiceListUrl = $showSuntikSosmedForm
            ? route('user.order.ordersosmed-services', ['category' => $category->id])
            : null;
        $categoryUsesTargetEmoji = \App\Support\WiboostEmoji::shouldUseForSubcategory($category);
    @endphp

    <div id="page-product-list" class="transition-all duration-300 relative z-10">
        <div class="relative mb-10 grid grid-cols-[auto,minmax(0,1fr)] gap-x-4 gap-y-3 overflow-visible rounded-[2.5rem] border-4 border-white bg-white/95 p-5 text-left shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:gap-x-6 sm:p-6 md:gap-x-8 md:p-8">
            <a href="{{ $category->parent_id ? route($categoryRouteName, $category->parent->slug) : $categoryHomeUrl }}" aria-label="Kembali" class="col-start-1 row-start-1 flex h-9 w-9 shrink-0 items-center justify-center self-center justify-self-center rounded-xl border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition-transform hover:scale-105 hover:bg-[#e0fbfc] active:scale-95">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div class="col-start-2 row-start-1 inline-block self-center justify-self-start rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                Katalog
            </div>

            <div class="wiboost-header-logo-frame col-start-1 row-start-2 flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-[1.5rem] bg-[#f4f9ff] text-[#5a76c8] shadow-inner sm:h-24 sm:w-24">
                @if($categoryUsesTargetEmoji)
                    <span class="text-2xl leading-none drop-shadow-sm sm:text-3xl">{{ \App\Support\WiboostEmoji::forCategory($category) }}</span>
                @elseif($category->image)
                    <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="wiboost-header-logo-img drop-shadow-sm">
                @else
                    <span class="drop-shadow-sm">@include('partials.wiboost-icon', ['label' => $category->name, 'class' => 'h-10 w-10'])</span>
                @endif
            </div>

            <div class="relative z-10 col-start-2 row-start-2 min-w-0 self-center text-left">
                <h2 class="wiboost-order-header-title mb-2 font-black tracking-tight text-[#2b3a67] drop-shadow-sm">{{ $category->name }}</h2>
                @if($category->description)
                    <p class="wiboost-order-header-description inline-block rounded-2xl border-2 border-white bg-[#f8faff] p-3 text-left font-bold text-[#4a5f96] shadow-inner w-full md:w-auto md:p-4">
                        {!! nl2br(e($category->description)) !!}
                    </p>
                @else
                    <p class="wiboost-order-header-description font-bold text-[#8faaf3]">Pilih layanan di bawah ini lalu lanjutkan ke checkout.</p>
                @endif
            </div>
        </div>

        @if($showSuntikSosmedForm)
            <form action="{{ route('user.checkout.process') }}" method="POST" id="socialCheckoutForm" onsubmit="return showSocialLoadingBtn()" class="rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm p-6 shadow-xl shadow-[#bde0fe]/30 md:p-10 relative overflow-hidden">
                @csrf
                <input type="hidden" name="checkout_nonce" value="{{ $checkoutNonce }}">
                <input type="hidden" name="product_id" id="social_product_id" value="{{ old('product_id') }}">

                <div class="flex items-center gap-3 mb-6 border-b-4 border-dashed border-[#f4f9ff] pb-4">
                    <h3 class="text-2xl font-black text-[#2b3a67]">Detail Pesanan</h3>
                </div>

                <div class="mb-6">
                    <label class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Layanan <span class="text-[#ff6b6b]">*</span></label>
                    <div id="social_service_select_wrap" class="wiboost-service-select-wrap">
                        <select id="social_service_select" tabindex="-1" aria-hidden="true" class="wiboost-service-native-select">
                            <option value="">{{ $products->isEmpty() ? 'Memuat layanan...' : 'Pilih layanan...' }}</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ (string) old('product_id') === (string) $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" id="social_service_dropdown_button" class="wiboost-service-trigger" aria-haspopup="listbox" aria-expanded="false">
                            <span id="social_service_selected_text" class="wiboost-service-trigger-text">{{ $products->isEmpty() ? 'Memuat layanan...' : 'Pilih layanan...' }}</span>
                            <svg class="wiboost-service-select-arrow" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="social_service_dropdown_panel" class="wiboost-service-dropdown" role="listbox" aria-label="Pilih layanan"></div>
                    </div>
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
                        <div class="wiboost-money-field flex overflow-hidden rounded-[1.5rem] border-4 border-white bg-[#eef2f7] shadow-inner">
                            <span class="border-r-2 border-[#cbd7ea] px-5 py-4 font-black text-[#4a5f96] bg-[#e2e8f0]">Rp</span>
                            <input type="text" id="social_price_per_1000" readonly value="0" class="wiboost-money-input min-w-0 flex-1 appearance-none border-0 bg-transparent px-4 py-4 font-black text-[#4a5f96] shadow-none outline-none focus:border-0 focus:ring-0">
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
                    <label class="mb-2 ml-2 block text-xs font-black uppercase tracking-widest text-[#8faaf3]">Total Harga</label>
                    <div class="wiboost-money-field flex overflow-hidden rounded-[1.2rem] border-2 border-white bg-white shadow-sm">
                        <span class="px-5 py-3 font-black text-[#4bc6b9] text-xl bg-[#e6fff7]">Rp</span>
                        <input type="text" id="social_total_price" readonly value="0" class="wiboost-money-input min-w-0 flex-1 appearance-none border-0 bg-transparent px-4 py-3 font-black text-xl text-[#2b3a67] shadow-none outline-none focus:border-0 focus:ring-0">
                    </div>
                </div>

                <div class="relative mb-8 rounded-[2rem] border-4 border-white bg-white p-6 shadow-md shadow-[#bde0fe]/20">
                    <h4 class="mb-5 ml-2 text-xl font-black text-[#2b3a67]">Metode Pembayaran</h4>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label id="social_wallet_option" class="group relative flex cursor-pointer items-center gap-4 rounded-[1.5rem] border-4 border-white bg-gradient-to-r from-[#e0fbfc] to-[#f4f9ff] p-5 shadow-sm transition-all hover:border-[#bde0fe]">
                            <input type="radio" name="payment_method" value="wallet" class="peer sr-only" required>
                            <div class="h-6 w-6 shrink-0 rounded-full border-2 border-[#8faaf3] bg-white transition-all peer-checked:border-[7px] peer-checked:border-[#5a76c8]"></div>
                            <div class="flex-1">
                                <p class="text-base font-black text-[#2b3a67]">Saldo</p>
                                <p class="mt-1 text-sm font-bold text-[#5a76c8]">Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-[#5a76c8] drop-shadow-sm opacity-70 transition-transform group-hover:scale-110">@include('partials.wiboost-icon', ['name' => 'wallet', 'class' => 'h-8 w-8'])</div>
                            <div class="pointer-events-none absolute inset-0 rounded-[1.5rem] border-4 border-transparent transition-colors peer-checked:border-[#5a76c8]"></div>
                        </label>

                        <label id="social_qris_option" class="group relative flex cursor-pointer items-center gap-4 rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] p-5 shadow-sm transition-all hover:border-[#bde0fe]">
                            <input type="radio" name="payment_method" value="tripay" class="peer sr-only" required>
                            <div class="h-6 w-6 shrink-0 rounded-full border-2 border-[#8faaf3] bg-white transition-all peer-checked:border-[7px] peer-checked:border-[#5a76c8]"></div>
                            <div class="flex-1">
                                <p class="text-base font-black text-[#2b3a67]">QRIS</p>
                                <p class="mt-1 text-xs font-bold text-[#8faaf3]">Otomatis via QRIS</p>
                            </div>
                            <div class="text-[#5a76c8] drop-shadow-sm opacity-70 transition-transform group-hover:scale-110">@include('partials.wiboost-icon', ['name' => 'qris', 'class' => 'h-8 w-8'])</div>
                            <div class="pointer-events-none absolute inset-0 rounded-[1.5rem] border-4 border-transparent transition-colors peer-checked:border-[#5a76c8]"></div>
                        </label>
                    </div>
                    <p id="social_qris_minimum_note" class="mt-4 hidden rounded-[1.25rem] border-2 border-white bg-[#fff5eb] px-4 py-3 text-sm font-black text-amber-500 shadow-inner">
                        QRIS hanya tersedia untuk pesanan minimal Rp 1.000
                    </p>
                </div>

                <button type="submit" id="btnSocialCheckout" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#4bc6b9] py-5 text-xl font-black text-white shadow-xl shadow-[#4bc6b9]/40 transition-transform hover:bg-[#3ba398] active:scale-95">
                    Bayar Pesanan
                </button>
            </form>
        @else
        <div class="mb-6 flex items-center gap-3 pl-2">
            <h3 class="text-2xl font-black text-[#2b3a67]">Daftar Layanan</h3>
        </div>

        <div class="{{ $showCompactServiceCards ? $compactProductGridClass : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5' }}">
            @forelse($products as $product)
                @php
                    $isOutOfStock = in_array($product->process_type, ['account', 'number'], true) && ($product->available_stock ?? 0) <= 0;
                    $rawDescription = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $product->description)));
                    $checkoutDescription = $rawDescription;
                    $cardDescription = $showCompactServiceCards ? '' : $rawDescription;

                    $compactCardHasDescription = $showCompactServiceCards && $shouldShowCompactServiceDescription && $cardDescription !== '';
                    $compactCardMinHeight = $compactCardHasDescription ? 'min-h-[185px]' : 'min-h-[150px]';
                    $compactCardShape = $showCompactServiceCards ? ' wiboost-product-square' . ($compactCardHasDescription ? ' wiboost-product-square--with-description' : '') : '';
                    $cardBaseClass = $showCompactServiceCards
                        ? 'group relative flex h-full w-full flex-col items-stretch justify-stretch overflow-hidden rounded-[1.35rem] border-4 border-white bg-white/95 p-2 text-center shadow-lg shadow-[#bde0fe]/20 transition-all hover:scale-[1.02] hover:border-[#bde0fe] active:scale-95 ' . $compactCardMinHeight . ' sm:p-4' . $compactCardShape . $memberProductHoverClass
                        : 'group relative flex flex-col items-center justify-between w-full overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 backdrop-blur-sm p-6 text-center shadow-xl shadow-[#bde0fe]/20 transition-all hover:scale-[1.02] hover:border-[#bde0fe] active:scale-95 min-h-[230px]' . $memberProductHoverClass;
                    $disabledCardClass = $showCompactServiceCards
                        ? 'relative flex h-full w-full cursor-not-allowed flex-col items-stretch justify-stretch overflow-hidden rounded-[1.35rem] border-4 border-white bg-gray-50 p-2 text-center opacity-60 grayscale shadow-sm ' . $compactCardMinHeight . ' sm:p-4' . $compactCardShape
                        : 'relative flex flex-col items-center justify-between w-full cursor-not-allowed overflow-hidden rounded-[2rem] border-4 border-white bg-gray-50 p-6 text-center opacity-60 grayscale shadow-sm min-h-[230px]';
                    $logoBoxClass = $showCompactServiceCards
                        ? 'wiboost-compact-logo flex shrink-0 items-center justify-center overflow-hidden rounded-[1.5rem] bg-[#f4f9ff] text-4xl shadow-inner transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3'
                        : 'mb-5 flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-[1.5rem] bg-[#f4f9ff] text-5xl shadow-inner transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3';
                    $productLogoEmote = $forceTopUpGameProductEmoji
                        ? \App\Support\DigiflazzProductLogo::emoteForProduct($product)
                        : $product->display_logo_emote;
                    $productLogoMode = ($forceEmoteLogoOnly || ($forceTopUpGameProductEmoji && $productLogoEmote)) ? 'emote' : 'auto';
                @endphp

                <button
                    type="button"
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-price="{{ $product->selling_price }}"
                    data-original-price="{{ $product->original_selling_price }}"
                    data-discount-label="{{ $product->active_discount_label ?? '' }}"
                    data-description="{{ $checkoutDescription }}"
                    data-logo-image="{{ ($productLogoMode !== 'emote' && $product->display_logo_image) ? Storage::url($product->display_logo_image) : '' }}"
                    data-logo-emote="{{ $productLogoEmote ?: Str::upper(Str::substr($product->name, 0, 1)) }}"
                    data-logo-mode="{{ $productLogoMode }}"
                    data-checkout-fields='{{ json_encode($product->checkout_fields, JSON_UNESCAPED_UNICODE) }}'
                    @if(!$isOutOfStock && ! $isGuestCatalog)
                        onclick="goToCheckout(this)"
                        class="{{ $cardBaseClass }}"
                    @elseif(!$isOutOfStock && $isGuestCatalog)
                        onclick="window.location.href='{{ $loginUrl }}'"
                        class="{{ $cardBaseClass }}"
                    @else
                        disabled
                        class="{{ $disabledCardClass }}"
                    @endif
                >
                    @if($isOutOfStock)
                        <span class="discount-corner-ribbon discount-corner-ribbon--sold-out">
                            <span class="discount-corner-ribbon__text">
                                Habis
                            </span>
                        </span>
                    @elseif($product->has_active_discount)
                        <span class="discount-corner-ribbon{{ $memberFridayDiscountRibbonClass }}">
                            <span class="discount-corner-ribbon__text">
                                {{ $product->active_discount_label }}
                            </span>
                        </span>
                    @endif

                    <div class="flex w-full flex-col items-center {{ $showCompactServiceCards ? 'wiboost-compact-card-main justify-center py-1' : 'mb-4' }}">
                        @if($forceTopUpGameProductEmoji && $productLogoEmote)
                            <div class="{{ $logoBoxClass }} leading-none drop-shadow-sm" style="font-size: {{ $showCompactServiceCards ? '1.85rem' : '2.2rem' }} !important;">
                                {{ $productLogoEmote }}
                            </div>
                        @elseif(! $forceEmoteLogoOnly && $product->display_logo_image)
                            <div class="{{ $logoBoxClass }}">
                                <span class="hidden text-[#5a76c8] drop-shadow-sm">@include('partials.wiboost-icon', ['label' => $product->name, 'class' => 'h-10 w-10'])</span>
                                <img src="{{ Storage::url($product->display_logo_image) }}" alt="" class="wiboost-square-logo-img drop-shadow-sm" onerror="this.classList.add('hidden'); this.previousElementSibling.classList.remove('hidden');">
                            </div>
                        @elseif($product->display_logo_emote)
                            <div class="{{ $logoBoxClass }} text-[#5a76c8] drop-shadow-sm">
                                @include('partials.wiboost-icon', ['label' => $product->name, 'class' => $showCompactServiceCards ? 'h-10 w-10' : 'h-12 w-12'])
                            </div>
                        @else
                            <div class="{{ $logoBoxClass }} text-[9px] font-black text-[#8faaf3] uppercase">
                                {{ $product->process_type }}
                            </div>
                        @endif
                        <span class="wiboost-product-title block max-w-full break-words px-1 font-black text-[#2b3a67] transition-colors group-hover:text-[#5a76c8] {{ $showCompactServiceCards ? 'wiboost-compact-card-title' : 'text-lg leading-tight' }}">{{ $product->name }}</span>
                        @if($cardDescription !== '')
                            <p class="{{ $showCompactServiceCards ? 'mt-1 max-w-full break-words px-1 text-center text-[10px] font-bold leading-snug text-[#8faaf3] sm:mt-2' : 'mt-2 text-center text-xs font-bold text-[#8faaf3]' }}">{{ $cardDescription }}</p>
                        @endif
                    </div>

                    <div class="flex items-center justify-center w-full border-t-2 border-dashed border-[#f4f9ff] {{ $showCompactServiceCards ? 'wiboost-compact-price-zone shrink-0 flex-col gap-1 py-2 sm:py-3' : 'mt-auto pt-4' }}">
                        @if($product->has_active_discount)
                            <div class="w-full text-center">
                                <span class="discount-old-price block text-xs font-black text-[#111827] line-through decoration-[#111827] decoration-2 sm:text-base">Rp {{ number_format($product->original_selling_price, 0, ',', '.') }}</span>
                                <span class="block shrink-0 font-black {{ $showCompactServiceCards ? 'text-sm sm:text-base' : 'text-lg' }}" style="color: {{ $discountPriceTextColor }} !important;">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                            </div>
                        @else
                            <span class="shrink-0 font-black {{ $showCompactServiceCards ? 'text-sm sm:text-base' : 'text-lg' }}" style="color: {{ $regularPriceTextColor }} !important;">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </button>
            @empty
                <div class="col-span-full rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] bg-white py-20 text-center shadow-lg shadow-[#bde0fe]/20">
                    <p class="text-2xl font-black text-[#5a76c8]">Layanan sedang kosong.</p>
                </div>
            @endforelse
        </div>
        @endif
    </div>

    <div id="page-checkout" class="hidden transition-all duration-300 relative z-10">
        <div class="relative z-10 mb-8 overflow-visible rounded-[2.5rem] border-4 border-white bg-white/95 p-5 text-left shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:p-6 md:p-8">
            <div class="relative z-10 min-w-0 flex-1 text-left">
                <div class="mb-3 flex items-center justify-start gap-2">
                    <button type="button" onclick="goBackToProducts()" aria-label="Kembali" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm transition-transform hover:scale-105 hover:bg-[#e0fbfc] active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <div class="inline-block rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                        Checkout
                    </div>
                </div>
                <h2 class="text-3xl font-black tracking-tight text-[#2b3a67] drop-shadow-sm md:text-4xl">Pesanan Anda</h2>
                <p class="mt-2 text-sm font-bold leading-relaxed text-[#8faaf3] md:text-base">
                    Lengkapi data sesuai layanan yang dipilih lalu pilih metode pembayaran.
                </p>
            </div>
        </div>

        <form action="{{ route('user.checkout.process') }}" method="POST" id="checkoutForm" onsubmit="return showLoadingBtn()">
            @csrf
            <input type="hidden" name="checkout_nonce" value="{{ $checkoutNonce }}">
            <input type="hidden" name="product_id" id="checkout_product_id">

            <div class="relative mb-8 flex flex-col justify-between gap-6 overflow-hidden rounded-[2.5rem] border-4 border-white bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] p-8 text-white shadow-xl shadow-[#bde0fe]/40 md:flex-row md:items-center">
                <div class="relative z-10 flex flex-1 items-center gap-5">
                    <div id="checkout_product_logo_box" class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-[1.5rem] bg-[#f4f9ff] text-[#5a76c8] shadow-inner">
                        <span id="checkout_product_logo_emote" class="text-4xl font-black leading-none drop-shadow-sm">W</span>
                        <img id="checkout_product_logo_image" src="" alt="" class="hidden wiboost-square-logo-img drop-shadow-sm">
                    </div>
                    <div>
                        <span class="mb-3 inline-block rounded-full bg-white/20 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-white shadow-inner border border-white/50">Layanan Terpilih</span>
                        <h3 class="text-2xl md:text-3xl font-black drop-shadow-md leading-tight" id="checkout_product_name">Nama Produk</h3>
                        <p id="checkout_product_description" class="mt-3 hidden max-w-2xl text-sm font-bold leading-relaxed text-white/90"></p>
                    </div>
                </div>

                <div class="relative z-10 shrink-0 overflow-hidden rounded-[2rem] border-4 border-white/50 bg-white/20 px-6 py-5 text-left backdrop-blur-md shadow-lg">
                    <span id="checkout_discount_badge" class="discount-corner-ribbon discount-corner-ribbon--checkout{{ $memberFridayDiscountRibbonClass }} hidden">
                        <span
                            id="checkout_discount_badge_text"
                            class="discount-corner-ribbon__text"
                        >Diskon 3%</span>
                    </span>
                    <div class="text-left">
                        <p class="mb-1 text-base font-black uppercase tracking-widest text-[#e0fbfc]">Total Harga</p>
                        <p class="discount-old-price-light hidden text-2xl font-black text-white/75 line-through decoration-white/80 decoration-2" id="checkout_original_price">Rp 0</p>
                        <p class="text-4xl font-black text-white drop-shadow-md" id="checkout_product_price">Rp 0</p>
                    </div>
                </div>
            </div>

            <div id="checkout_fields_section" class="relative mb-8 rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm p-6 shadow-xl shadow-[#bde0fe]/30 sm:p-8">
                <div class="checkout-step-heading mb-6">
                    <h4 class="min-w-0 text-2xl font-black text-[#2b3a67]">Informasi Buyer</h4>
                </div>
                <div id="checkout_fields_wrapper" class="space-y-6"></div>
            </div>

            <div id="no_target_msg" class="relative mb-8 hidden items-center gap-5 rounded-[2.5rem] border-4 border-white bg-[#e6fff7] p-8 shadow-xl shadow-emerald-100/50">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl border-2 border-white bg-emerald-100 text-emerald-500 shadow-sm sm:h-10 sm:w-10">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="3.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h4 class="text-xl font-black text-emerald-500 mb-1">Tidak Perlu Isi Form</h4>
                    <p class="text-sm font-bold leading-relaxed text-[#4bc6b9]">Tidak perlu isi form untuk layanan ini, kamu tidak perlu mengisi data tambahan. Detail akun akan diproses otomatis oleh sistem secara instan.</p>
                </div>
            </div>

            <div class="relative mb-8 rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm p-6 shadow-xl shadow-[#bde0fe]/30 sm:p-8">
                <div class="checkout-step-heading mb-6">
                    <h4 class="min-w-0 text-2xl font-black text-[#2b3a67]">Metode Pembayaran</h4>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <label id="checkout_wallet_option" class="group relative flex cursor-pointer items-center gap-4 rounded-[2rem] border-4 border-white bg-gradient-to-r from-[#e0fbfc] to-[#f4f9ff] p-6 shadow-md transition-transform hover:scale-[1.02]">
                        <input type="radio" name="payment_method" value="wallet" class="peer sr-only" required>
                        <div class="h-7 w-7 shrink-0 rounded-full border-2 border-[#8faaf3] bg-white transition-all peer-checked:border-[8px] peer-checked:border-[#5a76c8]"></div>
                        <div class="flex-1">
                            <p class="text-xl font-black text-[#2b3a67]">Saldo</p>
                            <p class="mt-1 text-sm font-bold text-[#5a76c8]">Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-[#5a76c8] drop-shadow-sm opacity-70 transition-transform group-hover:scale-110">@include('partials.wiboost-icon', ['name' => 'wallet', 'class' => 'h-10 w-10'])</div>
                        <div class="pointer-events-none absolute inset-0 rounded-[2rem] border-4 border-transparent transition-colors peer-checked:border-[#5a76c8]"></div>
                    </label>

                    <label id="checkout_qris_option" class="group relative flex cursor-pointer items-center gap-4 rounded-[2rem] border-4 border-white bg-[#f4f9ff] p-6 shadow-md transition-transform hover:scale-[1.02]">
                        <input type="radio" name="payment_method" value="tripay" class="peer sr-only" required>
                        <div class="h-7 w-7 shrink-0 rounded-full border-2 border-[#8faaf3] bg-white transition-all peer-checked:border-[8px] peer-checked:border-[#5a76c8]"></div>
                        <div class="flex-1">
                            <p class="text-xl font-black text-[#2b3a67]">QRIS</p>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">Otomatis via QRIS</p>
                        </div>
                        <div class="text-[#5a76c8] drop-shadow-sm opacity-70 transition-transform group-hover:scale-110">@include('partials.wiboost-icon', ['name' => 'qris', 'class' => 'h-10 w-10'])</div>
                        <div class="pointer-events-none absolute inset-0 rounded-[2rem] border-4 border-transparent transition-colors peer-checked:border-[#5a76c8]"></div>
                    </label>
                </div>
                <p id="checkout_qris_minimum_note" class="mt-4 hidden rounded-[1.5rem] border-2 border-white bg-[#fff5eb] px-5 py-4 text-sm font-black text-amber-500 shadow-inner">
                    QRIS hanya tersedia untuk pesanan minimal Rp 1.000
                </p>
            </div>

            <button type="submit" id="btnCheckout" class="flex w-full items-center justify-center gap-3 rounded-full border-4 border-white bg-[#4bc6b9] py-5 text-2xl font-black text-white shadow-xl shadow-[#4bc6b9]/40 transition-transform hover:bg-[#3ba398] active:scale-95">
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
    const oldCheckoutInput = @json(old());
    const oldProductId = @json(old('product_id'));
    const shouldStartAtTop = !oldProductId && !window.location.hash;
    const socialServiceDetailUrlTemplate = @json($socialServiceDetailUrlTemplate);
    const socialServiceListUrl = @json($socialServiceListUrl);
    const socialServiceCache = new Map();
    let socialServicesLoaded = false;
    let socialServiceDropdownRendered = false;
    const socialServiceDropdownOptions = new Map();
    let socialServiceDropdownRows = [];
    const socialServiceDropdownBatchSize = 80;
    let socialServiceDropdownVisibleCount = socialServiceDropdownBatchSize;
    let currentSocialService = null;
    let socialServiceRequestId = 0;
    let currentSocialServiceLoading = false;
    const socialServiceSelect = document.getElementById('social_service_select');
    const socialServiceSelectWrap = document.getElementById('social_service_select_wrap');
    const socialServiceDropdownButton = document.getElementById('social_service_dropdown_button');
    const socialServiceSelectedText = document.getElementById('social_service_selected_text');
    const socialServiceDropdownPanel = document.getElementById('social_service_dropdown_panel');
    const socialQuantityInput = document.getElementById('social_order_quantity');
    const checkoutProductLogoImage = document.getElementById('checkout_product_logo_image');
    const checkoutProductLogoEmote = document.getElementById('checkout_product_logo_emote');
    const rupiahFormatter = new Intl.NumberFormat('id-ID');

    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    function forceOrderPageTop() {
        if (shouldStartAtTop) {
            window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
        }
    }

    function userNavbarOffset() {
        const navbar = document.querySelector('.wiboost-user-navbar');
        return navbar ? Math.ceil(navbar.getBoundingClientRect().height + 16) : 120;
    }

    forceOrderPageTop();
    window.addEventListener('pageshow', forceOrderPageTop);

    function syncMinimumQrisOption(prefix, total) {
        const qrisOption = document.getElementById(prefix + '_qris_option');
        const qrisNote = document.getElementById(prefix + '_qris_minimum_note');

        if (!qrisOption) {
            return;
        }

        const form = qrisOption.closest('form');
        const qrisInput = qrisOption.querySelector('input[value="tripay"]');
        const walletInput = form?.querySelector('input[value="wallet"]');
        const shouldLockQris = Number(total) > 0 && Number(total) < 1000;

        if (qrisInput) {
            qrisInput.disabled = shouldLockQris;
            if (shouldLockQris && walletInput) {
                qrisInput.checked = false;
                walletInput.checked = true;
            }
        }

        qrisOption.classList.toggle('opacity-50', shouldLockQris);
        qrisOption.classList.toggle('cursor-not-allowed', shouldLockQris);
        qrisOption.classList.toggle('pointer-events-none', shouldLockQris);

        if (qrisNote) {
            qrisNote.classList.toggle('hidden', !shouldLockQris);
        }
    }

    function selectedSocialServiceId() {
        return socialServiceSelect ? String(socialServiceSelect.value || '') : '';
    }

    function selectedSocialServiceLabel() {
        if (!socialServiceSelect) {
            return 'Pilih layanan...';
        }

        return socialServiceSelect.options[socialServiceSelect.selectedIndex]?.textContent?.trim() || 'Pilih layanan...';
    }

    function syncSocialServiceDropdownLabel() {
        if (socialServiceSelectedText) {
            socialServiceSelectedText.textContent = selectedSocialServiceLabel();
        }

        if (socialServiceDropdownButton) {
            socialServiceDropdownButton.disabled = Boolean(socialServiceSelect?.disabled);
        }
    }

    function syncSocialServiceDropdownSelection() {
        if (!socialServiceDropdownPanel) {
            return;
        }

        const selectedId = selectedSocialServiceId();

        socialServiceDropdownOptions.forEach((item, value) => {
            const isSelected = value === selectedId;
            item.classList.toggle('is-selected', isSelected);
            item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
        });

        syncSocialServiceDropdownLabel();
    }

    function socialServiceOptionRows() {
        if (!socialServiceSelect) {
            return [];
        }

        if (socialServiceDropdownRows.length > 0) {
            return socialServiceDropdownRows;
        }

        socialServiceDropdownRows = Array.from(socialServiceSelect.options).map((option) => ({
            value: option.value,
            label: option.textContent || '-',
        }));

        return socialServiceDropdownRows;
    }

    function renderSocialServiceDropdown() {
        if (!socialServiceDropdownPanel || !socialServiceSelect) {
            return;
        }

        const selectedId = selectedSocialServiceId();
        const rows = socialServiceOptionRows();
        const visibleRows = rows.slice(0, socialServiceDropdownVisibleCount);
        socialServiceDropdownPanel.innerHTML = '';
        socialServiceDropdownOptions.clear();

        const fragment = document.createDocumentFragment();

        visibleRows.forEach((option) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'wiboost-service-option';
            item.dataset.value = option.value;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', option.value === selectedId ? 'true' : 'false');

            if (option.value === selectedId) {
                item.classList.add('is-selected');
            }

            const label = document.createElement('span');
            label.className = 'wiboost-service-option-label';
            label.textContent = option.label;

            const check = document.createElement('span');
            check.className = 'wiboost-service-option-check';
            check.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>';

            item.appendChild(label);
            item.appendChild(check);

            socialServiceDropdownOptions.set(option.value, item);
            fragment.appendChild(item);
        });

        if (visibleRows.length < rows.length) {
            const loadMore = document.createElement('span');
            loadMore.className = 'wiboost-service-load-more';
            loadMore.textContent = 'Scroll untuk memuat layanan lainnya';
            fragment.appendChild(loadMore);
        }

        socialServiceDropdownPanel.appendChild(fragment);
        socialServiceDropdownRendered = true;
        syncSocialServiceDropdownSelection();
    }

    function appendMoreSocialServiceOptions() {
        if (!socialServiceDropdownPanel || !socialServiceSelect) {
            return;
        }

        const rows = socialServiceOptionRows();
        if (socialServiceDropdownVisibleCount >= rows.length) {
            return;
        }

        const previousScrollTop = socialServiceDropdownPanel.scrollTop;
        socialServiceDropdownVisibleCount = Math.min(rows.length, socialServiceDropdownVisibleCount + socialServiceDropdownBatchSize);
        renderSocialServiceDropdown();
        socialServiceDropdownPanel.scrollTop = previousScrollTop;
    }

    function setSocialServiceSelectOpen(isOpen) {
        const shouldOpen = Boolean(isOpen) && !socialServiceSelect?.disabled;
        socialServiceSelectWrap?.classList.toggle('is-open', shouldOpen);
        socialServiceDropdownButton?.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');

        if (shouldOpen) {
            if (!socialServiceDropdownRendered) {
                socialServiceDropdownVisibleCount = socialServiceDropdownBatchSize;
                renderSocialServiceDropdown();
            } else {
                syncSocialServiceDropdownSelection();
            }
        }
    }

    function socialServiceDetailUrl(productId) {
        if (!socialServiceDetailUrlTemplate || !productId) {
            return null;
        }

        return socialServiceDetailUrlTemplate.replace('__PRODUCT_ID__', encodeURIComponent(productId));
    }

    async function loadSocialServices() {
        if (!socialServiceSelect || !socialServiceListUrl || socialServicesLoaded) {
            return;
        }

        socialServiceSelect.disabled = true;
        socialServiceSelect.innerHTML = '<option value="">Memuat layanan...</option>';
        socialServiceDropdownRendered = false;
        socialServiceDropdownRows = [{ value: '', label: 'Memuat layanan...' }];
        socialServiceDropdownVisibleCount = socialServiceDropdownBatchSize;
        if (socialServiceDropdownPanel) {
            socialServiceDropdownPanel.innerHTML = '';
        }
        syncSocialServiceDropdownLabel();

        try {
            const response = await fetch(socialServiceListUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const payload = await response.json();
            const selectedId = String(oldProductId || '');
            const services = Array.isArray(payload.services) ? payload.services : [];
            const options = ['<option value="">Pilih layanan...</option>'];

            services.forEach((service) => {
                const id = String(service.id || '');
                const option = document.createElement('option');
                option.value = id;
                option.textContent = service.name || '-';
                if (id === selectedId) {
                    option.selected = true;
                }
                options.push(option.outerHTML);
            });

            socialServiceSelect.innerHTML = options.join('');
            socialServiceDropdownRows = Array.from(socialServiceSelect.options).map((option) => ({
                value: option.value,
                label: option.textContent || '-',
            }));
            socialServicesLoaded = true;
            socialServiceDropdownRendered = false;
            socialServiceDropdownVisibleCount = socialServiceDropdownBatchSize;
        } catch (error) {
            socialServiceSelect.innerHTML = '<option value="">Gagal memuat layanan. Refresh halaman.</option>';
            socialServiceDropdownRows = [{ value: '', label: 'Gagal memuat layanan. Refresh halaman.' }];
            socialServiceDropdownRendered = false;
            socialServiceDropdownVisibleCount = socialServiceDropdownBatchSize;
        } finally {
            socialServiceSelect.disabled = false;
            syncSocialServiceDropdownLabel();
        }
    }

    function socialField(id) {
        return document.getElementById(id);
    }

    function findSocialService() {
        return currentSocialService;
    }

    function setSocialServiceFields(service, state = 'ready') {
        const isLoading = state === 'loading';
        const selectedId = selectedSocialServiceId();
        const productInput = socialField('social_product_id');

        if (productInput) {
            productInput.value = service ? service.id : selectedId;
        }

        const descriptionField = socialField('social_description');
        const minField = socialField('social_min');
        const maxField = socialField('social_max');
        const priceField = socialField('social_price_per_1000');
        const averageField = socialField('social_average_time');

        if (descriptionField) {
            descriptionField.value = isLoading ? 'Memuat detail layanan...' : (service ? service.description : '-');
        }

        if (minField) {
            minField.value = isLoading ? '...' : (service ? rupiahFormatter.format(service.min) : '0');
        }

        if (maxField) {
            maxField.value = isLoading ? '...' : (service ? rupiahFormatter.format(service.max) : '0');
        }

        if (priceField) {
            priceField.value = isLoading ? '...' : (service ? rupiahFormatter.format(Math.ceil(service.price_per_1000)) : '0');
        }

        if (averageField) {
            averageField.value = isLoading ? 'Memuat...' : (service ? service.average_time : 'Pilih layanan dulu');
        }

        if (socialQuantityInput) {
            if (service) {
                socialQuantityInput.disabled = false;
                socialQuantityInput.min = service.min;
                socialQuantityInput.max = service.max;
                socialQuantityInput.placeholder = '';
            } else {
                socialQuantityInput.disabled = isLoading;
                socialQuantityInput.removeAttribute('min');
                socialQuantityInput.removeAttribute('max');
                socialQuantityInput.placeholder = isLoading ? 'Tunggu detail layanan...' : '';
            }
        }

        updateSocialTotal();
    }

    async function syncSocialService() {
        const selectedId = selectedSocialServiceId();
        currentSocialService = null;
        currentSocialServiceLoading = false;

        if (!selectedId || !socialServiceDetailUrlTemplate) {
            setSocialServiceFields(null);
            return;
        }

        if (socialServiceCache.has(selectedId)) {
            currentSocialService = socialServiceCache.get(selectedId);
            setSocialServiceFields(currentSocialService);
            return;
        }

        const detailUrl = socialServiceDetailUrl(selectedId);
        if (!detailUrl) {
            setSocialServiceFields(null);
            return;
        }

        const requestId = ++socialServiceRequestId;
        currentSocialServiceLoading = true;
        setSocialServiceFields(null, 'loading');

        try {
            const response = await fetch(detailUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const service = await response.json();

            if (requestId !== socialServiceRequestId || selectedSocialServiceId() !== selectedId) {
                return;
            }

            socialServiceCache.set(selectedId, service);
            currentSocialService = service;
            currentSocialServiceLoading = false;
            setSocialServiceFields(service);
        } catch (error) {
            if (requestId !== socialServiceRequestId) {
                return;
            }

            currentSocialServiceLoading = false;
            setSocialServiceFields(null);

            const descriptionField = socialField('social_description');
            if (descriptionField) {
                descriptionField.value = 'Gagal memuat detail layanan. Pilih ulang layanan atau refresh halaman.';
            }
        }
    }

    function updateSocialTotal() {
        const service = findSocialService();
        const quantity = parseInt(socialQuantityInput?.value || '0', 10);
        const total = service && quantity > 0
            ? Math.max(1, Math.ceil(quantity * service.unit_price))
            : 0;

        const totalInput = socialField('social_total_price');
        if (totalInput) {
            totalInput.value = rupiahFormatter.format(total);
        }

        syncMinimumQrisOption('social', total);
    }

    function showSocialLoadingBtn() {
        const form = document.getElementById('socialCheckoutForm');
        const btn = document.getElementById('btnSocialCheckout');

        if (form?.dataset.submitted === '1') {
            return false;
        }

        if (socialServiceSelect && !selectedSocialServiceId()) {
            socialServiceSelectWrap?.classList.add('has-error');
            setSocialServiceSelectOpen(true);
            socialServiceDropdownButton?.focus();
            return false;
        }

        if (socialServiceSelect && selectedSocialServiceId() && (!currentSocialService || currentSocialServiceLoading)) {
            syncSocialService();
            return false;
        }

        if (form) {
            form.dataset.submitted = '1';
        }

        if (!btn) {
            return true;
        }

        btn.disabled = true;
        const selectedPayment = form?.querySelector('input[name="payment_method"]:checked')?.value;
        btn.classList.add('cursor-not-allowed', 'opacity-70');

        return true;
    }

    function checkoutInputClass() {
        return 'w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] placeholder-[#a3bbfb]';
    }

    function resetCheckoutLogo() {
        if (!checkoutProductLogoImage || !checkoutProductLogoEmote) {
            return;
        }

        checkoutProductLogoImage.onerror = null;
        checkoutProductLogoImage.onload = null;
        checkoutProductLogoImage.removeAttribute('src');
        checkoutProductLogoImage.style.display = 'none';
        checkoutProductLogoImage.classList.add('hidden');
        checkoutProductLogoEmote.textContent = 'W';
        checkoutProductLogoEmote.style.display = '';
        checkoutProductLogoEmote.classList.remove('hidden');
    }

    function showCheckoutLogoFallback(logoEmote, fallbackName = 'W') {
        if (!checkoutProductLogoImage || !checkoutProductLogoEmote) {
            return;
        }

        checkoutProductLogoImage.classList.add('hidden');
        checkoutProductLogoImage.removeAttribute('src');
        checkoutProductLogoImage.style.display = 'none';
        checkoutProductLogoEmote.style.display = '';
        checkoutProductLogoEmote.classList.remove('hidden');
        checkoutProductLogoEmote.textContent = logoEmote !== '' ? logoEmote : fallbackName.trim().charAt(0).toUpperCase();
    }

    function syncCheckoutLogo(button) {
        if (!checkoutProductLogoImage || !checkoutProductLogoEmote) {
            return;
        }

        const logoImage = (button.dataset.logoImage || '').trim();
        const logoEmote = (button.dataset.logoEmote || '').trim();
        const logoMode = (button.dataset.logoMode || 'auto').trim();

        checkoutProductLogoImage.onerror = null;
        checkoutProductLogoImage.onload = null;

        if (logoMode === 'emote') {
            showCheckoutLogoFallback(logoEmote, button.dataset.name || 'W');
            return;
        }

        if (logoImage !== '') {
            checkoutProductLogoImage.onerror = () => showCheckoutLogoFallback(logoEmote, button.dataset.name || 'W');
            checkoutProductLogoImage.onload = () => {
                checkoutProductLogoImage.style.display = '';
                checkoutProductLogoImage.classList.remove('hidden');
                checkoutProductLogoEmote.style.display = 'none';
                checkoutProductLogoEmote.classList.add('hidden');
            };
            checkoutProductLogoImage.src = logoImage;
            checkoutProductLogoImage.alt = '';
            checkoutProductLogoImage.style.display = '';
            checkoutProductLogoImage.classList.remove('hidden');
            checkoutProductLogoEmote.style.display = 'none';
            checkoutProductLogoEmote.classList.add('hidden');
            return;
        }

        showCheckoutLogoFallback(logoEmote, button.dataset.name || 'W');
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
        const { id, name, price, originalPrice, discountLabel, description } = button.dataset;
        const fields = JSON.parse(button.dataset.checkoutFields || '[]');
        const descriptionNode = document.getElementById('checkout_product_description');
        const originalPriceNode = document.getElementById('checkout_original_price');
        const discountBadgeNode = document.getElementById('checkout_discount_badge');
        const hasDiscount = discountLabel && Number(originalPrice) > Number(price);

        document.getElementById('checkout_product_id').value = id;
        document.getElementById('checkout_product_name').innerText = name;
        document.getElementById('checkout_product_price').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);

        if (originalPriceNode) {
            originalPriceNode.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(originalPrice || 0);
            originalPriceNode.classList.toggle('hidden', !hasDiscount);
        }

        if (discountBadgeNode) {
            const discountBadgeTextNode = document.getElementById('checkout_discount_badge_text');
            if (discountBadgeTextNode) {
                discountBadgeTextNode.innerText = discountLabel || '';
            }
            discountBadgeNode.classList.toggle('hidden', !hasDiscount);
            discountBadgeNode.classList.toggle('inline-block', hasDiscount);
        }

        syncMinimumQrisOption('checkout', Number(price));
        syncCheckoutLogo(button);

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
        } else {
            renderCheckoutFields(fields, restoreValues);
            checkoutFieldsSection.classList.remove('hidden');
            noTargetMsg.classList.add('hidden');
            noTargetMsg.classList.remove('flex');
        }

        pageProductList.classList.add('hidden');
        pageCheckout.classList.remove('hidden');
        pageCheckout.classList.add('fade-in');

        window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
    }

    function goBackToProducts() {
        pageCheckout.classList.add('hidden');
        pageProductList.classList.remove('hidden');
        pageProductList.classList.add('fade-in');
        document.getElementById('checkoutForm').reset();
        checkoutFieldsWrapper.innerHTML = '';
        resetCheckoutLogo();
        document.getElementById('checkout_original_price')?.classList.add('hidden');
        const checkoutDiscountBadge = document.getElementById('checkout_discount_badge');
        checkoutDiscountBadge?.classList.add('hidden');
        checkoutDiscountBadge?.classList.remove('inline-block');

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showLoadingBtn() {
        const form = document.getElementById('checkoutForm');
        const btn = document.getElementById('btnCheckout');

        if (form?.dataset.submitted === '1') {
            return false;
        }

        if (form) {
            form.dataset.submitted = '1';
        }

        btn.disabled = true;
        const selectedPayment = form?.querySelector('input[name="payment_method"]:checked')?.value;
        btn.classList.add('cursor-not-allowed', 'opacity-70');

        return true;
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (socialServiceSelect) {
            socialServiceDropdownButton?.addEventListener('click', function () {
                setSocialServiceSelectOpen(!socialServiceSelectWrap?.classList.contains('is-open'));
            });

            socialServiceDropdownButton?.addEventListener('keydown', function (event) {
                if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
                    event.preventDefault();
                    setSocialServiceSelectOpen(true);
                    socialServiceDropdownPanel?.querySelector('.wiboost-service-option')?.focus();
                }

                if (event.key === 'Escape') {
                    setSocialServiceSelectOpen(false);
                }
            });

            socialServiceDropdownPanel?.addEventListener('keydown', function (event) {
                const options = Array.from(socialServiceDropdownPanel.querySelectorAll('.wiboost-service-option'));
                const currentIndex = options.indexOf(document.activeElement);

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    options[Math.min(currentIndex + 1, options.length - 1)]?.focus();
                }

                if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    options[Math.max(currentIndex - 1, 0)]?.focus();
                }

                if (event.key === 'Escape') {
                    event.preventDefault();
                    setSocialServiceSelectOpen(false);
                    socialServiceDropdownButton?.focus();
                }
            });

            socialServiceDropdownPanel?.addEventListener('click', function (event) {
                const item = event.target instanceof Element ? event.target.closest('.wiboost-service-option') : null;
                if (!item || socialServiceSelect.disabled) {
                    return;
                }

                socialServiceSelect.value = item.dataset.value || '';
                socialServiceSelect.dispatchEvent(new Event('change', { bubbles: true }));
                socialServiceDropdownButton?.focus();
            });

            socialServiceDropdownPanel?.addEventListener('scroll', function () {
                const distanceToBottom = socialServiceDropdownPanel.scrollHeight - socialServiceDropdownPanel.scrollTop - socialServiceDropdownPanel.clientHeight;
                if (distanceToBottom < 120) {
                    appendMoreSocialServiceOptions();
                }
            }, { passive: true });

            socialServiceSelect.addEventListener('change', function () {
                socialServiceSelectWrap?.classList.remove('has-error');
                syncSocialServiceDropdownSelection();
                setSocialServiceSelectOpen(false);
                syncSocialService();
            });
            document.addEventListener('pointerdown', function (event) {
                if (socialServiceSelectWrap && !socialServiceSelectWrap.contains(event.target)) {
                    setSocialServiceSelectOpen(false);
                }
            });
            socialQuantityInput?.addEventListener('input', updateSocialTotal);
            loadSocialServices().then(syncSocialService);
        }

        if (shouldStartAtTop) {
            requestAnimationFrame(forceOrderPageTop);
            setTimeout(forceOrderPageTop, 120);
            return;
        }

        const productButton = document.querySelector(`[data-id="${oldProductId}"]`);
        if (productButton) {
            goToCheckout(productButton, oldCheckoutInput || {});
        }
    });
</script>
@endsection
