<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan Wiboost</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #2b3a67; margin: 0; padding: 20px; background-color: #ffffff; }
        .header-container { text-align: center; margin-bottom: 30px; padding: 20px; background-color: #f4f9ff; border: 3px solid #e0fbfc; border-radius: 20px; }
        .header-container h2 { margin: 0 0 5px 0; color: #5a76c8; font-size: 26px; font-weight: bold; }
        .header-container p { margin: 0; color: #8faaf3; font-weight: bold; font-size: 14px; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 10px; border: 2px solid #bde0fe; border-radius: 15px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px dashed #e0fbfc; }
        th { background-color: #f4f9ff; color: #5a76c8; font-weight: bold; text-transform: uppercase; font-size: 10px; letter-spacing: 1px; border-bottom: 3px solid #bde0fe; }
        tr:last-child td { border-bottom: none; }
        tr:nth-child(even) td { background-color: #fcfdfe; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .nowrap { white-space: nowrap; }
        .total-row td { background-color: #e6fff7; font-weight: bold; color: #10b981; font-size: 14px; border-top: 3px solid #bde0fe; }
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #8faaf3; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header-container">
        <h1>Wiboost Store - Rekapitulasi Pendapatan</h1>
        <p>Periode Laporan: {{ $monthName }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Waktu Transaksi</th>
                <th>No. Invoice</th>
                <th>Nama Pelanggan</th>
                <th>Produk</th>
                <th class="text-right nowrap">Nominal Masuk</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $trx)
                <tr>
                    <td class="text-center" style="font-weight: bold; color: #8faaf3;">{{ $index + 1 }}</td>
                    <td style="color: #4a5f96; font-size: 11px;">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                    <td style="font-weight: bold; color: #5a76c8;">{{ $trx->invoice_number }}</td>
                    <td style="font-weight: bold;">{{ $trx->user->name ?? 'Guest' }}</td>
                    <td style="color: #4a5f96;">{{ $trx->display_product_name }}</td>
                    <td class="text-right nowrap" style="font-weight: bold; color: #10b981;">Rp {{ number_format($trx->display_total_payable, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 40px; color: #8faaf3; font-weight: bold; font-size: 14px;">Tidak ada transaksi sukses pada periode ini.</td>
                </tr>
            @endforelse

            <tr class="total-row">
                <td colspan="5" class="text-right nowrap">TOTAL PENDAPATAN :</td>
                <td class="text-right nowrap">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak otomatis oleh Sistem Wiboost pada {{ date('d F Y, H:i') }} WIB</p>
    </div>
</body>
</html>
