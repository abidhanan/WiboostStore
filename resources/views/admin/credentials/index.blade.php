@extends('layouts.admin')
@section('title', 'Kelola Stok: ' . $product->name)
@section('content')
<div class="pb-12 max-w-6xl mx-auto" style="font-family: 'Nunito', sans-serif;">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pl-2">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.products.index') }}" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-[#5a76c8] hover:bg-[#f0f5ff] transition-colors border-2 border-white shadow-sm">❮</a>
            <div>
                <h3 class="text-2xl md:text-3xl font-black text-[#2b3a67] tracking-tight">Gudang Kredensial 📦</h3>
                <p class="text-sm font-bold text-[#8faaf3] mt-1">{{ $product->name }}</p>
            </div>
        </div>
        <div class="flex gap-3">
            <div class="bg-white px-4 py-2 rounded-[1rem] border-2 border-white shadow-sm text-center flex flex-col justify-center">
                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Sisa Stok Total</p>
                <div class="flex items-center justify-center gap-2 mt-0.5">
                    <p class="font-black text-2xl leading-none text-[#5a76c8]">{{ $product->available_stock }}</p>
                    @if($product->available_stock <= $product->stock_reminder)
                        <span class="bg-[#ffe5e5] text-[#ff6b6b] text-[9px] px-2 py-0.5 rounded-full font-black animate-pulse">RE-STOK!</span>
                    @endif
                </div>
            </div>

            <div class="bg-[#f0f5ff] px-4 py-2 rounded-[1rem] border-2 border-white shadow-sm text-center flex flex-col justify-center">
                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Tipe Produk</p>
                <p class="font-black text-[#5a76c8] text-sm uppercase mt-0.5">{{ $product->process_type }}</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-6 font-black shadow-sm flex items-start gap-4">
            <span class="text-2xl mt-1">✅</span><p class="mt-1.5">{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-6 font-black shadow-sm flex items-start gap-4">
            <span class="text-2xl mt-1">⚠️</span><p class="mt-1.5">{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1">
            <form action="{{ route('admin.credentials.store', $product->id) }}" method="POST" class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white p-6 sticky top-4">
                @csrf
                <h4 class="font-black text-[#2b3a67] text-lg mb-4 flex items-center gap-2"><span class="text-xl">➕</span> Tambah Stok</h4>
                
                <div class="space-y-4">
                    @if($product->process_type == 'account')
                        <div class="p-3 bg-[#e6fff7] border-2 border-emerald-100 rounded-xl mb-4">
                            <p class="text-xs text-emerald-600 font-bold">💡 Semua form di bawah <b>opsional</b>. Isi yang diperlukan saja sesuai jenis aplikasinya.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-[#8faaf3] mb-2 ml-2">Email / Username</label>
                            <input type="text" name="data_1" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1rem] px-4 py-3 text-[#2b3a67] font-black outline-none transition text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-[#8faaf3] mb-2 ml-2">Password</label>
                            <input type="text" name="data_2" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1rem] px-4 py-3 text-[#2b3a67] font-black outline-none transition text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-[#8faaf3] mb-2 ml-2">Profil Name</label>
                                <input type="text" name="data_3" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1rem] px-4 py-3 text-[#2b3a67] font-black outline-none transition text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-[#8faaf3] mb-2 ml-2">PIN</label>
                                <input type="text" name="data_4" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1rem] px-4 py-3 text-[#2b3a67] font-black outline-none transition text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-[#8faaf3] mb-2 ml-2">Link Invite / Link Akses</label>
                            <input type="text" name="data_5" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1rem] px-4 py-3 text-[#2b3a67] font-black outline-none transition text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-[#8faaf3] mb-2 ml-2">Batas Penggunaan (Wajib)</label>
                            <input type="number" name="max_usage" required min="1" value="1" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1rem] px-4 py-3 text-[#2b3a67] font-black outline-none transition text-sm">
                            <p class="text-[10px] font-bold text-[#8faaf3] mt-2 ml-1">Berapa orang yang boleh beli baris data ini?</p>
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-black text-[#8faaf3] mb-2 ml-2">Nomor Luar / Nomor HP (Wajib)</label>
                            <input type="text" name="data_1" required class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1rem] px-4 py-3 text-[#2b3a67] font-black outline-none transition text-sm" placeholder="Contoh: +123456789">
                        </div>
                    @endif

                    <div class="mt-4 pt-4 border-t-2 border-dashed border-[#e0fbfc]">
                        <label class="block text-xs font-black text-[#8faaf3] mb-2 ml-2">Link Tutorial OTP / Login (Opsional)</label>
                        <input type="url" name="tutorial_link" class="w-full bg-[#f4f9ff] border-2 border-transparent focus:border-[#5a76c8] rounded-[1rem] px-4 py-3 text-[#2b3a67] font-black outline-none transition text-sm placeholder-[#a3bbfb]" placeholder="https://youtube.com/...">
                        <p class="text-[10px] font-bold text-[#8faaf3] mt-2 ml-1">Isi jika pelanggan bisa cek OTP, tutorial login, atau akses penggunaan sendiri via link.</p>
                    </div>

                    <div class="mt-4 bg-[#fffcf0] p-4 rounded-xl border-2 border-amber-100 flex items-center justify-between shadow-sm">
                        <div>
                            <p class="text-xs font-black text-amber-600 mb-1">Butuh Bantuan Admin?</p>
                            <p class="text-[10px] font-bold text-amber-500">Pelanggan harus chat Admin untuk meminta OTP/Akses.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer ml-3 shrink-0">
                            <input type="checkbox" name="needs_otp" class="sr-only peer" value="1">
                            <div class="w-11 h-6 bg-amber-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-sm py-3.5 rounded-[1rem] transition-transform active:scale-95 shadow-md mt-6">Simpan Data</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b-2 border-dashed border-[#f0f5ff] bg-[#f4f9ff]">
                <h4 class="font-black text-[#2b3a67] text-lg flex items-center gap-2"><span class="text-xl">📋</span> Daftar Data Tersedia</h4>
            </div>
            
            <div class="overflow-x-auto flex-1 p-4">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Detail Data</th>
                            <th class="px-4 py-3 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Kuota Pakai</th>
                            <th class="px-4 py-3 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                        @forelse($credentials as $cred)
                        <tr class="hover:bg-[#f4f9ff] transition-colors group">
                            <td class="px-4 py-4 align-middle">
                                @if($cred->data_1 && $cred->data_1 !== '-')
                                    <p class="font-black text-[#2b3a67] text-sm break-all mb-1">{{ $cred->data_1 }}</p>
                                @endif
                                
                                <div class="space-y-1">
                                    @if($cred->data_2) <p class="font-black text-amber-500 text-xs">🔑 {{ $cred->data_2 }}</p> @endif
                                    @if($cred->data_3) <p class="font-black text-indigo-500 text-xs">👤 Profil: {{ $cred->data_3 }}</p> @endif
                                    @if($cred->data_4) <p class="font-black text-rose-500 text-xs">🔢 PIN: {{ $cred->data_4 }}</p> @endif
                                    @if($cred->data_5) <p class="font-black text-blue-500 text-xs truncate max-w-xs">🔗 {{ $cred->data_5 }}</p> @endif
                                    
                                    <div class="flex gap-2 mt-2 flex-wrap">
                                        @if($cred->tutorial_link)
                                            <span class="bg-[#e0fbfc] text-[#5a76c8] px-2 py-0.5 rounded text-[9px] font-black uppercase shadow-sm">Ada Tutorial</span>
                                        @endif
                                        @if($cred->needs_otp)
                                            <span class="bg-[#fff5eb] text-amber-500 px-2 py-0.5 rounded text-[9px] font-black uppercase shadow-sm">Butuh OTP Admin</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-[9px] font-bold text-[#8faaf3] mt-2">Ditambahkan: {{ $cred->created_at->format('d M Y') }}</p>
                            </td>
                            
                            <td class="px-4 py-4 align-middle text-center">
                                @if($cred->current_usage >= $cred->max_usage)
                                    <span class="bg-[#ffe5e5] text-[#ff6b6b] px-3 py-1 rounded-full text-[10px] font-black">HABIS ({{ $cred->current_usage }}/{{ $cred->max_usage }})</span>
                                @else
                                    <span class="bg-[#e6fff7] text-emerald-500 px-3 py-1 rounded-full text-[10px] font-black">SISA {{ $cred->max_usage - $cred->current_usage }}</span>
                                    <p class="text-[9px] font-bold text-[#8faaf3] mt-1">Terpakai: {{ $cred->current_usage }}/{{ $cred->max_usage }}</p>
                                @endif
                            </td>
                            
                            <td class="px-4 py-4 align-middle text-center">
                                <form action="{{ route('admin.credentials.destroy', $cred->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 mx-auto bg-white border-2 border-[#f0f5ff] rounded-xl flex items-center justify-center text-[#ff6b6b] hover:bg-[#ff6b6b] hover:border-[#ff6b6b] hover:text-white transition-colors shadow-sm" title="Hapus">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-[1.5rem] bg-[#f0f5ff] border-4 border-white mb-2 shadow-inner"><span class="text-xl">📭</span></div>
                                <p class="text-[#8faaf3] font-black text-xs">Belum ada stok data dimasukkan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
