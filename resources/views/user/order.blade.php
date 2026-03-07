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
                    <input type="text" name="target_data" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan data sesuai petunjuk...">
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
                        <label class="relative border rounded-xl p-4 flex justify-between items-center cursor-pointer hover:bg-blue-50 transition border-gray-200 peer-checked:border-blue-600">
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

            <div class="bg-white rounded-2xl shadow-sm border p-6">
                <div class="flex items-center mb-4">
                    <span class="bg-blue-600 text-white w-7 h-7 rounded-full flex items-center justify-center font-bold mr-3 text-sm">3</span>
                    <h2 class="text-lg font-bold">Konfirmasi Pesanan</h2>
                </div>
                
                <p class="text-sm text-gray-600 mb-6">Metode pembayaran akan tersedia setelah klik tombol di bawah (QRIS, E-Wallet, Bank Transfer).</p>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg transition-transform active:scale-95">
                    Pesan Sekarang
                </button>
            </div>
        </form>
    </main>

</body>
</html>