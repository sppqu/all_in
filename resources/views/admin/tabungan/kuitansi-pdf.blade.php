<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Tabungan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 20px;
        }
        
        .kuitansi-info {
            flex: 1;
            padding: 15px;
            border: 1px solid #000;
            border-radius: 5px;
        }
        
        .kuitansi-info h3 {
            margin: 0 0 15px 0;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        
        .info-label {
            font-weight: bold;
            width: 120px;
            font-size: 11px;
        }
        
        .info-value {
            flex: 1;
            font-size: 11px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        
        .table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-success {
            color: #28a745;
        }
        
        .text-danger {
            color: #dc3545;
        }
        
        .summary {
            margin-top: 15px;
            padding: 12px;
            border: 1px solid #000;
            border-radius: 5px;
        }
        
        .summary h4 {
            margin: 0 0 12px 0;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-weight: bold;
            font-size: 10px;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>KUITANSI TABUNGAN SISWA</h1>
        <p>{{ $school->nama_sekolah ?? 'NAMA SEKOLAH' }}</p>
        <p>{{ $school->alamat ?? 'ALAMAT SEKOLAH' }}</p>
        <p>Telp: {{ $school->no_telp ?? 'NOMOR TELEPON' }}</p>
    </div>

    <!-- Informasi Kuitansi dan Siswa (Digabung) -->
    <div class="info-section">
        <div class="kuitansi-info">
            <h3>INFORMASI KUITANSI & SISWA</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <!-- Kolom Kiri - Informasi Kuitansi -->
                    <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                        <div class="info-row">
                            <div class="info-label">Nomor Kuitansi:</div>
                            <div class="info-value">KTS-{{ $tabungan->student_nis }}-{{ date('Ymd') }}-{{ str_pad($transaksi->count(), 3, '0', STR_PAD_LEFT) }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Tanggal Cetak:</div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($tanggalCetak)->format('d/m/Y') }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Jenis Kuitansi:</div>
                            <div class="info-value">
                                @switch($jenisKuitansi)
                                    @case('setoran')
                                        Kuitansi Setoran
                                        @break
                                    @case('penarikan')
                                        Kuitansi Penarikan
                                        @break
                                    @default
                                        Semua Transaksi
                                @endswitch
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Periode:</div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($periodeAwal)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($periodeAkhir)->format('d/m/Y') }}</div>
                        </div>
                    </td>
                    
                    <!-- Kolom Kanan - Informasi Siswa -->
                    <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                        <div class="info-row">
                            <div class="info-label">Nama Siswa:</div>
                            <div class="info-value">{{ $tabungan->student_full_name }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">NIS:</div>
                            <div class="info-value">{{ $tabungan->student_nis }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Kelas:</div>
                            <div class="info-value">{{ $tabungan->class_name }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Saldo Saat Ini:</div>
                            <div class="info-value"><strong>Rp {{ number_format($tabungan->saldo, 0, ',', '.') }}</strong></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Tabel Transaksi -->
    @if($transaksi->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 15%;">Jenis</th>
                <th style="width: 20%;">Jumlah</th>
                <th style="width: 20%;">Saldo Setelah Transaksi</th>
                <th style="width: 25%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($item->log_tabungan_input_date)->format('d/m/Y H:i') }}</td>
                <td class="text-center">
                    @if($item->kredit > 0)
                        <span class="text-success">Setoran</span>
                    @elseif($item->debit > 0)
                        <span class="text-danger">Penarikan</span>
                    @else
                        <span>Lainnya</span>
                    @endif
                </td>
                <td class="text-right">
                    @if($item->kredit > 0)
                        <span class="text-success">+ Rp {{ number_format($item->kredit, 0, ',', '.') }}</span>
                    @elseif($item->debit > 0)
                        <span class="text-danger">- Rp {{ number_format($item->debit, 0, ',', '.') }}</span>
                    @else
                        Rp 0
                    @endif
                </td>
                <td class="text-right">
                    <strong>Rp {{ number_format($item->saldo, 0, ',', '.') }}</strong>
                </td>
                <td>{{ $item->keterangan ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 20px; border: 1px solid #000;">
        <p>Tidak ada transaksi untuk periode yang dipilih</p>
    </div>
    @endif

    <!-- Ringkasan -->
    <div class="summary">
        <h4>RINGKASAN TRANSAKSI</h4>
        <div class="summary-row">
            <div>Total Setoran:</div>
            <div class="text-success">Rp {{ number_format($totalSetoran, 0, ',', '.') }}</div>
        </div>
        <div class="summary-row">
            <div>Total Penarikan:</div>
            <div class="text-danger">Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</div>
        </div>
        <div class="summary-row">
            <div>Saldo Akhir Periode:</div>
            <div><strong>Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</strong></div>
        </div>
        <div class="summary-row">
            <div>Jumlah Transaksi:</div>
            <div>{{ $transaksi->count() }} transaksi</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Dokumen ini dicetak pada {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Kuitansi ini sah dan dapat digunakan sebagai bukti transaksi tabungan</p>
    </div>
</body>
</html>
