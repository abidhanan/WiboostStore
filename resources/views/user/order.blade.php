<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order {{ $category->name }} - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Nunito', sans-serif; } </style>
</head>
<body class="bg-[#f4f9ff] text-slate-800 antialiased pb-24">

    <nav class="bg-white/90 backdrop-blur-md border-b-4 border-white shadow-sm sticky top-0 z-50">
        <div class="max-w-3xl mx-auto px-4 flex items-center h-20">
            <a href="{{ route('user.dashboard') }}" class="w-10 h-10 bg-[#f0f5ff] hover:bg-[#e0ebff] text-[#5a76c8] rounded-full flex items-center justify-center transition mr-4 border-2 border-white shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h1 class="text-2xl font-black text-[#2b3a67]">{{ $category->name }}</h1>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto py-10 px-4">
        
        @if(session('error'))
            <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
                <span class="text-2xl">⚠️</span> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('user.checkout.process') }}" method="POST">
            @csrf
            <input type="hidden" name="category_id" value="{{ $category->id }}">

            <div class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-8 mb-8 relative">
                <div class="absolute -left-3 -top-3 w-12 h-12 bg-[#5a76c8] text-white rounded-full flex items-center justify-center font-black text-xl border-4 border-[#f4f9ff] shadow-sm">1</div>
                <h2 class="text-xl font-black text-[#2b3a67] mb-6 ml-6">Lengkapi Data Pesanan</h2>
                
                <div>
                    <label class="block text-sm font-black text-[#8faaf3] mb-2 pl-2">
                        @if(Str::contains(Str::lower($category->name), 'game'))
                            Masukkan User ID & Zone ID
                        @elseif(Str::contains(Str::lower($category->name), 'sosmed'))
                            Username atau Link Profile/Postingan
                        @else
                            Nomor HP / Akun Tujuan
                        @endif
                    </label>
                    <input type="text" name="target_data" required 
                           class="w-full bg-[#f0f5ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-bold focus:ring-0 outline-none transition placeholder-[#a3bbfb]" 
                           placeholder="Ketik di sini...">
                    <p class="mt-2 text-xs font-bold text-amber-500 pl-2">💡 Pastikan data yang diinput sudah benar.</p>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-8 mb-8 relative">
                <div class="absolute -left-3 -top-3 w-12 h-12 bg-[#5a76c8] text-white rounded-full flex items-center justify-center font-black text-xl border-4 border-[#f4f9ff] shadow-sm">2</div>
                <h2 class="text-xl font-black text-[#2b3a67] mb-6 ml-6">Pilih Layanan</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($products as $product)
                        <label class="relative bg-[#f4f9ff] border-4 border-transparent hover:border-[#bde0fe] rounded-[1.5rem] p-5 flex flex-col justify-between cursor-pointer transition-all group">
                            <input type="radio" name="product_id" value="{{ $product->id }}" class="sr-only peer" required>
                            
                            <div class="mb-4">
                                <p class="font-black text-[#2b3a67] text-lg leading-tight">{{ $product->name }}</p>
                                <p class="text-xs font-bold text-[#8faaf3] mt-1">{{ $product->description ?? 'Proses Cepat & Legal' }}</p>
                            </div>
                            <div class="text-right mt-auto">
                                <p class="text-[#5a76c8] font-black text-xl">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                            </div>
                            
                            <div class="absolute inset-0 border-4 border-transparent peer-checked:border-[#5a76c8] rounded-[1.5rem] pointer-events-none transition-colors"></div>
                            <div class="absolute top-4 right-4 w-6 h-6 bg-[#5a76c8] rounded-full text-white flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </label>
                    @empty
                        <p class="col-span-full text-center text-[#8faaf3] font-bold py-8 bg-[#f4f9ff] rounded-[2rem] border-4 border-dashed border-[#bde0fe]">Belum ada produk tersedia untuk kategori ini.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-8 relative">
                <div class="absolute -left-3 -top-3 w-12 h-12 bg-[#5a76c8] text-white rounded-full flex items-center justify-center font-black text-xl border-4 border-[#f4f9ff] shadow-sm">3</div>
                <h2 class="text-xl font-black text-[#2b3a67] mb-6 ml-6">Pilih Metode Pembayaran</h2>

                <div class="grid grid-cols-1 gap-4 mb-8">
                    <label class="relative bg-gradient-to-r from-[#e0fbfc] to-[#f4f9ff] rounded-[1.5rem] p-5 flex items-center gap-4 cursor-pointer border-4 border-white hover:border-[#bde0fe] shadow-sm transition-all">
                        <input type="radio" name="payment_method" value="wallet" class="sr-only peer" required>
                        <div class="w-6 h-6 rounded-full border-2 border-[#8faaf3] peer-checked:border-[#5a76c8] peer-checked:border-[7px] transition-all bg-white shrink-0"></div>
                        <div class="flex-1">
                            <p class="font-black text-[#2b3a67] text-lg">Saldo Wiboost</p>
                            <p class="text-sm font-bold text-[#5a76c8]">Sisa: Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-4xl drop-shadow-sm">💰</div>
                        <div class="absolute inset-0 border-4 border-transparent peer-checked:border-[#5a76c8] rounded-[1.5rem] pointer-events-none transition-colors"></div>
                    </label>

                    <label class="relative bg-[#f4f9ff] rounded-[1.5rem] p-5 flex items-center gap-4 cursor-pointer border-4 border-white hover:border-[#bde0fe] shadow-sm transition-all">
                        <input type="radio" name="payment_method" value="manual" class="sr-only peer" required>
                        <div class="w-6 h-6 rounded-full border-2 border-[#8faaf3] peer-checked:border-[#5a76c8] peer-checked:border-[7px] transition-all bg-white shrink-0"></div>
                        <div class="flex-1">
                            <p class="font-black text-[#2b3a67] text-lg">QRIS / e-Wallet / Bank</p>
                            <p class="text-sm font-bold text-[#8faaf3]">Otomatis via Midtrans</p>
                        </div>
                        <div class="text-4xl drop-shadow-sm">📱</div>
                        <div class="absolute inset-0 border-4 border-transparent peer-checked:border-[#5a76c8] rounded-[1.5rem] pointer-events-none transition-colors"></div>
                    </label>
                </div>

                <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-xl py-5 rounded-[2rem] shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 border-4 border-white flex justify-center items-center gap-3">
                    Pesan Sekarang 🚀
                </button>
            </div>
        </form>
    </main>

</body>
</html>