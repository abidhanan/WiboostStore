@extends('layouts.user')

@section('title', 'Order - ' . $category->name)

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    
    .fade-in { animation: fadeIn 0.4s ease-in-out forwards; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="wiboost-font pb-24 max-w-4xl mx-auto mt-4 px-4 overflow-hidden relative">

    @if(session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
            <span class="text-2xl">⚠️</span> {{ session('error') }}
        </div>
    @endif

    <div id="page-product-list" class="transition-all duration-300">
        
        <div class="bg-white rounded-[2.5rem] p-6 md:p-10 shadow-lg shadow-[#bde0fe]/30 border-4 border-white mb-10 relative overflow-hidden flex flex-col md:flex-row items-center gap-6 md:gap-10">
            <div class="absolute -right-10 -bottom-10 text-8xl opacity-10 pointer-events-none">✨</div>
            <div class="absolute top-5 right-1/4 text-4xl opacity-20 pointer-events-none">☁️</div>

            <div class="flex items-center gap-6 w-full md:w-auto relative z-10">
                <a href="{{ $category->parent_id ? route('user.order.category', $category->parent->slug) : route('user.dashboard') }}" class="w-12 h-12 bg-[#f0f5ff] rounded-2xl flex items-center justify-center text-[#5a76c8] hover:bg-[#e0fbfc] hover:-translate-y-1 transition-all border-2 border-white shadow-inner shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                
                <div class="w-24 h-24 bg-gradient-to-br from-[#f0f5ff] to-[#e0ebff] rounded-[1.5rem] border-4 border-white shadow-md flex items-center justify-center text-[#5a76c8] shrink-0 overflow-hidden">
                    @if($category->image)
                        <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-5xl">{{ $category->emote ?? '✨' }}</span>
                    @endif
                </div>
            </div>

            <div class="flex-1 text-center md:text-left relative z-10">
                <h2 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight mb-2">{{ $category->name }}</h2>
                @if($category->description)
                    <p class="text-[#4a5f96] font-bold text-sm md:text-base leading-relaxed bg-[#f4f9ff] p-4 rounded-2xl border-2 border-white shadow-inner inline-block text-left">
                        {!! nl2br(e($category->description)) !!}
                    </p>
                @else
                    <p class="text-[#8faaf3] font-bold">Pilih layanan di bawah ini dan selesaikan pesananmu.</p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3 mb-6 pl-2">
            <span class="text-2xl">🛍️</span>
            <h3 class="text-2xl font-black text-[#2b3a67]">Daftar Layanan</h3>
        </div>

        <div class="flex flex-col gap-3">
            @forelse($products as $product)
                @php
                    // Logika deteksi stok habis (Hanya untuk tipe Account & Number)
                    $isOutOfStock = in_array($product->process_type, ['account', 'number']) && $product->available_stock <= 0;
                @endphp

                <button type="button" 
                    @if(!$isOutOfStock)
                        onclick="goToCheckout({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }}, '{{ $product->process_type }}')" 
                        class="w-full bg-white rounded-[1.5rem] p-4 md:p-5 border-4 border-white hover:border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20 flex items-center gap-4 transition-all active:scale-95 text-left group"
                    @else
                        disabled
                        class="w-full bg-gray-50 rounded-[1.5rem] p-4 md:p-5 border-4 border-white shadow-sm flex items-center gap-4 text-left opacity-60 cursor-not-allowed grayscale relative overflow-hidden"
                    @endif
                >
                    
                    @if($product->image && $product->process_type == 'number')
                        <div class="w-10 h-10 md:w-12 md:h-12 shrink-0 rounded-xl overflow-hidden border-2 border-[#f0f5ff] shadow-sm bg-[#f4f9ff]">
                            <img src="{{ Storage::url($product->image) }}" alt="Logo" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-10 h-10 md:w-12 md:h-12 shrink-0 rounded-xl bg-[#f0f5ff] text-[#8faaf3] flex items-center justify-center border-2 border-white shadow-inner text-xl">
                            @if($product->process_type == 'number') 📱 
                            @elseif($product->process_type == 'account') 📦
                            @elseif($product->process_type == 'api') ⚡
                            @else 🛍️ @endif
                        </div>
                    @endif

                    <span class="font-black text-[#2b3a67] text-sm md:text-lg {{ !$isOutOfStock ? 'group-hover:text-[#5a76c8]' : '' }} pr-2 flex-1 leading-tight">{{ $product->name }}</span>
                    
                    @if($isOutOfStock)
                        <span class="font-black text-[#ff6b6b] text-xs md:text-sm shrink-0 bg-[#ffe5e5] px-3 py-1.5 rounded-lg border border-white shadow-sm flex items-center gap-1">
                            ⚠️ HABIS
                        </span>
                    @else
                        <span class="font-black text-[#4bc6b9] text-base md:text-xl shrink-0">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    @endif
                </button>
            @empty
                <div class="text-center py-16 bg-white rounded-[2.5rem] border-4 border-dashed border-[#bde0fe]">
                    <div class="text-6xl mb-4">📭</div>
                    <p class="text-[#5a76c8] font-black text-xl">Layanan sedang kosong.</p>
                </div>
            @endforelse
        </div>
    </div>


    <div id="page-checkout" class="hidden transition-all duration-300">
        
        <button type="button" onclick="goBackToProducts()" class="mb-8 inline-flex items-center gap-2 text-[#5a76c8] font-black hover:text-[#4760a9] transition-colors bg-white px-5 py-3 rounded-full shadow-sm border-2 border-white hover:-translate-x-1 hover:shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            Kembali ke Daftar Layanan
        </button>

        <div class="mb-8 pl-2">
            <h2 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight">Checkout Pesanan 🛒</h2>
            <p class="text-[#8faaf3] font-bold mt-1">Lengkapi data tujuan dan pilih metode pembayaran.</p>
        </div>

        <form action="{{ route('user.checkout.process') }}" method="POST" id="checkoutForm" onsubmit="showLoadingBtn()">
            @csrf
            <input type="hidden" name="product_id" id="checkout_product_id">

            <div class="bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] rounded-[2rem] p-6 md:p-8 border-4 border-white shadow-lg shadow-[#bde0fe]/40 mb-8 relative overflow-hidden text-white flex flex-col md:flex-row justify-between md:items-center gap-4">
                <div class="absolute -right-4 -bottom-4 text-7xl opacity-20 transform rotate-12 pointer-events-none">📦</div>
                
                <div class="relative z-10">
                    <span class="bg-[#e0fbfc] text-[#5a76c8] text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest mb-3 inline-block shadow-sm">Layanan Terpilih</span>
                    <h3 class="font-black text-2xl md:text-3xl drop-shadow-sm" id="checkout_product_name">Nama Produk</h3>
                </div>
                
                <div class="relative z-10 bg-white/20 backdrop-blur-md px-6 py-4 rounded-[1.5rem] border-2 border-white/50 text-left md:text-right shrink-0">
                    <p class="text-xs font-bold text-[#e0fbfc] uppercase tracking-widest mb-1">Total Harga</p>
                    <p class="font-black text-2xl md:text-3xl drop-shadow-sm" id="checkout_product_price">Rp 0</p>
                </div>
            </div>

            <div id="target_data_container" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-8 mb-8 relative">
                <div class="absolute -left-3 -top-3 w-12 h-12 bg-[#5a76c8] text-white rounded-full flex items-center justify-center font-black text-xl border-4 border-[#f4f9ff] shadow-sm">1</div>
                <h4 class="text-xl font-black text-[#2b3a67] mb-6 ml-6">Informasi Target</h4>
                
                <div>
                    <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2" id="label_target_data">
                        @if(Str::contains(Str::lower($category->name), 'game'))
                            Target Pesanan (User ID & Zone ID)
                        @else
                            Target Pesanan (Username / Link Profile)
                        @endif
                    </label>
                    <input type="text" name="target_data" id="input_target_data" required 
                           class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" 
                           placeholder="Ketik target tujuan di sini...">
                </div>
            </div>
            
            <div id="no_target_msg" class="hidden bg-[#e6fff7] rounded-[2rem] shadow-lg shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-8 mb-8 relative flex items-center gap-4">
                <div class="text-5xl drop-shadow-sm shrink-0">🎁</div>
                <div>
                    <h4 class="font-black text-emerald-500 text-lg">Kamu Tidak Perlu Mengisi Apapun!</h4>
                    <p class="font-bold text-[#8faaf3] text-sm leading-tight mt-1">Data akun atau nomor akan otomatis dikirimkan ke halaman riwayat pesananmu setelah pembayaran lunas.</p>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-8 mb-8 relative">
                <div id="step_number_2" class="absolute -left-3 -top-3 w-12 h-12 bg-[#5a76c8] text-white rounded-full flex items-center justify-center font-black text-xl border-4 border-[#f4f9ff] shadow-sm">2</div>
                <h4 class="text-xl font-black text-[#2b3a67] mb-6 ml-6">Metode Pembayaran</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="relative bg-gradient-to-r from-[#e0fbfc] to-[#f4f9ff] rounded-[1.5rem] p-5 flex items-center gap-4 cursor-pointer border-4 border-white hover:border-[#bde0fe] shadow-sm transition-all group">
                        <input type="radio" name="payment_method" value="wallet" class="sr-only peer" required>
                        <div class="w-6 h-6 rounded-full border-2 border-[#8faaf3] peer-checked:border-[#5a76c8] peer-checked:border-[7px] transition-all bg-white shrink-0"></div>
                        <div class="flex-1">
                            <p class="font-black text-[#2b3a67] text-lg">Saldo Wiboost</p>
                            <p class="text-sm font-bold text-[#5a76c8] mt-1">Sisa: Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-3xl drop-shadow-sm group-hover:scale-110 transition-transform">💰</div>
                        <div class="absolute inset-0 border-4 border-transparent peer-checked:border-[#5a76c8] rounded-[1.5rem] pointer-events-none transition-colors"></div>
                    </label>

                    <label class="relative bg-[#f4f9ff] rounded-[1.5rem] p-5 flex items-center gap-4 cursor-pointer border-4 border-white hover:border-[#bde0fe] shadow-sm transition-all group">
                        <input type="radio" name="payment_method" value="manual" class="sr-only peer" required>
                        <div class="w-6 h-6 rounded-full border-2 border-[#8faaf3] peer-checked:border-[#5a76c8] peer-checked:border-[7px] transition-all bg-white shrink-0"></div>
                        <div class="flex-1">
                            <p class="font-black text-[#2b3a67] text-lg">E-Wallet & QRIS</p>
                            <p class="text-xs font-bold text-[#8faaf3] mt-1">Otomatis via Payment Gateway</p>
                        </div>
                        <div class="text-3xl drop-shadow-sm group-hover:scale-110 transition-transform">📱</div>
                        <div class="absolute inset-0 border-4 border-transparent peer-checked:border-[#5a76c8] rounded-[1.5rem] pointer-events-none transition-colors"></div>
                    </label>
                </div>
            </div>

            <button type="submit" id="btnCheckout" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-2xl py-5 rounded-[2rem] transition-transform active:scale-95 shadow-xl shadow-[#4bc6b9]/40 border-4 border-white flex justify-center items-center gap-3">
                Bayar Pesanan 🚀
            </button>
        </form>
    </div>
</div>

<script>
    const pageProductList = document.getElementById('page-product-list');
    const pageCheckout = document.getElementById('page-checkout');
    
    // Element Kontrol Target Data
    const targetContainer = document.getElementById('target_data_container');
    const inputTarget = document.getElementById('input_target_data');
    const noTargetMsg = document.getElementById('no_target_msg');
    const stepNumber2 = document.getElementById('step_number_2');

    // Menerima parameter process_type dari tombol
    function goToCheckout(id, name, price, processType) {
        document.getElementById('checkout_product_id').value = id;
        document.getElementById('checkout_product_name').innerText = name;
        document.getElementById('checkout_product_price').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);

        // LOGIKA PENYEMBUNYIAN INPUT TARGET
        if(processType === 'account' || processType === 'number') {
            // Sembunyikan input target & hapus attribute required
            targetContainer.classList.add('hidden');
            inputTarget.removeAttribute('required');
            inputTarget.value = ''; // Kosongkan nilainya
            
            // Tampilkan pesan pengganti & ubah bulatan langkah ke-2 jadi 1
            noTargetMsg.classList.remove('hidden');
            stepNumber2.innerText = '1';
        } else {
            // Tampilkan kembali input target & jadikan required lagi
            targetContainer.classList.remove('hidden');
            inputTarget.setAttribute('required', 'required');
            
            // Sembunyikan pesan pengganti & kembalikan bulatan langkah ke-2
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
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showLoadingBtn() {
        const btn = document.getElementById('btnCheckout');
        btn.innerHTML = 'Sedang Memproses... ⏳';
        btn.classList.add('opacity-70', 'cursor-not-allowed');
    }
</script>

@endsection