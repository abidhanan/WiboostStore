@extends('layouts.user')

@section('title', 'Riwayat Pesanan')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    
    .modal-active { opacity: 1 !important; pointer-events: auto !important; }
    .modal-content-active { transform: scale(1) !important; }
    
    @keyframes pulse-soft {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    .animate-pulse-soft { animation: pulse-soft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    .hide-scroll::-webkit-scrollbar { display: none; }
</style>

<div class="wiboost-font pb-12 max-w-4xl mx-auto mt-4">
    <div class="mb-10 pl-2">
        <h2 class="text-3xl font-black text-[#2b3a67] tracking-tight">Riwayat Pesanan 🛒</h2>
        <p class="text-[#8faaf3] font-bold text-sm mt-1">Klik tiket pesanan untuk melihat detail atau melanjutkan pembayaran.</p>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
            <span class="text-2xl">🎉</span> {{ session('success') }}
        </div>
    @endif

    <div class="space-y-5">
        @forelse($transactions as $trx)
            
            @php
                $statusLabel = '';
                $statusClass = '';
                $isUnpaid = false;
                $credentials = null;

                if ($trx->payment_status == 'unpaid') {
                    $statusLabel = 'Menunggu Bayar';
                    $statusClass = 'bg-[#fff5eb] text-amber-500 border-amber-200 animate-pulse-soft';
                    $isUnpaid = true;
                } elseif ($trx->payment_status == 'failed' || $trx->order_status == 'failed') {
                    $statusLabel = 'Gagal';
                    $statusClass = 'bg-[#ffe5e5] text-[#ff6b6b] border-red-200';
                } else {
                    if ($trx->order_status == 'success') {
                        $statusLabel = 'Sukses';
                        $statusClass = 'bg-[#e6fff7] text-emerald-500 border-emerald-200';
                        if(!empty($trx->credential_data)) {
                            $credentials = json_decode($trx->credential_data, true);
                        }
                    } elseif ($trx->order_status == 'processing') {
                        $statusLabel = 'Proses';
                        $statusClass = 'bg-[#e0fbfc] text-[#5a76c8] border-[#bde0fe]';
                    } else {
                        $statusLabel = 'Pending';
                        $statusClass = 'bg-[#f4f9ff] text-[#8faaf3] border-[#e0ebff]';
                    }
                }
            @endphp

            <div onclick="openModal('modal-{{ $trx->id }}')" class="bg-white rounded-[2.5rem] p-6 shadow-lg shadow-[#bde0fe]/20 border-4 border-white flex flex-col md:flex-row justify-between items-start md:items-center hover:border-[#bde0fe] hover:-translate-y-1 transition-all group cursor-pointer">
                
                <div class="mb-4 md:mb-0 w-full md:w-2/3 pr-0 md:pr-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-[10px] font-black text-white bg-[#5a76c8] px-3 py-1 rounded-full shadow-sm tracking-widest">{{ $trx->invoice_number }}</span>
                        <span class="text-[10px] font-bold text-[#8faaf3]">{{ $trx->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    
                    <h4 class="font-black text-lg md:text-xl text-[#2b3a67] line-clamp-1 group-hover:text-[#5a76c8] transition-colors">{{ $trx->product->name ?? 'Produk Dihapus' }}</h4>
                    
                    @if(!in_array($trx->product->process_type ?? '', ['account', 'number']))
                        <p class="text-sm text-[#8faaf3] font-bold truncate mt-1">Target: {{ $trx->target_data }}</p>
                    @endif
                </div>
                
                <div class="flex flex-row md:flex-col items-center md:items-end justify-between w-full md:w-auto bg-[#f8faff] md:bg-transparent p-4 md:p-0 rounded-[1.5rem] border-2 border-white md:border-0 shadow-inner md:shadow-none gap-3">
                    <p class="font-black text-[#5a76c8] text-xl">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                    <span class="{{ $statusClass }} px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm text-center min-w-[120px]">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>

            <div id="modal-{{ $trx->id }}" class="fixed inset-0 flex items-center justify-center bg-[#2b3a67]/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 p-4" style="z-index: 99999;">
                <div class="absolute inset-0" onclick="closeModal('modal-{{ $trx->id }}')"></div>
                
                <div class="relative bg-white w-full max-w-md rounded-[2.5rem] p-6 md:p-8 shadow-2xl transform scale-95 transition-transform duration-300 flex flex-col overflow-hidden max-h-[90vh]">
                    
                    <div class="flex justify-between items-center mb-6 pb-4 border-b-2 border-dashed border-[#f0f5ff]">
                        <h3 class="text-xl font-black text-[#2b3a67]">Detail Pesanan 🧾</h3>
                        <button onclick="closeModal('modal-{{ $trx->id }}')" class="w-10 h-10 bg-[#ffe5e5] text-[#ff6b6b] hover:bg-[#ff6b6b] hover:text-white rounded-xl flex items-center justify-center transition-colors font-black border-2 border-white">✕</button>
                    </div>

                    <div class="flex-1 overflow-y-auto hide-scroll space-y-5 pr-1">
                        
                        @if($credentials)
                            <div class="bg-[#fffcf0] p-5 rounded-[1.5rem] border-2 border-amber-200 shadow-sm relative overflow-hidden">
                                <div class="absolute -right-4 -top-4 text-6xl opacity-10">🎁</div>
                                <div class="relative z-10">
                                    <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-3 flex items-center gap-1">
                                        <span>✨</span> Data Pesanan Kamu
                                    </p>

                                    <div class="space-y-3">
                                        @foreach(['email' => 'Email / Username / Nomor', 'password' => 'Password', 'profile' => 'Profil', 'pin' => 'PIN', 'link' => 'Link Akses'] as $key => $label)
                                            @if(!empty($credentials[$key]))
                                                <div>
                                                    <p class="text-[9px] font-black text-amber-600/70 uppercase tracking-widest mb-1">{{ $label }}</p>
                                                    <div class="bg-white px-4 py-2.5 rounded-xl border-2 border-amber-100 flex justify-between items-center group">
                                                        <p class="font-black text-[#2b3a67] text-sm break-all">{{ $credentials[$key] }}</p>
                                                        <button onclick="copyToClipboard('{{ addslashes($credentials[$key]) }}')" class="text-amber-400 hover:text-amber-600 transition-colors" title="Salin">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    @if(isset($credentials['type']) && $credentials['type'] == 'number')
                                        <div class="mt-5 bg-white p-4 rounded-xl border-2 border-amber-100 flex flex-col gap-2">
                                            <p class="text-[10px] font-black text-amber-600 leading-tight">💬 Nomor sudah aktif. Untuk mendapatkan kode OTP, silakan hubungi Admin OTP kami.</p>
                                            <a href="https://wa.me/6285326513324?text=Halo%20Admin,%20saya%20ingin%20meminta%20kode%20OTP%20untuk%20nomor%20{{ $credentials['email'] ?? '' }}%20(Invoice:%20{{ $trx->invoice_number }})" target="_blank" class="mt-1 bg-[#25D366] text-white text-xs font-black py-2.5 rounded-lg text-center hover:bg-[#1eb956] transition-colors flex items-center justify-center gap-2">
                                                Minta Kode OTP Sekarang
                                            </a>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @endif

                        <div class="bg-[#f4f9ff] p-5 rounded-[1.5rem] border-2 border-white shadow-inner">
                            <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-1">Invoice</p>
                            <p class="font-black text-[#5a76c8] text-lg">{{ $trx->invoice_number }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-1">Layanan</p>
                            <p class="font-black text-[#2b3a67] text-base leading-tight">{{ $trx->product->name ?? 'Produk Dihapus' }}</p>
                        </div>

                        @if(!in_array($trx->product->process_type ?? '', ['account', 'number']))
                            <div>
                                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-1">Target / Tujuan</p>
                                <div class="bg-[#f0f5ff] p-3 rounded-xl border border-white">
                                    <p class="font-black text-[#2b3a67] text-sm break-all line-clamp-2">{{ $trx->target_data }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-1">Total</p>
                                <p class="font-black text-[#4bc6b9] text-lg">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-1">Tanggal</p>
                                <p class="font-bold text-[#2b3a67] text-sm">{{ $trx->created_at->format('d/m/Y, H:i') }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-2">Status Saat Ini</p>
                            <span class="{{ $statusClass }} px-4 py-2 rounded-full text-xs font-black uppercase tracking-widest border shadow-sm inline-block">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t-2 border-dashed border-[#f0f5ff] shrink-0">
                        @if($isUnpaid)
                            @if(!empty($trx->snap_token))
                                <button type="button" onclick="payWithMidtrans('{{ $trx->snap_token }}', '{{ $trx->id }}')" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white py-4 rounded-[1.5rem] font-black transition-transform active:scale-95 shadow-lg shadow-[#4bc6b9]/30 border-2 border-white flex justify-center items-center gap-2 text-lg mb-3">Bayar Sekarang 🚀</button>
                            @else
                                <a href="https://wa.me/6285326513324?text=Halo%20Admin,%20saya%20mau%20bayar%20manual%20untuk%20invoice%20{{ $trx->invoice_number }}" target="_blank" class="w-full bg-[#25D366] hover:bg-[#1eb956] text-white py-4 rounded-[1.5rem] font-black transition-transform active:scale-95 shadow-lg border-2 border-white flex justify-center items-center gap-2 text-lg mb-3">Hubungi Admin 💬</a>
                            @endif
                            <button onclick="closeModal('modal-{{ $trx->id }}')" class="w-full bg-[#f4f9ff] text-[#5a76c8] py-3 rounded-[1.5rem] font-black text-sm hover:bg-[#e0ebff] transition-colors">Nanti Saja</button>
                        @else
                            <button onclick="closeModal('modal-{{ $trx->id }}')" class="w-full bg-[#f4f9ff] hover:bg-[#e0ebff] text-[#5a76c8] py-4 rounded-[1.5rem] font-black transition-colors border-2 border-white shadow-sm text-lg">Tutup Detail</button>
                        @endif
                    </div>
                </div>
            </div>

        @empty
            <div class="text-center py-24 bg-white rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] shadow-sm">
                <div class="text-7xl mb-4 opacity-50 inline-block w-24 h-24 bg-[#f0f5ff] rounded-full flex items-center justify-center mx-auto border-4 border-white shadow-inner">🛒</div>
                <h3 class="text-xl font-black text-[#5a76c8] mb-2 mt-4">Belum Ada Pesanan</h3>
                <p class="text-[#8faaf3] font-bold max-w-sm mx-auto px-4">Kamu belum pernah jajan di sini. Yuk, mulai buat pesanan pertamamu!</p>
                <a href="{{ route('user.dashboard') }}" class="inline-block mt-8 bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black px-8 py-4 rounded-full transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-2 border-white">
                    Lihat Katalog Layanan
                </a>
            </div>
        @endforelse
    </div>
    
    @if(method_exists($transactions, 'links') && $transactions->hasPages())
        <div class="mt-10 p-4 bg-white rounded-[2rem] shadow-sm border-2 border-white">
            {{ $transactions->links() }}
        </div>
    @endif
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modals = document.querySelectorAll('[id^="modal-"]');
        modals.forEach(modal => document.body.appendChild(modal));

        // LOGIKA AUTO-OPEN MODAL TRANSAKSI
        // 1. Jika pengguna bayar via Saldo Wiboost (didapat dari Session)
        @if(session('new_trx_id'))
            setTimeout(() => {
                openModal('modal-{{ session('new_trx_id') }}');
            }, 300);
        @endif

        // 2. Jika pengguna bayar via Midtrans (didapat dari URL Parameter)
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.has('auto_open')) {
            setTimeout(() => {
                openModal('modal-' + urlParams.get('auto_open'));
            }, 300);
            // Bersihkan URL agar modal tidak terbuka terus-terusan saat di-refresh
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) {
            modal.classList.add('modal-active');
            modal.querySelector('.transform').classList.add('modal-content-active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) {
            modal.classList.remove('modal-active');
            modal.querySelector('.transform').classList.remove('modal-content-active');
            document.body.style.overflow = '';
        }
    }

    // Fungsi Midtrans diperbarui agar mengirim URL Parameter ?auto_open=id_transaksi
    function payWithMidtrans(snapToken, trxId) {
        if(!snapToken) return alert("Maaf, token pembayaran tidak ditemukan. Hubungi Admin.");
        snap.pay(snapToken, {
            onSuccess: (r) => { window.location.href = window.location.pathname + '?auto_open=' + trxId; },
            onPending: (r) => { window.location.href = window.location.pathname + '?auto_open=' + trxId; },
            onError: (r) => alert("Pembayaran gagal, silakan coba lagi.")
        });
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => alert('Data berhasil disalin ke clipboard! 📋'), (err) => alert('Gagal menyalin teks: ', err));
    }
</script>
@endsection