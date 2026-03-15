@extends('layouts.user')

@section('title', 'Order - ' . $category->name)

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-20 max-w-5xl mx-auto mt-4 px-4">
    
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
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                @endif
            </div>
        </div>

        <div class="flex-1 text-center md:text-left relative z-10">
            <h2 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight mb-2">{{ $category->name }}</h2>
            
            @if($category->description)
                <p class="text-[#4a5f96] font-bold text-sm md:text-base leading-relaxed bg-[#f4f9ff] p-4 rounded-2xl border-2 border-white shadow-inner inline-block">
                    {!! nl2br(e($category->description)) !!}
                </p>
            @else
                <p class="text-[#8faaf3] font-bold">Pilih layanan di bawah ini dan masukkan target pesananmu.</p>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
            <span class="text-2xl">⚠️</span> {{ session('error') }}
        </div>
    @endif

    <div class="flex items-center gap-3 mb-6 pl-2">
        <span class="text-2xl">🛍️</span>
        <h3 class="text-2xl font-black text-[#2b3a67]">Daftar Layanan</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($products as $product)
            <div class="bg-white rounded-[2rem] p-6 border-4 border-white shadow-lg shadow-[#bde0fe]/20 flex flex-col justify-between hover:border-[#bde0fe] hover:-translate-y-2 transition-all group relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-20 h-20 bg-gradient-to-br from-[#f0f5ff] to-transparent rounded-full opacity-50 pointer-events-none"></div>

                <div>
                    <h4 class="font-black text-[#2b3a67] text-lg leading-tight mb-4 group-hover:text-[#5a76c8] transition-colors pr-4">{{ $product->name }}</h4>
                </div>

                <div class="mt-4 pt-4 border-t-2 border-dashed border-[#f0f5ff] flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-0.5">Harga</p>
                        <p class="font-black text-[#4bc6b9] text-xl">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                    </div>
                    <button onclick="openCheckoutModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }})" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white px-5 py-2.5 rounded-full font-black text-sm transition-transform active:scale-95 shadow-md shadow-[#5a76c8]/30 border-2 border-white">
                        Beli &rarr;
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 bg-white rounded-[2.5rem] border-4 border-dashed border-[#bde0fe]">
                <div class="text-6xl mb-4">📭</div>
                <p class="text-[#5a76c8] font-black text-xl">Layanan sedang kosong.</p>
                <p class="text-[#8faaf3] font-bold mt-2">Admin sedang menyiapkan produk terbaik untukmu.</p>
            </div>
        @endforelse
    </div>
</div>

<div id="checkoutModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-[#2b3a67]/60 backdrop-blur-sm transition-opacity opacity-0" id="modalOverlay" onclick="closeCheckoutModal()"></div>
    
    <div class="bg-white rounded-[2.5rem] shadow-2xl border-4 border-white w-full max-w-lg relative z-10 transform scale-95 opacity-0 transition-all duration-300" id="modalContent">
        
        <div class="bg-gradient-to-r from-[#8faaf3] to-[#5a76c8] p-6 rounded-t-[2rem] relative overflow-hidden flex justify-between items-center text-white">
            <div class="absolute -right-4 -top-4 text-6xl opacity-20 transform rotate-12">🛒</div>
            <div>
                <h3 class="font-black text-xl tracking-tight drop-shadow-sm">Detail Pesanan</h3>
                <p class="font-bold text-sm text-[#e0fbfc]" id="modalProductName">Nama Produk</p>
            </div>
            <button onclick="closeCheckoutModal()" class="w-8 h-8 bg-white/20 hover:bg-white/40 rounded-full flex items-center justify-center transition-colors backdrop-blur-sm font-black relative z-10">&times;</button>
        </div>

        <form action="{{ route('user.checkout.process') }}" method="POST" class="p-6 md:p-8" onsubmit="showLoadingBtn()">
            @csrf
            <input type="hidden" name="product_id" id="modalProductId">
            
            <div class="mb-6">
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">
                    @if(Str::contains(Str::lower($category->name), 'game'))
                        Target (User ID & Zone ID)
                    @else
                        Target (Username / Link Akun)
                    @endif
                </label>
                <input type="text" name="target_data" required 
                       class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" 
                       placeholder="Ketik target pesanan di sini...">
            </div>

            <div class="mb-8">
                <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Catatan (Opsional)</label>
                <textarea name="target_notes" rows="2" 
                          class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" 
                          placeholder="Pesan khusus untuk admin..."></textarea>
            </div>

            <div class="bg-[#f0f5ff] rounded-[1.5rem] p-5 border-2 border-white shadow-inner mb-8">
                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-1 text-center">Total Pembayaran</p>
                <p class="font-black text-[#5a76c8] text-3xl text-center" id="modalProductPrice">Rp 0</p>
            </div>

            <input type="hidden" name="payment_method" value="wallet">

            <button type="submit" id="btnCheckout" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white font-black text-xl py-4 rounded-[1.5rem] transition-transform active:scale-95 shadow-lg shadow-[#4bc6b9]/30 border-2 border-white flex justify-center items-center gap-2">
                Bayar Pakai Saldo ✨
            </button>
        </form>
    </div>
</div>

<script>
    // JS Untuk mengontrol Modal Pop-Up
    const modal = document.getElementById('checkoutModal');
    const overlay = document.getElementById('modalOverlay');
    const content = document.getElementById('modalContent');

    function openCheckoutModal(id, name, price) {
        document.getElementById('modalProductId').value = id;
        document.getElementById('modalProductName').innerText = name;
        document.getElementById('modalProductPrice').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Animasi Masuk agar lebih smoth
        setTimeout(() => {
            overlay.classList.remove('opacity-0');
            content.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }

    function closeCheckoutModal() {
        // Animasi Keluar
        overlay.classList.add('opacity-0');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }

    // Ubah teks tombol jadi Loading saat form di-submit
    function showLoadingBtn() {
        const btn = document.getElementById('btnCheckout');
        btn.innerHTML = 'Memproses... ⏳';
        btn.classList.add('opacity-70', 'cursor-not-allowed');
    }
</script>

@endsection