<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Arus KAS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .summary .label {
            font-weight: bold;
            background-color: #f5f5f5;
            width: 30%;
        }
        .section-header {
            background-color: #333;
            color: white;
            padding: 8px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0 10px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        .data-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .amount {
            font-family: 'Courier New', monospace;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ARUS KAS</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Total Pemasukan:</td>
                <td class="amount">Rp {{ number_format($totalPemasukan) }}</td>
            </tr>
            <tr>
                <td class="label">Total Pengeluaran:</td>
                <td class="amount">Rp {{ number_format($totalPengeluaran) }}</td>
            </tr>
            <tr>
                <td class="label">Saldo KAS:</td>
                <td class="amount {{ $saldoKas >= 0 ? '' : 'color: red;' }}">
                    Rp {{ number_format(abs($saldoKas)) }}
                    @if($saldoKas < 0)
                        (Defisit)
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="section-header">DETAIL PEMASUKAN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="15%">Tanggal</th>
                <th width="40%">Keterangan</th>
                <th width="20%">Nominal</th>
                <th width="10%">Pajak</th>
                <th width="10%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detailPemasukan as $index => $pemasukan)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($pemasukan->tanggal_penerimaan)->format('d/m/Y') }}</td>
                <td>{{ $pemasukan->keterangan_transaksi ?? '-' }}</td>
                <td class="text-right amount">Rp {{ number_format($pemasukan->total_penerimaan) }}</td>
                <td class="text-right amount">0%</td>
                <td class="text-right amount">Rp {{ number_format($pemasukan->total_penerimaan) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data pemasukan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-header">DETAIL PENGELUARAN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="15%">Tanggal</th>
                <th width="40%">Keterangan</th>
                <th width="20%">Nominal</th>
                <th width="10%">Unit POS</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detailPengeluaran as $index => $pengeluaran)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($pengeluaran->tanggal_pengeluaran)->format('d/m/Y') }}</td>
                <td>{{ $pengeluaran->keterangan_transaksi ?? '-' }}</td>
                <td class="text-right amount">Rp {{ number_format($pengeluaran->total_pengeluaran) }}</td>
                <td class="text-center">{{ $pengeluaran->pos_pengeluaran_id ?? '-' }}</td>
                <td class="text-center">{{ $pengeluaran->status ?? 'Confirmed' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data pengeluaran</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem SPPQU</p>
        <p>© {{ date('Y') }} SPPQU - Sistem Pembayaran SPP Sekolah</p>
    </div>
</body>
</html>
