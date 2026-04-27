@extends('layouts.user')

@section('title', 'Wiboost Wallet')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font pb-12 max-w-7xl mx-auto relative z-10">
    <div class="mb-8 text-center md:text-left pl-2">
        <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
            Keuangan
        </div>
        <h2 class="text-3xl sm:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm">Dompet Anda 💳</h2>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-lg shadow-emerald-100/50">
            <span class="text-2xl drop-shadow-sm">🎉</span> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-lg shadow-[#ff6b6b]/20">
            <span class="text-2xl drop-shadow-sm">⚠️</span> {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 space-y-8">
            
            <div class="bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] rounded-[2.5rem] p-8 shadow-xl shadow-[#5a76c8]/30 text-white relative overflow-hidden border-4 border-white hover:scale-[1.02] transition-transform duration-300">
                <div class="relative z-10">
                    <p class="text-[#e0fbfc] text-xs font-black uppercase tracking-widest mb-2">Total Saldo Tersedia</p>
                    <h3 class="text-4xl font-black tracking-tight mb-5 drop-shadow-md">Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}</h3>
                    <div class="inline-flex items-center gap-2 text-[10px] font-black bg-white/20 px-4 py-2 rounded-full backdrop-blur-sm border-2 border-white/40 shadow-inner uppercase tracking-wider">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Aman & Terenkripsi
                    </div>
                </div>
                <div class="absolute -right-10 -bottom-10 text-9xl opacity-20 transform -rotate-12 pointer-events-none animate-float">☁️</div>
            </div>

            <div class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] p-8 shadow-xl shadow-[#bde0fe]/30 border-4 border-white">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <p class="text-[#8faaf3] text-[10px] font-black uppercase tracking-widest mb-1">Poin Wiboost ⭐</p>
                        <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">{{ Auth::user()->points ?? 0 }} <span class="text-sm font-bold text-[#8faaf3]">Pts</span></h3>
                    </div>
                    <div class="text-4xl animate-bounce drop-shadow-sm">🎁</div>
                </div>

                @if((Auth::user()->points ?? 0) >= 5)
                    <form action="{{ route('user.wallet.exchange') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white py-4 rounded-full font-black transition-transform active:scale-95 shadow-lg shadow-[#4bc6b9]/30 border-4 border-white flex items-center justify-center gap-2">
                            Tukar 5 Poin &rarr; Saldo 1K
                        </button>
                    </form>
                @else
                    <div class="w-full bg-[#f8faff] border-4 border-dashed border-[#bde0fe] text-[#8faaf3] py-4 rounded-[1.5rem] font-black text-center text-sm shadow-inner">
                        Kumpulkan {{ 5 - (Auth::user()->points ?? 0) }} Poin Lagi!
                    </div>
                @endif
                <p class="text-[9px] font-bold text-[#8faaf3] mt-4 text-center uppercase tracking-widest">1 Poin didapat tiap transaksi sukses.</p>
            </div>

            <div class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] p-8 border-4 border-white shadow-xl shadow-[#bde0fe]/30">
                <div class="flex items-center gap-2 mb-6 ml-2">
                    <span class="text-2xl">⚡</span>
                    <h4 class="font-black text-[#2b3a67] text-xl">Isi Saldo Cepat</h4>
                </div>
                <form action="{{ route('user.wallet.topup') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-3 ml-2">Pilih Nominal Instan</label>
                        <div class="grid grid-cols-2 gap-3 mb-5">
                            <button type="button" onclick="setNominal(20000)" class="py-3 bg-[#f4f9ff] border-2 border-white hover:border-[#bde0fe] shadow-sm hover:bg-[#e0fbfc] rounded-[1.2rem] text-sm font-black text-[#5a76c8] transition-all active:scale-95">20K</button>
                            <button type="button" onclick="setNominal(50000)" class="py-3 bg-[#f4f9ff] border-2 border-white hover:border-[#bde0fe] shadow-sm hover:bg-[#e0fbfc] rounded-[1.2rem] text-sm font-black text-[#5a76c8] transition-all active:scale-95">50K</button>
                            <button type="button" onclick="setNominal(100000)" class="py-3 bg-[#f4f9ff] border-2 border-white hover:border-[#bde0fe] shadow-sm hover:bg-[#e0fbfc] rounded-[1.2rem] text-sm font-black text-[#5a76c8] transition-all active:scale-95">100K</button>
                            <button type="button" onclick="setNominal(500000)" class="py-3 bg-[#f4f9ff] border-2 border-white hover:border-[#bde0fe] shadow-sm hover:bg-[#e0fbfc] rounded-[1.2rem] text-sm font-black text-[#5a76c8] transition-all active:scale-95">500K</button>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-5 font-black text-[#5a76c8]">Rp</span>
                            <input type="number" name="amount" id="amount_input" min="10000" required
                                   class="w-full pl-12 pr-5 py-4 bg-[#f4f9ff] rounded-[1.5rem] border-4 border-white shadow-inner focus:border-[#bde0fe] outline-none font-black text-lg text-[#2b3a67] transition placeholder-[#a3bbfb]" 
                                   placeholder="ketik nominal">
                        </div>
                        <p class="text-[10px] font-black text-amber-500 mt-3 ml-3 uppercase tracking-widest">*Min. isi Rp 10.000</p>
                    </div>
                    <button type="submit" class="w-full bg-[#5a76c8] text-white py-4 rounded-full font-black hover:bg-[#4760a9] transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex justify-center items-center gap-2">
                        Lanjut Bayar 🚀
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white h-full flex flex-col overflow-hidden relative">
                
                <div class="px-8 py-6 border-b-4 border-dashed border-[#f4f9ff] flex flex-col md:flex-row md:justify-between items-start md:items-center bg-[#ffffff] gap-3 relative z-10">
                    <h4 class="font-black text-xl text-[#2b3a67]">Mutasi Saldo Terbaru 💸</h4>
                    <span class="text-[10px] font-black bg-[#f4f9ff] px-4 py-1.5 rounded-full text-[#8faaf3] border-2 border-white shadow-sm uppercase tracking-widest">Semua Transaksi</span>
                </div>
                
                <div class="flex-1 p-6 space-y-4 overflow-y-auto max-h-[700px] hide-scroll bg-[#f8faff]/50 relative z-10">
                    @php
                        $walletLogs = App\Models\WalletHistory::where('user_id', Auth::id())->latest()->take(20)->get();
                    @endphp

                    @forelse($walletLogs as $log)
                        @php
                            $displayDesc = $log->description;
                            
                            if ($log->type === 'topup') {
                                if (class_exists('\App\Models\Deposit')) {
                                    $deposit = \App\Models\Deposit::where('invoice_number', $log->invoice_number)->first();
                                    if ($deposit && $deposit->payment_method) {
                                        $method = ucwords(str_replace('_', ' ', $deposit->payment_method));
                                        if (strtolower($method) == 'qris') $method = 'QRIS';
                                        if (strtolower($method) == 'gopay') $method = 'GoPay';
                                        if (strtolower($method) == 'shopeepay') $method = 'ShopeePay';
                                        
                                        $displayDesc = "Top Up Saldo via " . $method;
                                    }
                                }
                            }

                            $iconClass = '';
                            $iconChar = '';
                            if($log->type == 'refund') {
                                $iconClass = 'bg-[#e6fff7] text-emerald-500'; $iconChar = '↺';
                            } elseif ($log->type == 'purchase') {
                                $iconClass = 'bg-[#ffe5e5] text-[#ff6b6b]'; $iconChar = '−';
                            } else {
                                $iconClass = 'bg-[#f0f5ff] text-[#5a76c8]'; $iconChar = '+';
                            }
                        @endphp

                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between p-5 bg-white border-2 border-white rounded-[1.5rem] hover:border-[#bde0fe] hover:-translate-y-1 transition-all duration-300 group shadow-md shadow-[#bde0fe]/10">
                            
                            <div class="flex items-center gap-4 mb-3 md:mb-0">
                                <div class="w-14 h-14 shrink-0 rounded-2xl flex items-center justify-center text-2xl font-black border-2 border-white shadow-inner group-hover:scale-110 transition-transform {{ $iconClass }}">
                                    {{ $iconChar }}
                                </div>
                                <div>
                                    <h5 class="font-black text-[#2b3a67] text-sm md:text-base group-hover:text-[#5a76c8] transition-colors line-clamp-2 leading-tight">
                                        {{ $displayDesc }}
                                    </h5>
                                    <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mt-1.5 flex items-center gap-1">
                                        <span class="bg-[#f4f9ff] px-2 py-0.5 rounded border border-white">{{ $log->invoice_number ?? '-' }}</span> &bull; {{ $log->created_at->format('d M, H:i') }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="text-left md:text-right w-full md:w-auto pl-16 md:pl-0 flex flex-row md:flex-col justify-between md:justify-end items-center md:items-end">
                                <p class="font-black text-xl mb-1 {{ $log->type == 'purchase' ? 'text-[#ff6b6b]' : 'text-emerald-500' }} drop-shadow-sm">
                                    {{ $log->type == 'purchase' ? '-' : '+' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                                </p>
                                <span class="text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full bg-[#f4f9ff] text-[#8faaf3] border-2 border-white shadow-sm inline-block">
                                    {{ $log->type }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-24 bg-white/50 rounded-[2rem] border-4 border-dashed border-[#bde0fe]">
                            <div class="text-6xl mb-4 opacity-50 animate-float">🪫</div>
                            <h3 class="text-xl font-black text-[#5a76c8] mb-1">Dompet Masih Kosong</h3>
                            <p class="text-[#8faaf3] font-bold text-sm">Belum ada mutasi saldo masuk ataupun keluar.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function setNominal(amount) {
        document.getElementById('amount_input').value = amount;
    }
</script>
@endsection