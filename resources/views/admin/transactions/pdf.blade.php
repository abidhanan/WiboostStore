<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan Wiboost</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px dashed #bde0fe; }
        .header h2 { margin: 0; color: #2b3a67; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #5a76c8; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e0fbfc; padding: 10px; text-align: left; }
        th { background-color: #f4f9ff; color: #5a76c8; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .total-row { background-color: #e6fff7; font-weight: bold; color: #2b3a67; font-size: 14px; }
        .badge-success { color: #10b981; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Wiboost Store - Rekapitulasi Pendapatan</h2>
        <p>Periode: Laporan Bulan {{ $monthName }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Tanggal & Waktu</th>
                <th>No. Invoice</th>
                <th>Pelanggan</th>
                <th>Produk Layanan</th>
                <th class="text-center">Status</th>
                <th class="text-right">Nominal Pemasukan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $trx)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $trx->created_at->format('d/m/Y H:i') }} WIB</td>
                <td>{{ $trx->invoice_number }}</td>
                <td>{{ $trx->user->name ?? 'Guest' }}</td>
                <td>{{ $trx->product->name ?? 'Produk Dihapus' }}</td>
                <td class="text-center badge-success">LUNAS</td>
                <td class="text-right">Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 30px;">Tidak ada transaksi sukses pada periode ini.</td>
            </tr>
            @endforelse
            
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL PENDAPATAN BULAN INI:</td>
                <td class="text-right">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right; font-size: 11px; color: #888;">
        <p>Dicetak otomatis oleh Sistem Wiboost pada {{ date('d F Y, H:i') }} WIB</p>
    </div>
</body>
</html>