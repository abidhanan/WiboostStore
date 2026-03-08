@extends('layouts.user')

@section('title', 'Wiboost Wallet')

@section('content')
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-900">Dompet Wiboost</h2>
    <p class="text-gray-500 mt-1">Top up saldo untuk transaksi yang lebih cepat tanpa ribet transfer berkali-kali.</p>
</div>

@if(session('success'))
    <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-4 py-3 rounded-xl mb-6 font-bold flex items-center gap-3">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-3xl p-6 shadow-xl shadow-indigo-200 text-white relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-indigo-100 text-sm font-bold uppercase tracking-widest mb-1">Total Saldo</p>
                <h3 class="text-4xl font-extrabold tracking-tight mb-4">Rp {{ number_format(Auth::user()->balance, 0, ',', '.') }}</h3>
                <div class="flex items-center gap-2 text-xs font-medium bg-white/20 w-max px-3 py-1.5 rounded-full backdrop-blur-sm border border-white/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Aman & Terenkripsi
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-10">
                <svg width="150" height="150" viewBox="0 0 24 24" fill="white"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
            <h4 class="font-bold text-gray-800 mb-4">Top Up Saldo</h4>
            <form action="{{ route('user.wallet.topup') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-2">Pilih Nominal</label>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <button type="button" onclick="setNominal(20000)" class="py-2 border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:border-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 transition">20K</button>
                        <button type="button" onclick="setNominal(50000)" class="py-2 border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:border-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 transition">50K</button>
                        <button type="button" onclick="setNominal(100000)" class="py-2 border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:border-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 transition">100K</button>
                        <button type="button" onclick="setNominal(500000)" class="py-2 border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:border-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 transition">500K</button>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 font-bold text-gray-500">Rp</span>
                        <input type="number" name="amount" id="amount_input" min="10000" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-gray-800" placeholder="Atau ketik nominal..." required>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">*Minimal top up Rp 10.000</p>
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white py-3.5 rounded-xl font-bold hover:bg-black transition shadow-lg active:scale-95">
                    Lanjutkan Pembayaran
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                <h4 class="font-bold text-gray-800">Riwayat Top Up</h4>
            </div>
            <div class="overflow-x-auto flex-1 p-6">
                @forelse($deposits as $deposit)
                    <div class="flex items-center justify-between p-4 mb-3 border border-gray-100 rounded-2xl hover:bg-gray-50 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $deposit->payment_status == 'paid' ? 'bg-emerald-100 text-emerald-600' : 'bg-indigo-100 text-indigo-600' }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            </div>
                            <div>
                                <h5 class="font-bold text-gray-800">Top Up Saldo</h5>
                                <p class="text-xs font-mono text-gray-500 mt-0.5">{{ $deposit->invoice_number }} &bull; {{ $deposit->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-900 text-lg">+ Rp {{ number_format($deposit->amount, 0, ',', '.') }}</p>
                            @if($deposit->payment_status == 'paid')
                                <span class="text-emerald-500 text-xs font-bold uppercase mt-1 inline-block">Berhasil</span>
                            @elseif($deposit->payment_status == 'failed')
                                <span class="text-rose-500 text-xs font-bold uppercase mt-1 inline-block">Gagal</span>
                            @else
                                <span class="text-amber-500 text-xs font-bold uppercase mt-1 inline-block border border-amber-200 bg-amber-50 px-2 py-0.5 rounded-md">Menunggu Bayar</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                        <p class="text-gray-500 font-medium text-sm">Belum ada riwayat top up saldo.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi untuk tombol nominal cepat
    function setNominal(amount) {
        document.getElementById('amount_input').value = amount;
    }
</script>
@endsection