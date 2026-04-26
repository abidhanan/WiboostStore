@extends('layouts.user')

@section('title', 'Wiboost Wallet')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .bg-wiboost-sky { background: linear-gradient(180deg, #bde0fe 0%, #e0fbfc 100%); }
</style>

<div class="wiboost-font pb-12 max-w-7xl mx-auto">
    <div class="mb-8 pl-2">
        <h2 class="text-3xl font-black text-[#2b3a67] tracking-tight">Dompet Anda</h2>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
            <span class="text-2xl">🎉</span> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
            <span class="text-2xl">⚠️</span> {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 space-y-8">
            
            <div class="bg-gradient-to-br from-[#8faaf3] to-[#5a76c8] rounded-[2.5rem] p-8 shadow-xl shadow-[#5a76c8]/30 text-white relative overflow-hidden border-4 border-white">
                <div class="relative z-10">
                    <p class="text-[#e0fbfc] text-xs font-black uppercase tracking-widest mb-2">Total Saldo Tersedia</p>
                    <h3 class="text-4xl font-black tracking-tight mb-5 drop-shadow-sm">Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}</h3>
                    <div class="inline-flex items-center gap-2 text-xs font-bold bg-white/20 px-4 py-2 rounded-full backdrop-blur-sm border border-white/30 shadow-inner">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Aman & Terenkripsi
                    </div>
                </div>
                <div class="absolute -right-10 -bottom-10 text-9xl opacity-20 transform -rotate-12">☁️</div>
            </div>

            <div class="bg-white rounded-[2rem] p-6 shadow-lg shadow-[#bde0fe]/30 border-4 border-white">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <p class="text-[#8faaf3] text-xs font-black uppercase tracking-widest mb-1">Poin Wiboost ⭐</p>
                        <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">{{ Auth::user()->points ?? 0 }} <span class="text-sm font-bold text-[#8faaf3]">Pts</span></h3>
                    </div>
                    <div class="text-4xl animate-bounce">🎁</div>
                </div>

                @if((Auth::user()->points ?? 0) >= 5)
                    <form action="{{ route('user.wallet.exchange') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white py-3.5 rounded-[1.5rem] font-black transition-transform active:scale-95 shadow-md shadow-[#4bc6b9]/30 border-2 border-white flex items-center justify-center gap-2">
                            Tukar 5 Poin &rarr; Saldo 1K
                        </button>
                    </form>
                @else
                    <div class="w-full bg-[#f8faff] border-2 border-dashed border-[#bde0fe] text-[#8faaf3] py-3.5 rounded-[1.5rem] font-black text-center text-sm">
                        Kumpulkan {{ 5 - (Auth::user()->points ?? 0) }} Poin Lagi!
                    </div>
                @endif
                <p class="text-[9px] font-bold text-[#8faaf3] mt-3 text-center uppercase tracking-widest">1 Poin didapat tiap transaksi sukses.</p>
            </div>

            <div class="bg-white rounded-[2rem] p-6 border-4 border-white shadow-lg shadow-[#bde0fe]/30">
                <h4 class="font-black text-[#2b3a67] text-xl mb-5 ml-2">Isi Saldo Cepat</h4>
                <form action="{{ route('user.wallet.topup') }}" method="POST">
                    @csrf
                    <div class="mb-5">
                        <label class="block text-sm font-black text-[#8faaf3] mb-3 ml-2">Pilih Nominal Instan</label>
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <button type="button" onclick="setNominal(20000)" class="py-2.5 bg-[#f4f9ff] border-2 border-transparent hover:border-[#bde0fe] hover:bg-[#e0fbfc] rounded-[1.5rem] text-sm font-black text-[#5a76c8] transition-all">20K</button>
                            <button type="button" onclick="setNominal(50000)" class="py-2.5 bg-[#f4f9ff] border-2 border-transparent hover:border-[#bde0fe] hover:bg-[#e0fbfc] rounded-[1.5rem] text-sm font-black text-[#5a76c8] transition-all">50K</button>
                            <button type="button" onclick="setNominal(100000)" class="py-2.5 bg-[#f4f9ff] border-2 border-transparent hover:border-[#bde0fe] hover:bg-[#e0fbfc] rounded-[1.5rem] text-sm font-black text-[#5a76c8] transition-all">100K</button>
                            <button type="button" onclick="setNominal(500000)" class="py-2.5 bg-[#f4f9ff] border-2 border-transparent hover:border-[#bde0fe] hover:bg-[#e0fbfc] rounded-[1.5rem] text-sm font-black text-[#5a76c8] transition-all">500K</button>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-5 font-black text-[#5a76c8]">Rp</span>
                            <input type="number" name="amount" id="amount_input" min="10000" required
                                   class="w-full pl-12 pr-5 py-4 bg-[#f4f9ff] rounded-[1.5rem] border-2 border-[#e0fbfc] focus:border-[#5a76c8] outline-none font-black text-lg text-[#2b3a67] transition placeholder-[#a3bbfb]" 
                                   placeholder="ketik nominal">
                        </div>
                        <p class="text-xs font-bold text-amber-500 mt-2 ml-2">*Min. isi Rp 10.000</p>
                    </div>
                    <button type="submit" class="w-full bg-[#5a76c8] text-white py-4 rounded-[1.5rem] font-black hover:bg-[#4760a9] transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-2 border-white">
                        Lanjut Bayar 🚀
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white h-full flex flex-col overflow-hidden">
                <div class="px-8 py-6 border-b-2 border-dashed border-[#f0f5ff] flex justify-between items-center bg-[#f4f9ff]">
                    <h4 class="font-black text-xl text-[#2b3a67]">Mutasi Saldo Terbaru 💰</h4>
                    <span class="text-[10px] font-black bg-white px-3 py-1 rounded-full text-[#8faaf3] border border-[#f0f5ff]">SEMUA TRANSAKSI</span>
                </div>
                
                <div class="flex-1 p-6 space-y-4 overflow-y-auto max-h-[700px] hide-scroll">
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

                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between p-5 bg-white border-2 border-[#f4f9ff] rounded-[1.5rem] hover:border-[#bde0fe] hover:-translate-y-1 transition-all group shadow-sm">
                            
                            <div class="flex items-center gap-4 mb-3 md:mb-0">
                                <div class="w-12 h-12 shrink-0 rounded-2xl flex items-center justify-center text-xl font-black border-2 border-white shadow-inner {{ $iconClass }}">
                                    {{ $iconChar }}
                                </div>
                                <div>
                                    <h5 class="font-black text-[#2b3a67] text-sm md:text-base group-hover:text-[#5a76c8] transition-colors line-clamp-2 leading-tight">
                                        {{ $displayDesc }}
                                    </h5>
                                    <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mt-1">
                                        {{ $log->invoice_number ?? '-' }} &bull; {{ $log->created_at->format('d M, H:i') }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="text-left md:text-right w-full md:w-auto pl-16 md:pl-0 flex flex-row md:flex-col justify-between md:justify-end items-center md:items-end">
                                <p class="font-black text-lg mb-1 {{ $log->type == 'purchase' ? 'text-[#ff6b6b]' : 'text-emerald-500' }}">
                                    {{ $log->type == 'purchase' ? '-' : '+' }} Rp {{ number_format($log->amount, 0, ',', '.') }}
                                </p>
                                <span class="text-[9px] font-black uppercase px-2.5 py-1 rounded-md bg-[#f4f9ff] text-[#8faaf3] border border-white shadow-sm inline-block">
                                    {{ $log->type }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-20">
                            <div class="text-6xl mb-4 opacity-30">🪫</div>
                            <p class="text-[#8faaf3] font-bold text-lg">Belum ada mutasi saldo masuk/keluar.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
</style>
<script>
    function setNominal(amount) {
        document.getElementById('amount_input').value = amount;
    }
</script>
@endsection