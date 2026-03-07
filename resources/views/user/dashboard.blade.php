<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dasbor Pelanggan - Wiboost Store</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <nav class="bg-blue-600 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 text-white font-bold text-xl">
                    Wiboost Store
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('user.history') }}" class="text-blue-100 hover:text-white text-sm font-medium">Riwayat Transaksi</a>
                    
                    <div class="relative group">
                        <button class="flex items-center text-white text-sm font-medium focus:outline-none">
                            Halo, {{ Auth::user()->name }}
                            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden group-hover:block z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ubah Profil & Password</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Keluar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-6 md:p-10 text-white shadow-lg mb-8 cursor-pointer transform transition hover:scale-[1.01]" onclick="window.location.href='#'">
            <h2 class="text-2xl font-bold mb-2">Promo Spesial Bulan Ini! 🎉</h2>
            <p class="text-blue-100">Dapatkan potongan harga khusus untuk layanan Suntik Sosmed dan Aplikasi Premium. Tap di sini untuk info lebih lanjut.</p>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500 mb-1">Transaksi Bulan Ini</p>
                <p class="text-3xl font-bold text-blue-600">{{ $totalThisMonth }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500 mb-1">Total Transaksi Saya</p>
                <p class="text-3xl font-bold text-indigo-600">{{ $totalAllTime }}</p>
            </div>
        </div>

        <h3 class="text-lg font-bold text-gray-800 mb-4">Pilih Layanan</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($categories as $category)
                <a href="{{ route('user.order.category', $category->slug) }}" class="bg-white rounded-xl p-4 flex flex-col items-center justify-center shadow-sm border border-gray-100 hover:shadow-md transition-shadow text-center group">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-3 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-700">{{ $category->name }}</span>
                </a>
            @endforeach
        </div>

    </main>

    <a href="https://wa.me/6285326513324?text=Halo%20Admin%20Wiboost,%20saya%20ingin%20mengajukan%20komplain/klaim%20garansi..." target="_blank" class="fixed bottom-6 right-6 bg-green-500 text-white p-4 rounded-full shadow-lg hover:bg-green-600 hover:scale-110 transition-transform z-50 flex items-center justify-center">
        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.347-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.876 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
        <span class="font-bold">Komplain / Garansi</span>
    </a>

    <div id="live-notif" class="fixed bottom-6 left-6 bg-white border-l-4 border-blue-500 shadow-lg rounded-r-lg p-4 transform transition-transform translate-y-20 opacity-0 z-40">
        <p class="text-sm text-gray-800"><span class="font-bold text-blue-600">Budi</span> baru saja membeli <span class="font-bold">50 Diamond ML</span></p>
        <p class="text-xs text-gray-500 mt-1">Beberapa detik yang lalu</p>
    </div>

</body>
</html>