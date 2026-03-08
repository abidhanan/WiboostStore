<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order {{ $category->name }} - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <nav class="bg-white shadow-sm border-b p-4">
        <div class="max-w-3xl mx-auto flex items-center">
            <a href="{{ route('user.dashboard') }}" class="text-blue-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-xl font-bold italic text-blue-700">{{ $category->name }}</h1>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto py-8 px-4">
        
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-6 font-bold flex items-center gap-3">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('user.checkout.process') }}" method="POST">
            @csrf
            <input type="hidden" name="category_id" value="{{ $category->id }}">

            <div class="bg-white rounded-2xl shadow-sm border p-6 mb-6">
                <div class="flex items-center mb-4">
                    <span class="bg-blue-600 text-white w-7 h-7 rounded-full flex items-center justify-center font-bold mr-3 text-sm">1</span>
                    <h2 class="text-lg font-bold">Lengkapi Data Pesanan</h2>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        @if(Str::contains(Str::lower($category->name), 'game'))
                            User ID & Zone ID
                        @elseif(Str::contains(Str::lower($category->name), 'sosmed'))
                            Username atau Link Profile/Postingan
                        @else
                            Nomor HP / Akun Tujuan
                        @endif
                    </label>
                    <input type="text" name="target_data" required class="w-full border-gray-300 rounded-lg shadow-sm px-4 py-3 focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan data sesuai petunjuk...">
                    <p class="mt-2 text-xs text-gray-500 italic">*Mohon teliti, kesalahan input bukan tanggung jawab kami.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border p-6 mb-6">
                <div class="flex items-center mb-4">
                    <span class="bg-blue-600 text-white w-7 h-7 rounded-full flex items-center justify-center font-bold mr-3 text-sm">2</span>
                    <h2 class="text-lg font-bold">Pilih Layanan</h2>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    @forelse($products as $product)
                        <label class="relative border rounded-xl p-4 flex justify-between items-center cursor-pointer hover:bg-blue-50 transition border-gray-200">
                            <input type="radio" name="product_id" value="{{ $product->id }}" class="sr-only peer" required>
                            <div>
                                <p class="font-bold text-gray-800">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $product->description ?? 'Proses Cepat & Legal' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-blue-600 font-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                            </div>
                            <div class="absolute inset-0 border-2 border-transparent peer-checked:border-blue-600 rounded-xl pointer-events-none"></div>
                        </label>
                    @empty
                        <p class="text-center text-gray-500 py-4 italic">Belum ada produk tersedia untuk kategori ini.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border p-6 mb-6">
                <div class="flex items-center mb-4">
                    <span class="bg-blue-600 text-white w-7 h-7 rounded-full flex items-center justify-center font-bold mr-3 text-sm">3</span>
                    <h2 class="text-lg font-bold">Pilih Metode Pembayaran</h2>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <label class="relative border rounded-xl p-4 flex justify-between items-center cursor-pointer hover:bg-blue-50 transition border-gray-200">
                        <input type="radio" name="payment_method" value="wallet" class="sr-only peer" required>
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full border border-gray-300 peer-checked:border-blue-600 peer-checked:border-[6px] transition-all"></div>
                            <div>
                                <p class="font-bold text-gray-800">Saldo Wiboost</p>
                                <p class="text-xs text-blue-600 font-bold mt-0.5">Sisa: Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        <div class="absolute inset-0 border-2 border-transparent peer-checked:border-blue-600 rounded-xl pointer-events-none"></div>
                    </label>

                    <label class="relative border rounded-xl p-4 flex justify-between items-center cursor-pointer hover:bg-blue-50 transition border-gray-200">
                        <input type="radio" name="payment_method" value="manual" class="sr-only peer" required>
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full border border-gray-300 peer-checked:border-blue-600 peer-checked:border-[6px] transition-all"></div>
                            <div>
                                <p class="font-bold text-gray-800">QRIS / e-Wallet / Bank</p>
                                <p class="text-xs text-gray-500 mt-0.5">Otomatis via Midtrans</p>
                            </div>
                        </div>
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        <div class="absolute inset-0 border-2 border-transparent peer-checked:border-blue-600 rounded-xl pointer-events-none"></div>
                    </label>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border p-6">
                <div class="flex items-center mb-4">
                    <span class="bg-blue-600 text-white w-7 h-7 rounded-full flex items-center justify-center font-bold mr-3 text-sm">4</span>
                    <h2 class="text-lg font-bold">Konfirmasi Pesanan</h2>
                </div>
                
                <p class="text-sm text-gray-600 mb-6">Pastikan ID dan pilihan layanan sudah benar sebelum melanjutkan pembayaran.</p>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transition-transform active:scale-95">
                    Pesan Sekarang
                </button>
            </div>
        </form>
    </main>

</body>
</html>