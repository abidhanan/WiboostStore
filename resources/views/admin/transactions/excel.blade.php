<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi Wiboost</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #2b3a67;
        }

        .title {
            font-size: 20px;
            font-weight: 700;
            color: #5a76c8;
        }

        .period {
            font-size: 12px;
            font-weight: 700;
            color: #8faaf3;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th {
            background: #f4f9ff;
            color: #5a76c8;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid #bde0fe;
            padding: 8px;
        }

        td {
            border: 1px solid #dceeff;
            padding: 8px;
            font-size: 12px;
            vertical-align: top;
            mso-number-format: "\@";
        }

        .money {
            color: #10b981;
            font-weight: 700;
            mso-number-format: "\@";
        }

        .total td {
            background: #e6fff7;
            color: #10b981;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="6" class="title">Wiboost Store - Laporan Transaksi</td>
        </tr>
        <tr>
            <td colspan="6" class="period">Periode: {{ $monthName }}</td>
        </tr>
        <tr>
            <td colspan="6"></td>
        </tr>
        <tr>
            <th>No</th>
            <th>Waktu Transaksi</th>
            <th>Invoice</th>
            <th>Nama Pelanggan</th>
            <th>Layanan / Produk</th>
            <th>Total Pendapatan</th>
        </tr>
        @forelse($transactions as $index => $trx)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $trx->created_at?->format('d/m/Y H:i') }}</td>
                <td>{{ $trx->invoice_number }}</td>
                <td>{{ $trx->user->name ?? 'Guest' }}</td>
                <td>{{ $trx->product->name ?? 'Produk Dihapus' }}</td>
                <td class="money">Rp {{ number_format($trx->display_total_payable, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6">Tidak ada transaksi sukses pada periode ini.</td>
            </tr>
        @endforelse
        <tr class="total">
            <td colspan="5">TOTAL PENDAPATAN</td>
            <td>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
        </tr>
    </table>
</body>
</html>
