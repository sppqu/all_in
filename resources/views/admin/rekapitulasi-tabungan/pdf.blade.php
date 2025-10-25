<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Tabungan</title>
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
        
        .filter-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        
        .filter-info h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .filter-info p {
            margin: 5px 0;
            font-size: 11px;
        }
        
        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .summary-card {
            flex: 1;
            min-width: 150px;
            margin: 0 2px;
            padding: 8px;
            border: 1px solid #000;
            text-align: center;
        }
        
        .summary-card h5 {
            margin: 0 0 5px 0;
            font-size: 11px;
            font-weight: bold;
        }
        
        .summary-card .amount {
            font-size: 14px;
            font-weight: bold;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
        }
        
        .table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
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
        
        .text-warning {
            color: #ffc107;
        }
        
        .text-info {
            color: #17a2b8;
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
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>REKAPITULASI TABUNGAN SISWA</h1>
        <p>{{ $school->nama_sekolah ?? 'NAMA SEKOLAH' }}</p>
        <p>{{ $school->alamat ?? 'ALAMAT SEKOLAH' }}</p>
        <p>Telp: {{ $school->no_telp ?? 'NOMOR TELEPON' }}</p>
    </div>

    <!-- Filter Information -->
    <div class="filter-info">
        <h4>Informasi Filter:</h4>
        <p><strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        @if($paymentMethod)
            <p><strong>Metode Pembayaran:</strong> 
                @switch($paymentMethod)
                    @case('tunai')
                        Tunai
                        @break
                    @case('transfer_bank')
                        Transfer Bank
                        @break
                    @case('payment_gateway')
                        Payment Gateway
                        @break
                    @default
                        {{ $paymentMethod }}
                @endswitch
            </p>
        @endif
        <p><strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>

            <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <h5>Setoran Tunai</h5>
                <div class="amount text-success">Rp {{ number_format($rekapitulasiData->sum('setoran_tunai'), 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <h5>Transfer Bank</h5>
                <div class="amount text-primary">Rp {{ number_format($rekapitulasiData->sum('setoran_transfer_bank'), 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <h5>Payment Gateway</h5>
                <div class="amount text-info">Rp {{ number_format($rekapitulasiData->sum('setoran_payment_gateway'), 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <h5>Total Setoran</h5>
                <div class="amount text-warning">Rp {{ number_format($rekapitulasiData->sum('total_setoran'), 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <h5>Total Penarikan</h5>
                <div class="amount text-danger">Rp {{ number_format($rekapitulasiData->sum('jumlah_penarikan'), 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <h5>Total Saldo</h5>
                <div class="amount text-dark">Rp {{ number_format($rekapitulasiData->sum('saldo_akhir'), 0, ',', '.') }}</div>
            </div>
        </div>

            <!-- Data Table -->
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 10%;">NIS</th>
                    <th style="width: 18%;">Nama Siswa</th>
                    <th style="width: 12%;">Kelas</th>

                    <th style="width: 11%;">Setoran Tunai</th>
                    <th style="width: 11%;">Transfer Bank</th>
                    <th style="width: 11%;">Payment Gateway</th>
                    <th style="width: 11%;">Total Setoran</th>
                    <th style="width: 11%;">Penarikan</th>
                    <th style="width: 11%;">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekapitulasiData as $index => $data)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $data['student_nis'] }}</td>
                    <td>{{ $data['student_name'] }}</td>
                    <td>{{ $data['class_name'] }}</td>

                    <td class="text-right text-success">Rp {{ number_format($data['setoran_tunai'], 0, ',', '.') }}</td>
                    <td class="text-right text-primary">Rp {{ number_format($data['setoran_transfer_bank'], 0, ',', '.') }}</td>
                    <td class="text-right text-info">Rp {{ number_format($data['setoran_payment_gateway'], 0, ',', '.') }}</td>
                    <td class="text-right text-warning">Rp {{ number_format($data['total_setoran'], 0, ',', '.') }}</td>
                    <td class="text-right text-danger">Rp {{ number_format($data['jumlah_penarikan'], 0, ',', '.') }}</td>
                    <td class="text-right text-dark">Rp {{ number_format($data['saldo_akhir'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data tabungan untuk periode yang dipilih</td>
                </tr>
                @endforelse
                @if($rekapitulasiData->count() > 0)
                <!-- Total Row -->
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td colspan="4" class="text-center">TOTAL</td>
                    <td class="text-right text-success">Rp {{ number_format($rekapitulasiData->sum('setoran_tunai'), 0, ',', '.') }}</td>
                    <td class="text-right text-primary">Rp {{ number_format($rekapitulasiData->sum('setoran_transfer_bank'), 0, ',', '.') }}</td>
                    <td class="text-right text-info">Rp {{ number_format($rekapitulasiData->sum('setoran_payment_gateway'), 0, ',', '.') }}</td>
                    <td class="text-right text-warning">Rp {{ number_format($rekapitulasiData->sum('total_setoran'), 0, ',', '.') }}</td>
                    <td class="text-right text-danger">Rp {{ number_format($rekapitulasiData->sum('jumlah_penarikan'), 0, ',', '.') }}</td>
                    <td class="text-right text-dark">Rp {{ number_format($rekapitulasiData->sum('saldo_akhir'), 0, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
        </table>

    <!-- Detail Transaksi untuk setiap siswa -->
    @foreach($rekapitulasiData as $index => $data)
        @if($index > 0 && $index % 5 == 0)
            <div class="page-break"></div>
        @endif
        
        <div style="margin-bottom: 20px;">
            <h4 style="margin: 0 0 10px 0; font-size: 14px; border-bottom: 1px solid #000; padding-bottom: 5px;">
                Detail Transaksi: {{ $data['student_name'] }} ({{ $data['student_nis'] }}) - {{ $data['class_name'] }}
            </h4>
            
            <table class="table" style="font-size: 9px;">
                <thead>
                    <tr>
                        <th style="width: 15%;">Tanggal</th>
                        <th style="width: 35%;">Keterangan</th>
                        <th style="width: 15%;">Setoran</th>
                        <th style="width: 15%;">Penarikan</th>
                        <th style="width: 20%;">Metode</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['detail_transaksi'] as $trans)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($trans['tanggal'])->format('d/m/Y') }}</td>
                        <td>{{ $trans['keterangan'] }}</td>
                        <td class="text-right text-success">
                            {{ $trans['kredit'] > 0 ? 'Rp ' . number_format($trans['kredit'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right text-warning">
                            {{ $trans['debit'] > 0 ? 'Rp ' . number_format($trans['debit'], 0, ',', '.') : '-' }}
                        </td>
                        <td>{{ $trans['metode_pembayaran'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada transaksi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach

    <!-- Footer -->
    <div class="footer">
        <p>Dokumen ini dicetak pada {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Total {{ $rekapitulasiData->count() }} siswa dengan total saldo Rp {{ number_format($rekapitulasiData->sum('saldo_akhir'), 0, ',', '.') }}</p>
    </div>
</body>
</html>
