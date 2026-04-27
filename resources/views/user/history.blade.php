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

<div class="wiboost-font pb-12 max-w-4xl mx-auto mt-4 relative z-10">
    
    <div class="mb-10 text-center md:text-left pl-2">
        <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">Catatan Transaksi</div>
        <h2 class="text-3xl sm:text-4xl font-black text-[#2b3a67] tracking-tight drop-shadow-sm">Riwayat Pesanan 🧾</h2>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-5 rounded-[2.5rem] mb-8 font-black flex items-center gap-4 shadow-lg shadow-emerald-100/50">
            <span class="text-3xl drop-shadow-sm">✅</span> {{ session('success') }}
        </div>
    @endif

    <div class="space-y-6 relative z-10">
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

                        if (! empty($trx->credential_data)) {
                            $credentials = is_array($trx->credential_data)
                                ? $trx->credential_data
                                : json_decode($trx->credential_data, true);
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

            <div onclick="openModal('modal-{{ $trx->id }}')" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] p-6 md:p-8 shadow-xl shadow-[#bde0fe]/30 border-4 border-white flex flex-col md:flex-row justify-between items-start md:items-center hover:border-[#bde0fe] hover:-translate-y-2 transition-all duration-300 group cursor-pointer relative overflow-hidden">
                <div class="mb-4 md:mb-0 w-full md:w-2/3 pr-0 md:pr-4 relative z-10">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-[10px] font-black text-[#5a76c8] bg-[#f4f9ff] px-4 py-1.5 rounded-full border-2 border-white shadow-sm tracking-widest">{{ $trx->invoice_number }}</span>
                        <span class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">{{ $trx->created_at->format('d M Y, H:i') }}</span>
                    </div>

                    <h4 class="font-black text-xl md:text-2xl text-[#2b3a67] line-clamp-2 group-hover:text-[#5a76c8] transition-colors leading-tight drop-shadow-sm">{{ $trx->product->name ?? 'Produk Dihapus' }}</h4>

                    @if($trx->has_order_input && filled($trx->order_input_summary))
                        <p class="text-xs text-[#8faaf3] font-bold truncate mt-3 bg-[#f8faff] inline-block px-4 py-2 rounded-xl border border-white shadow-inner">Input: {{ $trx->order_input_summary }}</p>
                    @endif
                </div>

                <div class="flex flex-row md:flex-col items-center md:items-end justify-between w-full md:w-auto bg-[#f8faff] md:bg-transparent p-5 md:p-0 rounded-[1.5rem] border-2 border-white md:border-0 shadow-inner md:shadow-none gap-4 relative z-10">
                    <p class="font-black text-[#5a76c8] text-2xl drop-shadow-sm">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                    <span class="{{ $statusClass }} px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest border-2 shadow-sm text-center min-w-[130px]">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>

            <div id="modal-{{ $trx->id }}" class="fixed inset-0 flex items-center justify-center bg-[#2b3a67]/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 p-4" style="z-index: 99999;">
                <div class="absolute inset-0" onclick="closeModal('modal-{{ $trx->id }}')"></div>

                <div class="relative bg-white/95 backdrop-blur-md w-full max-w-md rounded-[2.5rem] p-6 md:p-8 shadow-2xl transform scale-90 transition-transform duration-300 flex flex-col overflow-hidden max-h-[90vh] border-4 border-white">
                    <div class="flex justify-between items-center mb-6 pb-4 border-b-4 border-dashed border-[#f4f9ff]">
                        <h3 class="text-xl font-black text-[#2b3a67] drop-shadow-sm">Detail Pesanan 🧾</h3>
                        <button onclick="closeModal('modal-{{ $trx->id }}')" class="w-12 h-12 bg-[#ffe5e5] text-[#ff6b6b] hover:bg-[#ff6b6b] hover:text-white rounded-[1.2rem] flex items-center justify-center transition-colors font-black border-4 border-white shadow-sm text-xl shrink-0 active:scale-95">✕</button>
                    </div>

                    <div class="flex-1 overflow-y-auto hide-scroll space-y-5 pr-1 pb-4">
                        @if($trx->order_status == 'failed' && !empty($trx->target_notes))
                            <div class="bg-[#ffe5e5] p-5 rounded-[1.5rem] border-4 border-white shadow-inner relative overflow-hidden mb-5">
                                <div class="relative z-10">
                                    <p class="text-[10px] font-black text-[#ff6b6b] uppercase tracking-widest mb-1">Keterangan Sistem</p>
                                    <p class="font-bold text-[#2b3a67] text-sm leading-tight">{{ $trx->target_notes }}</p>
                                </div>
                            </div>
                        @endif

                        @if($credentials)
                            <div class="bg-[#fffcf0] p-6 rounded-[2rem] border-4 border-white shadow-md shadow-amber-100/50 relative overflow-hidden">
                                <div class="relative z-10">
                                    <div class="flex items-center gap-3 mb-5">
                                        <span class="text-2xl drop-shadow-sm">🔑</span>
                                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Akses Kredensial Kamu</p>
                                    </div>

                                    <div class="space-y-4">
                                        @foreach(['email' => 'Nomor / Akun', 'password' => 'Password', 'profile' => 'Profil', 'pin' => 'PIN', 'link' => 'Link Akses'] as $key => $label)
                                            @if(!empty($credentials[$key]))
                                                <div>
                                                    <p class="text-[9px] font-black text-amber-600/70 uppercase tracking-widest mb-1 ml-1">{{ $label }}</p>
                                                    <div class="bg-white px-5 py-3.5 rounded-[1.2rem] border-4 border-amber-100 flex justify-between items-center group shadow-sm">
                                                        <p class="font-black text-[#2b3a67] text-sm break-all">{{ $credentials[$key] }}</p>
                                                        <button onclick="copyToClipboard('{{ addslashes($credentials[$key]) }}')" class="text-amber-400 hover:text-amber-600 transition-colors bg-amber-50 p-2 rounded-xl border border-amber-100 active:scale-95" title="Salin">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    @if(!empty($credentials['tutorial_link']) || (isset($credentials['needs_otp']) && $credentials['needs_otp']))
                                        <div class="mt-6 space-y-3">
                                            @if(!empty($credentials['tutorial_link']))
                                                <a href="{{ $credentials['tutorial_link'] }}" target="_blank" class="w-full bg-[#f4f9ff] border-4 border-white hover:border-[#bde0fe] hover:bg-[#e0fbfc] text-[#5a76c8] font-black text-xs py-4 rounded-[1.5rem] flex items-center justify-center gap-2 transition-colors shadow-sm active:scale-95">
                                                    <span>Panduan</span> Cara Pakai 📖
                                                </a>
                                            @endif

                                            @if(isset($credentials['needs_otp']) && $credentials['needs_otp'] == true)
                                                <div class="bg-white p-5 rounded-[1.5rem] border-4 border-amber-100 flex flex-col gap-2 shadow-sm text-center">
                                                    <p class="text-[10px] font-black text-amber-600 leading-relaxed uppercase tracking-widest">⚠️ Pesanan ini butuh bantuan login admin. Cek tutorial atau minta OTP ke admin.</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="bg-[#f4f9ff] p-6 rounded-[1.5rem] border-4 border-white shadow-inner flex justify-between items-center">
                            <div>
                                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-1">Invoice</p>
                                <p class="font-black text-[#5a76c8] text-xl drop-shadow-sm">{{ $trx->invoice_number }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-[#f8faff] p-5 rounded-[1.5rem] border-4 border-white shadow-sm flex flex-col justify-center">
                                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-2">Metode Bayar</p>
                                @php
                                    $rawMethod = strtolower($trx->payment_method ?? '');
                                    if ($rawMethod == 'wallet') {
                                        $methodDisplay = 'SALDO';
                                        $colorClass = 'bg-[#e0fbfc] text-[#5a76c8] border-[#bde0fe]';
                                    } elseif (($rawMethod == 'manual' || empty($rawMethod)) && $trx->payment_status != 'paid') {
                                        $methodDisplay = 'BELUM PILIH';
                                        $colorClass = 'bg-[#fff5eb] text-amber-500 border-amber-200';
                                    } else {
                                        $methodMap = [
                                            'qris' => 'QRIS', 'gopay' => 'GoPay', 'shopeepay' => 'ShopeePay',
                                            'bank_transfer' => 'Bank', 'cstore' => 'ALFA/INDOMARET',
                                            'credit_card' => 'Kartu Kredit', 'echannel' => 'Mandiri Bill',
                                            'permata_va' => 'Permata VA', 'bca_va' => 'BCA VA', 'bni_va' => 'BNI VA',
                                            'bri_va' => 'BRI VA', 'cimb_va' => 'CIMB VA', 'other_va' => 'ATM Bersama',
                                        ];
                                        $methodDisplay = $methodMap[$rawMethod] ?? ucwords(str_replace('_', ' ', $rawMethod));
                                        if ($methodDisplay == 'Manual') $methodDisplay = 'E-WALLET';
                                        $colorClass = 'bg-white text-[#5a76c8] border-[#f0f5ff]';
                                    }
                                @endphp
                                <span class="text-[9px] font-black uppercase tracking-widest px-3 py-2 rounded-full border-2 shadow-sm inline-block text-center {{ $colorClass }}">
                                    {{ $methodDisplay }}
                                </span>
                            </div>
                            <div class="bg-[#f8faff] p-5 rounded-[1.5rem] border-4 border-white shadow-sm flex flex-col justify-center text-right">
                                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-2">Total Bayar</p>
                                <p class="font-black text-[#4bc6b9] text-xl leading-tight truncate drop-shadow-sm">Rp {{ number_format($trx->amount, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="bg-[#f8faff] p-6 rounded-[1.5rem] border-4 border-white shadow-sm">
                            <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-1">Produk</p>
                            <p class="font-black text-[#2b3a67] text-base leading-tight">{{ $trx->product->name ?? 'Produk Dihapus' }}</p>
                        </div>

                        @if($trx->has_order_input)
                            <div class="bg-[#f8faff] p-6 rounded-[1.5rem] border-4 border-white shadow-sm">
                                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-3">Input Buyer</p>
                                <div class="space-y-3">
                                    @foreach($trx->order_input_fields as $inputField)
                                        <div class="bg-white p-4 rounded-[1.2rem] border-2 border-[#f0f5ff] shadow-inner">
                                            <p class="text-[9px] font-black uppercase tracking-widest text-[#8faaf3] mb-1">{{ $inputField['label'] }}</p>
                                            <p class="font-black text-[#2b3a67] text-sm break-all">{{ $inputField['value'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center justify-between px-2 pt-2">
                            <div>
                                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-1">Waktu Transaksi</p>
                                <p class="font-bold text-[#2b3a67] text-sm">{{ $trx->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-black text-[#8faaf3] uppercase tracking-widest mb-2">Status Saat Ini</p>
                                <span class="{{ $statusClass }} px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest border-2 shadow-sm inline-block">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-5 border-t-4 border-dashed border-[#f4f9ff] shrink-0">
                        @if($isUnpaid)
                            @if(!empty($trx->snap_token))
                                <button type="button" onclick="payWithMidtrans('{{ $trx->snap_token }}', '{{ $trx->id }}')" class="w-full bg-[#4bc6b9] hover:bg-[#3ba398] text-white py-4 rounded-full font-black transition-transform active:scale-95 shadow-xl shadow-[#4bc6b9]/30 border-4 border-white flex justify-center items-center gap-2 text-lg mb-3">
                                    Bayar Sekarang 💳
                                </button>
                            @else
                                <div class="mb-3 rounded-[1.5rem] border-4 border-white bg-[#fff5eb] px-5 py-4 text-center text-sm font-black text-amber-600 shadow-sm">
                                    Tagihan belum memiliki link otomatis. Tunggu admin atau gunakan metode lain.
                                </div>
                            @endif
                            <button onclick="closeModal('modal-{{ $trx->id }}')" class="w-full bg-[#f4f9ff] hover:bg-[#e0ebff] text-[#5a76c8] py-3.5 rounded-full font-black text-sm transition-colors border-4 border-white shadow-sm active:scale-95">Nanti Saja</button>
                        @else
                            <button onclick="closeModal('modal-{{ $trx->id }}')" class="w-full bg-[#f4f9ff] hover:bg-[#5a76c8] hover:text-white text-[#5a76c8] py-4 rounded-full font-black transition-all duration-300 border-4 border-white shadow-md text-base active:scale-95">Tutup Detail</button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-20 bg-white/90 backdrop-blur-sm rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20 relative z-10">
                <div class="text-6xl mb-5 opacity-50 animate-float block">📦</div>
                <h3 class="text-2xl font-black text-[#5a76c8] mb-2 drop-shadow-sm">Belum Ada Pesanan</h3>
                <p class="text-[#8faaf3] font-bold max-w-sm mx-auto px-4 mb-8">Yuk, mulai buat pesanan pertamamu dan nikmati layanan kami!</p>
                <a href="{{ route('user.dashboard') }}" class="inline-flex items-center justify-center gap-2 bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black px-8 py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white">
                    Lihat Katalog Layanan 🚀
                </a>
            </div>
        @endforelse
    </div>

    @if(method_exists($transactions, 'links') && $transactions->hasPages())
        <div class="mt-10 p-5 bg-white/90 backdrop-blur-md rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white relative z-10">
            {{ $transactions->links() }}
        </div>
    @endif
</div>

<script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modals = document.querySelectorAll('[id^="modal-"]');
        modals.forEach(modal => document.body.appendChild(modal));

        @if(session('new_trx_id'))
            setTimeout(() => { openModal('modal-{{ session('new_trx_id') }}'); }, 300);
        @endif

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('auto_open')) {
            setTimeout(() => { openModal('modal-' + urlParams.get('auto_open')); }, 300);
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('modal-active');
            modal.querySelector('.transform').classList.add('modal-content-active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('modal-active');
            modal.querySelector('.transform').classList.remove('modal-content-active');
            document.body.style.overflow = '';
        }
    }

    function payWithMidtrans(snapToken, trxId) {
        if (!snapToken) {
            return alert('Maaf, token pembayaran tidak ditemukan.');
        }

        snap.pay(snapToken, {
            onSuccess: () => { window.location.href = window.location.pathname + '?auto_open=' + trxId; },
            onPending: () => { window.location.href = window.location.pathname + '?auto_open=' + trxId; },
            onError: () => alert('Pembayaran gagal, silakan coba lagi.'),
        });
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(
            () => alert('Data berhasil disalin ke clipboard.'),
            () => alert('Gagal menyalin teks.')
        );
    }
</script>
@endsection