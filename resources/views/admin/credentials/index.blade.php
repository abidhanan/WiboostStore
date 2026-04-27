@extends('layouts.admin')

@section('title', 'Kelola Stok: ' . $product->name)

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font pb-12 max-w-6xl mx-auto relative z-10">
    
    <div class="absolute top-10 right-10 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">📦</div>
    <div class="absolute top-1/3 -left-10 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 right-0 text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 mb-10 pl-2 relative z-10">
        <div class="flex items-center gap-5">
            <a href="{{ route('admin.products.index') }}" class="w-14 h-14 bg-white/90 backdrop-blur-sm rounded-[1.2rem] flex items-center justify-center text-[#5a76c8] hover:bg-[#e0fbfc] transition-transform active:scale-95 border-4 border-white shadow-lg shadow-[#bde0fe]/30 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h3 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm">Gudang Stok 📦</h3>
                <p class="text-sm font-bold text-[#8faaf3] mt-2 bg-white/50 px-4 py-2 rounded-xl border border-white shadow-sm inline-block">{{ $product->name }}</p>
            </div>
        </div>

        <div class="flex gap-4">
            <div class="bg-white/90 backdrop-blur-sm px-6 py-3 rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 text-center flex flex-col justify-center transform hover:scale-105 transition-transform">
                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Sisa Stok Total</p>
                <div class="flex items-center justify-center gap-3 mt-1">
                    <p class="font-black text-3xl leading-none text-[#5a76c8]">{{ $product->available_stock }}</p>
                    @if($product->available_stock <= $product->stock_reminder)
                        <span class="bg-[#ffe5e5] text-[#ff6b6b] text-[10px] px-3 py-1 rounded-md border border-white font-black animate-pulse shadow-sm">RE-STOK!</span>
                    @endif
                </div>
            </div>

            <div class="bg-[#f0f5ff]/90 backdrop-blur-sm px-6 py-3 rounded-[1.5rem] border-4 border-white shadow-lg shadow-[#bde0fe]/20 text-center flex flex-col justify-center transform hover:scale-105 transition-transform">
                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Tipe Produk</p>
                <p class="font-black text-[#5a76c8] text-lg uppercase mt-1">{{ $product->process_type }}</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-emerald-100/50 flex items-center gap-4 relative z-10">
            <span class="text-3xl drop-shadow-sm">✅</span><p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-center gap-4 relative z-10">
            <span class="text-3xl drop-shadow-sm">⚠️</span><p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 relative z-10">
        
        <div class="lg:col-span-1">
            <form action="{{ route('admin.credentials.store', $product->id) }}" method="POST" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-8 sticky top-6">
                @csrf
                <div class="flex items-center gap-3 mb-6 border-b-4 border-dashed border-[#f4f9ff] pb-4">
                    <span class="text-3xl drop-shadow-sm">➕</span>
                    <h4 class="font-black text-[#2b3a67] text-2xl">Tambah Stok</h4>
                </div>
                
                <div class="space-y-5">
                    @if($product->process_type == 'account')
                        <div class="p-4 bg-[#e6fff7] border-4 border-white rounded-[1.5rem] mb-5 shadow-sm">
                            <p class="text-xs text-emerald-600 font-bold leading-relaxed">💡 Semua form di bawah <span class="font-black underline">opsional</span>. Cukup isi yang diperlukan sesuai dengan jenis aplikasi premiumnya.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Email / Username</label>
                            <input type="text" name="data_1" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Password</label>
                            <input type="text" name="data_2" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Profil Name</label>
                                <input type="text" name="data_3" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">PIN</label>
                                <input type="text" name="data_4" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Link Invite / Link Akses</label>
                            <input type="text" name="data_5" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Batas Penggunaan <span class="text-rose-500">*</span></label>
                            <input type="number" name="max_usage" required min="1" value="1" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner">
                            <p class="text-[10px] font-black text-[#8faaf3] mt-2 ml-2 uppercase tracking-widest bg-white inline-block px-2 py-1 border border-white rounded shadow-sm">Berapa orang yang boleh beli?</p>
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Nomor Luar / Nomor HP <span class="text-rose-500">*</span></label>
                            <input type="text" name="data_1" required class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]" placeholder="Contoh: +123456789">
                        </div>
                    @endif

                    <div class="mt-6 pt-6 border-t-4 border-dashed border-[#f4f9ff]">
                        <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Link Tutorial OTP / Login (Ops)</label>
                        <input type="url" name="tutorial_link" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]" placeholder="https://youtube.com/...">
                        <p class="text-[10px] font-black text-[#8faaf3] mt-2 ml-2 uppercase tracking-widest">Tutorial khusus data ini.</p>
                    </div>

                    <div class="mt-4 bg-[#fffcf0] p-5 rounded-[1.5rem] border-4 border-white flex items-center justify-between shadow-md">
                        <div>
                            <p class="text-sm font-black text-amber-600 mb-1">Butuh OTP Admin?</p>
                            <p class="text-[10px] font-bold text-amber-500 leading-tight pr-4">Centang ini jika pelanggan harus chat Admin untuk meminta OTP.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" name="needs_otp" class="sr-only peer" value="1">
                            <div class="w-14 h-8 bg-amber-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-amber-300 after:border-2 after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-amber-500 shadow-inner"></div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-lg py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white mt-8 flex justify-center items-center gap-2">
                    Simpan Data 💾
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/30 border-4 border-white overflow-hidden flex flex-col">
            <div class="px-6 py-6 border-b-4 border-dashed border-[#f4f9ff] bg-[#f8faff]">
                <h4 class="font-black text-[#2b3a67] text-2xl flex items-center gap-3"><span class="text-3xl drop-shadow-sm">📋</span> Daftar Data Tersedia</h4>
            </div>
            
            <div class="overflow-x-auto flex-1 p-4 md:p-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Detail Data</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center border-b-4 border-dashed border-[#f4f9ff]">Kuota Pakai</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center border-b-4 border-dashed border-[#f4f9ff]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($credentials as $cred)
                        <tr class="hover:bg-[#f8faff] transition-colors group">
                            <td class="px-5 py-5 align-top">
                                @if($cred->data_1 && $cred->data_1 !== '-')
                                    <p class="font-black text-[#2b3a67] text-base break-all mb-3">{{ $cred->data_1 }}</p>
                                @endif
                                
                                <div class="space-y-2 bg-white p-4 rounded-2xl border-2 border-[#f0f5ff] shadow-sm w-fit min-w-[200px]">
                                    @if($cred->data_2) <p class="font-black text-amber-500 text-xs bg-amber-50 px-2 py-1 rounded border border-amber-100">🔑 {{ $cred->data_2 }}</p> @endif
                                    @if($cred->data_3) <p class="font-black text-indigo-500 text-xs bg-indigo-50 px-2 py-1 rounded border border-indigo-100">👤 Profil: {{ $cred->data_3 }}</p> @endif
                                    @if($cred->data_4) <p class="font-black text-rose-500 text-xs bg-rose-50 px-2 py-1 rounded border border-rose-100">🔢 PIN: {{ $cred->data_4 }}</p> @endif
                                    @if($cred->data_5) <p class="font-black text-blue-500 text-xs truncate max-w-[200px] bg-blue-50 px-2 py-1 rounded border border-blue-100">🔗 {{ $cred->data_5 }}</p> @endif
                                    
                                    <div class="flex gap-2 pt-2 flex-wrap">
                                        @if($cred->tutorial_link)
                                            <span class="bg-[#e0fbfc] text-[#5a76c8] px-3 py-1 rounded-md text-[9px] font-black uppercase shadow-sm border border-white">Tutor Bawaan</span>
                                        @endif
                                        @if($cred->needs_otp)
                                            <span class="bg-[#fff5eb] text-amber-500 px-3 py-1 rounded-md text-[9px] font-black uppercase shadow-sm border border-white">Butuh OTP</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-[9px] font-black text-[#8faaf3] mt-3 uppercase tracking-widest">Added: {{ $cred->created_at->format('d M Y') }}</p>
                            </td>
                            
                            <td class="px-5 py-5 align-middle text-center">
                                @if($cred->current_usage >= $cred->max_usage)
                                    <span class="bg-[#ffe5e5] text-[#ff6b6b] px-4 py-2 rounded-full text-[10px] font-black border-2 border-white shadow-sm inline-block">HABIS ({{ $cred->current_usage }}/{{ $cred->max_usage }})</span>
                                @else
                                    <span class="bg-[#e6fff7] text-emerald-500 px-4 py-2 rounded-full text-[10px] font-black border-2 border-white shadow-sm inline-block">SISA {{ $cred->max_usage - $cred->current_usage }}</span>
                                    <p class="text-[10px] font-bold text-[#8faaf3] mt-2 uppercase tracking-widest">Terpakai: {{ $cred->current_usage }}/{{ $cred->max_usage }}</p>
                                @endif
                            </td>
                            
                            <td class="px-5 py-5 align-middle text-center">
                                <form action="{{ route('admin.credentials.destroy', $cred->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data kredensial ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-12 h-12 mx-auto bg-[#ffe5e5] border-4 border-white rounded-[1.2rem] flex items-center justify-center text-xl hover:bg-[#ff6b6b] transition-transform active:scale-95 shadow-md shadow-[#ff6b6b]/20 group-hover:scale-110" title="Hapus Data">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-5 py-24 text-center">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-[2rem] bg-[#f0f5ff] border-4 border-white mb-4 shadow-inner animate-float"><span class="text-4xl">📭</span></div>
                                <p class="text-[#5a76c8] font-black text-xl">Belum ada stok data.</p>
                                <p class="text-[#8faaf3] font-bold mt-1">Silakan tambahkan melalui form di samping kiri.</p>
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