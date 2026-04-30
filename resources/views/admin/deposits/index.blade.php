@extends('layouts.admin')
@section('title', 'Riwayat Deposit')
@section('admin_header_subtitle', 'Pantau riwayat top-up atau pengisian saldo pengguna.')

{{-- Section admin_header_actions dihapus, dipindah ke toolbar agar responsif --}}

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; border: 2px solid #f0f5ff; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    <!-- Floating Emojis Background -->
    <div class="absolute top-10 right-10 text-5xl animate-float opacity-30 pointer-events-none hidden lg:block z-0">💰</div>
    <div class="absolute top-1/3 left-5 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden lg:block z-0">✨</div>
    <div class="absolute bottom-20 right-[15%] text-6xl animate-float opacity-20 pointer-events-none hidden lg:block z-0">🏦</div>

    <!-- Alert Messages (Aman dari 500 Error) -->
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 sm:px-6 py-4 font-black text-emerald-500 shadow-lg shadow-emerald-100/50 relative z-10 text-sm sm:text-base w-full">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-5 sm:px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 relative z-10 text-sm sm:text-base w-full">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Toolbar: Pencarian & Segarkan -->
    <div class="mb-6 sm:mb-8 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-4 sm:p-5 shadow-xl shadow-[#bde0fe]/30 relative z-10 w-full min-w-0 transition-transform duration-300 hover:border-[#bde0fe]">
        <form action="{{ route('admin.deposits.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 w-full">
            
            <!-- Wrapper Input Cari -->
            <div class="flex-1 flex gap-2 w-full min-w-0">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="min-w-0 flex-1 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] py-3 px-4 sm:px-5 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                    placeholder="Cari Invoice atau Pelanggan...">
                
                <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] w-12 h-12 text-white shadow-md active:scale-95 transition-transform" title="Cari Deposit">
                    🔍
                </button>
                
                @if(request('search'))
                    <a href="{{ route('admin.deposits.index') }}" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 text-[#ff6b6b] shadow-md active:scale-95 transition-transform" title="Reset">
                        ✖️
                    </a>
                @endif
            </div>
            
            <!-- Tombol Segarkan -->
            <a href="{{ route('admin.deposits.index') }}" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#8faaf3] px-6 py-3 h-12 text-sm font-black text-white shadow-md transition-transform active:scale-95 hover:bg-[#6f8ddc] w-full md:w-auto">
                <span class="text-base">🔄</span> <span class="whitespace-nowrap">Segarkan</span>
            </a>
            
        </form>
    </div>

    <!-- Tabel Data Deposit -->
    <div class="overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/95 backdrop-blur-sm shadow-2xl shadow-[#bde0fe]/30 relative z-10 min-w-0 w-full">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full min-w-[800px] text-left whitespace-nowrap">
                    <thead>
                        <tr>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Waktu & Invoice</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Pelanggan</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] border-b-4 border-dashed border-[#f4f9ff]">Nominal</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Metode</th>
                            <th style="font-size: 10px;" class="px-5 py-4 font-black uppercase tracking-widest text-[#8faaf3] text-center border-b-4 border-dashed border-[#f4f9ff]">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($deposits ?? [] as $deposit)
                        <tr class="transition-colors hover:bg-[#f8faff] group">
                            
                            <!-- Invoice & Waktu -->
                            <td class="px-5 py-4 sm:py-5 align-middle min-w-[150px]">
                                <p class="font-black text-[#5a76c8] text-xs sm:text-sm mb-1 bg-[#f4f9ff] inline-block px-2 py-1 rounded-md border border-white truncate max-w-full">{{ $deposit->invoice_number ?? '-' }}</p>
                                <p class="text-[9px] sm:text-[10px] font-bold text-[#8faaf3] ml-1">{{ isset($deposit->created_at) ? $deposit->created_at->format('d M Y, H:i') : '-' }}</p>
                            </td>
                            
                            <!-- User -->
                            <td class="px-5 py-4 sm:py-5 align-middle min-w-[150px] max-w-[200px]">
                                <p class="font-black text-[#2b3a67] text-xs sm:text-sm truncate">{{ $deposit->user->name ?? 'Guest' }}</p>
                                <p class="text-[9px] sm:text-[10px] font-bold text-[#8faaf3] truncate mt-1">{{ $deposit->user->email ?? '-' }}</p>
                            </td>
                            
                            <!-- Nominal -->
                            <td class="px-5 py-4 sm:py-5 align-middle">
                                <p class="font-black text-[#4bc6b9] text-sm sm:text-base drop-shadow-sm">Rp {{ number_format($deposit->amount ?? 0, 0, ',', '.') }}</p>
                            </td>
                            
                            <!-- Metode Pembayaran -->
                            <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                @php
                                    $rawMethod = strtolower($deposit->payment_method ?? '');
                                    if (($rawMethod == 'manual' || empty($rawMethod)) && ($deposit->status ?? '') != 'paid') {
                                        $methodDisplay = 'BELUM PILIH';
                                        $colorClass = 'bg-[#fff5eb] text-amber-500 border-white';
                                    } else {
                                        $methodMap = [
                                            'qris'           => 'QRIS',
                                            'gopay'          => 'GoPay',
                                            'shopeepay'      => 'ShopeePay',
                                            'bank_transfer'  => 'Bank Transfer',
                                            'cstore'         => 'Indomaret/ALFA',
                                            'credit_card'    => 'Kartu Kredit',
                                            'echannel'       => 'Mandiri Bill',
                                            'permata_va'     => 'Permata VA',
                                            'bca_va'         => 'BCA Transfer',
                                            'bni_va'         => 'BNI Transfer',
                                            'bri_va'         => 'BRI Transfer',
                                            'cimb_va'        => 'CIMB Transfer',
                                            'other_va'       => 'ATM Bersama',
                                            'danamon_online' => 'Danamon Online',
                                        ];
                                        $methodDisplay = $methodMap[$rawMethod] ?? ucwords(str_replace('_', ' ', $rawMethod));
                                        if ($methodDisplay == 'Manual' || $methodDisplay == '') $methodDisplay = 'MANUAL';
                                        $colorClass = 'bg-[#f4f9ff] text-[#8faaf3] border-white';
                                    }
                                @endphp
                                <span style="font-size: 9px;" class="font-black uppercase tracking-widest px-3 py-1.5 rounded-full border-2 shadow-sm inline-block {{ $colorClass }}">
                                    {{ $methodDisplay }}
                                </span>
                            </td>
                            
                            <!-- Status -->
                            <td class="px-5 py-4 sm:py-5 align-middle text-center min-w-[120px]">
                                @php
                                    // Status pada deposit umumnya adalah paid, unpaid, failed, atau expired
                                    $statusLabels = [
                                        'paid' => ['bg-[#e6fff7]', 'text-emerald-500', 'Berhasil'],
                                        'success' => ['bg-[#e6fff7]', 'text-emerald-500', 'Berhasil'],
                                        'unpaid' => ['bg-[#fff5eb]', 'text-amber-500', 'Pending'],
                                        'pending' => ['bg-[#fff5eb]', 'text-amber-500', 'Pending'],
                                        'failed' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', 'Gagal'],
                                        'expired' => ['bg-[#ffe5e5]', 'text-[#ff6b6b]', 'Expired'],
                                    ];
                                    $rawStatus = strtolower($deposit->status ?? 'pending');
                                    $st = $statusLabels[$rawStatus] ?? ['bg-gray-100', 'text-gray-500', $rawStatus];
                                @endphp
                                <span style="font-size: 9px;" class="{{ $st[0] }} {{ $st[1] }} px-4 py-1.5 rounded-full font-black uppercase tracking-widest border-2 border-white shadow-sm inline-block w-full text-center">
                                    {{ $st[2] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 sm:py-20 text-center">
                                <div class="mb-4 sm:mb-5 inline-flex h-20 w-20 sm:h-24 sm:w-24 items-center justify-center rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-[#f0f5ff] text-4xl sm:text-5xl shadow-inner animate-float">💰</div>
                                <p class="text-lg sm:text-xl font-black text-[#5a76c8]">Belum ada riwayat deposit.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Paginasi -->
        @if(isset($deposits) && method_exists($deposits, 'links') && $deposits->hasPages())
            <div class="border-t-4 border-dashed border-[#f4f9ff] bg-[#f8faff] p-5 sm:p-8 w-full min-w-0">
                {{ $deposits->links() }}
            </div>
        @endif
    </div>
</div>
@endsection