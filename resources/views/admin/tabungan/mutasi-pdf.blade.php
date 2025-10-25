<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mutasi Tabungan</title>
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
        .header-with-logo {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .logo-section {
            flex: 0 0 auto;
            margin-right: 20px;
        }
        .school-info {
            flex: 1;
            text-align: left;
        }
        .school-info h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: bold;
        }
        .school-info p {
            margin: 2px 0;
            font-size: 12px;
        }
        .header-kop {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .logo-kop {
            flex: 0 0 auto;
            margin-right: 20px;
        }
        .school-info-kop {
            flex: 1;
            text-align: center;
        }
        .school-info-kop h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: bold;
        }
        .school-info-kop p {
            margin: 2px 0;
            font-size: 12px;
        }
        .title-kop {
            flex: 0 0 auto;
            text-align: center;
            border: 2px dashed #333;
            padding: 10px;
            margin-left: 20px;
        }
        .title-kop h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .report-title {
            text-align: left;
            margin: 10px 0;
        }
        .report-title h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .report-title p {
            margin: 2px 0;
            font-size: 11px;
        }
        .date-info {
            text-align: left;
            margin: 5px 0;
            font-size: 11px;
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
        .info-section {
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 30%;
            background-color: #f5f5f5;
        }
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .transactions-table th,
        .transactions-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .transactions-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        .transactions-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .credit {
            color: #28a745;
            font-weight: bold;
        }
        .debit {
            color: #dc3545;
            font-weight: bold;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>MUTASI TABUNGAN</h1>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>Nama Sekolah</td>
                <td>{{ $school->nama_sekolah ?? 'NAMA SEKOLAH' }}</td>
            </tr>
            <tr>
                <td>NIS</td>
                <td>{{ $tabungan->student_nis }}</td>
            </tr>
            <tr>
                <td>Nama Siswa</td>
                <td>{{ $tabungan->student_full_name }}</td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>{{ $tabungan->class_name }}</td>
            </tr>
            <tr>
                <td>Saldo</td>
                <td><strong>Rp {{ number_format($tabungan->saldo, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    @if($riwayat->count() > 0)
        <h3>Riwayat Transaksi</h3>
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Saldo Setelah Transaksi</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($riwayat as $index => $transaksi)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($transaksi->log_tabungan_input_date)->format('d/m/Y H:i') }}</td>
                        <td style="text-align: center;">
                            @if($transaksi->kredit > 0)
                                <span class="credit">SETORAN</span>
                            @elseif($transaksi->debit > 0)
                                <span class="debit">PENARIKAN</span>
                            @else
                                LAINNYA
                            @endif
                        </td>
                        <td style="text-align: right;">
                            @if($transaksi->kredit > 0)
                                <span class="credit">+ Rp {{ number_format($transaksi->kredit, 0, ',', '.') }}</span>
                            @elseif($transaksi->debit > 0)
                                <span class="debit">- Rp {{ number_format($transaksi->debit, 0, ',', '.') }}</span>
                            @else
                                Rp 0
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <strong>Rp {{ number_format($transaksi->saldo, 0, ',', '.') }}</strong>
                        </td>
                        <td>{{ $transaksi->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <h4>Ringkasan Transaksi:</h4>
            <table class="info-table">
                <tr>
                    <td>Total Setoran</td>
                    <td>Rp {{ number_format($riwayat->sum('kredit'), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Penarikan</td>
                    <td>Rp {{ number_format($riwayat->sum('debit'), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Jumlah Transaksi</td>
                    <td>{{ $riwayat->count() }} transaksi</td>
                </tr>
                <tr>
                    <td>Periode</td>
                    <td>{{ \Carbon\Carbon::parse($riwayat->last()->log_tabungan_input_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($riwayat->first()->log_tabungan_input_date)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>Tanggal Cetak</td>
                    <td>{{ date('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 20px; color: #666;">
            <p>Belum ada transaksi tabungan untuk siswa ini.</p>
        </div>
    @endif

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh sistem pada {{ date('d/m/Y H:i:s') }}</p>
        <p>Dokumen ini sah dan dapat digunakan sebagai bukti mutasi tabungan</p>
    </div>
</body>
</html> 