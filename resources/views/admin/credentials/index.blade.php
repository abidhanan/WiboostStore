@extends('layouts.admin')

@section('title', 'Kelola Stok: ' . $product->name)

@section('content')
<style>
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font pb-12 max-w-6xl mx-auto relative z-10">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.products.index'),
        'badge' => 'Gudang Akun',
        'title' => 'Gudang Stok',
        'description' => $product->name,
    ])

    <div class="mb-10 grid grid-cols-2 gap-3 sm:gap-4">
        <div class="min-w-0 rounded-[1.5rem] border-4 border-white bg-white/90 px-4 py-4 text-center shadow-lg shadow-[#bde0fe]/20 backdrop-blur-sm sm:px-6">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Tipe Produk</p>
            <p class="mt-1 truncate text-base font-black uppercase text-[#5a76c8] sm:text-lg">{{ $product->process_type }}</p>
        </div>

        <div class="min-w-0 rounded-[1.5rem] border-4 border-white bg-white/90 px-4 py-4 text-center shadow-lg shadow-[#bde0fe]/20 backdrop-blur-sm sm:px-6">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Sisa Stok Total</p>
            <div class="mt-1 flex min-w-0 items-center justify-center gap-2">
                <p class="text-2xl font-black leading-none text-[#5a76c8] sm:text-3xl">{{ $product->available_stock }}</p>
                @if($product->available_stock <= $product->stock_reminder)
                    <span class="shrink-0 rounded-md border border-white bg-[#ffe5e5] px-2.5 py-1 text-[9px] font-black text-[#ff6b6b] shadow-sm animate-pulse sm:px-3 sm:text-[10px]">RE-STOK!</span>
                @endif
            </div>
        </div>
    </div>
    @if (session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-emerald-100/50 flex items-center gap-4 relative z-10">
            @include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-6 w-6 shrink-0'])
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-center gap-4 relative z-10">
            @include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'h-6 w-6 shrink-0'])
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 relative z-10">
        
        <div class="lg:col-span-1">
            <form action="{{ route('admin.credentials.store', $product->id) }}" method="POST" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-8 sticky top-6">
                @csrf
                <div class="flex items-center gap-3 mb-6 border-b-4 border-dashed border-[#f4f9ff] pb-4">
                    <h4 class="font-black text-[#2b3a67] text-2xl">Tambah Stok</h4>
                </div>
                
                <div class="space-y-5">
                    @if($product->process_type == 'account')
                        <div class="p-4 bg-[#e6fff7] border-4 border-white rounded-[1.5rem] mb-5 shadow-sm">
                            <p class="text-xs text-emerald-600 font-bold leading-relaxed">Semua form di bawah <span class="font-black underline">opsional</span>. Cukup isi yang diperlukan sesuai dengan jenis aplikasi premiumnya.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Email</label>
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
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Catatan</label>
                            <textarea name="notes" rows="3" class="w-full resize-none bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Batas Penggunaan <span class="text-rose-500">*</span></label>
                            <input type="number" name="max_usage" required min="1" value="1" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner">
                        </div>
                    @endif

                    <div class="mt-6 pt-6 border-t-4 border-dashed border-[#f4f9ff]">
                        <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Link Tutorial</label>
                        <input type="url" name="tutorial_link" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]">
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

                    <div class="mt-4 bg-[#eef6ff] p-5 rounded-[1.5rem] border-4 border-white flex items-center justify-between shadow-md">
                        <div>
                            <p class="text-sm font-black text-[#5a76c8] mb-1">Perlu Upload Bukti Login?</p>
                            <p class="text-[10px] font-bold text-[#8faaf3] leading-tight pr-4">Aktifkan jika pembeli wajib upload bukti login dari detail pesanan.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" name="requires_login_proof" class="sr-only peer" value="1">
                            <div class="w-14 h-8 bg-[#bde0fe] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-[#8faaf3] after:border-2 after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#5a76c8] shadow-inner"></div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-lg py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white mt-8 flex justify-center items-center gap-2">
                    Simpan Data
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/30 border-4 border-white overflow-hidden flex flex-col">
            <div class="px-6 py-6 border-b-4 border-dashed border-[#f4f9ff] bg-[#f8faff]">
                <h4 class="font-black text-[#2b3a67] text-2xl flex items-center gap-3">
                    @include('partials.wiboost-icon', ['name' => 'package', 'class' => 'h-7 w-7 text-[#5a76c8]'])
                    Daftar Data Tersedia
                </h4>
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
                                <div class="space-y-2 bg-white p-4 rounded-2xl border-2 border-[#f0f5ff] shadow-sm w-full min-w-[220px] max-w-[360px]">
                                    @if($cred->data_1 && $cred->data_1 !== '-')
                                        <div class="rounded-xl border border-[#dbeafe] bg-[#f4f9ff] px-3 py-2">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-[#8faaf3]">Email</p>
                                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $cred->data_1 }}</p>
                                        </div>
                                    @endif
                                    @if($cred->data_2)
                                        <div class="rounded-xl border border-amber-100 bg-amber-50 px-3 py-2">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-amber-500">Password</p>
                                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $cred->data_2 }}</p>
                                        </div>
                                    @endif
                                    @if($cred->data_3)
                                        <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-2">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-indigo-500">Profil</p>
                                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $cred->data_3 }}</p>
                                        </div>
                                    @endif
                                    @if($cred->data_4)
                                        <div class="rounded-xl border border-rose-100 bg-rose-50 px-3 py-2">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-rose-500">PIN</p>
                                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $cred->data_4 }}</p>
                                        </div>
                                    @endif
                                    @if($cred->data_5)
                                        <div class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-2">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-blue-500">Link Akses</p>
                                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $cred->data_5 }}</p>
                                        </div>
                                    @endif
                                    @if($cred->notes)
                                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-slate-500">Catatan</p>
                                            <p class="mt-1 whitespace-pre-line break-words text-sm font-black text-[#2b3a67]">{{ $cred->notes }}</p>
                                        </div>
                                    @endif
                                    
                                    <div class="flex gap-2 pt-2 flex-wrap">
                                        @if($cred->tutorial_link)
                                            <span class="bg-[#e0fbfc] text-[#5a76c8] px-3 py-1 rounded-md text-[9px] font-black uppercase shadow-sm border border-white">Tutor Bawaan</span>
                                        @endif
                                        @if($cred->needs_otp)
                                            <span class="bg-[#fff5eb] text-amber-500 px-3 py-1 rounded-md text-[9px] font-black uppercase shadow-sm border border-white">Butuh OTP</span>
                                        @endif
                                        @if($cred->requires_login_proof)
                                            <span class="bg-[#eef6ff] text-[#5a76c8] px-3 py-1 rounded-md text-[9px] font-black uppercase shadow-sm border border-white">Perlu Bukti Login</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-[9px] font-black text-[#8faaf3] mt-3 uppercase tracking-widest">
                                    Ditambahkan oleh {{ $cred->createdBy->name ?? 'Admin' }} &bull; {{ $cred->created_at?->timezone(config('app.timezone', 'Asia/Jakarta'))->locale('id')->translatedFormat('d F Y, H:i') }}
                                </p>
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
                                    <button type="submit" class="w-12 h-12 mx-auto bg-[#ffe5e5] border-4 border-white rounded-[1.2rem] flex items-center justify-center text-[#ff6b6b] hover:bg-[#ffcccc] transition-transform active:scale-95 shadow-md shadow-[#ff6b6b]/20 group-hover:scale-110" title="Hapus Data">
                                        @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-5 w-5'])
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-5 py-24 text-center">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-[2rem] bg-[#f0f5ff] border-4 border-white mb-4 text-[#5a76c8] shadow-inner animate-float">
                                    @include('partials.wiboost-icon', ['name' => 'package', 'class' => 'h-9 w-9'])
                                </div>
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
