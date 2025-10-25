<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Arus Kas - {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
        }
        
        .report-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 15mm;
            background: white;
            position: relative;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .school-info {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .school-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .school-address {
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .report-title {
            font-weight: bold;
            font-size: 14px;
            margin: 15px 0 5px 0;
        }
        
        .report-period {
            font-size: 12px;
            margin-bottom: 20px;
        }
        
        .summary-section {
            margin-bottom: 20px;
            border: 1px solid #000;
            padding: 10px;
        }
        
        .summary-title {
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-table td {
            padding: 5px 10px;
            border: 1px solid #333;
        }
        
        .summary-table .label {
            font-weight: bold;
            background-color: #f0f0f0;
            width: 40%;
        }
        
        .summary-table .value {
            text-align: right;
            width: 60%;
        }
        
        .kas-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .kas-header {
            background-color: #f5f5f5;
            border: 1px solid #000;
            padding: 8px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .kas-summary {
            margin-bottom: 15px;
        }
        
        .kas-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .kas-summary-table td {
            padding: 3px 8px;
            border: 1px solid #ddd;
            font-size: 11px;
        }
        
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .transaction-table th,
        .transaction-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
            font-size: 10px;
        }
        
        .transaction-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        
        .transaction-table .text-center {
            text-align: center;
        }
        
        .transaction-table .text-right {
            text-align: right;
        }
        
        .transaction-table .no-col {
            width: 5%;
        }
        
        .transaction-table .date-col {
            width: 12%;
        }
        
        .transaction-table .ref-col {
            width: 15%;
        }
        
        .transaction-table .desc-col {
            width: 30%;
        }
        
        .transaction-table .type-col {
            width: 12%;
        }
        
        .transaction-table .method-col {
            width: 12%;
        }
        
        .transaction-table .amount-col {
            width: 14%;
        }
        
        .section-title {
            font-weight: bold;
            margin: 15px 0 5px 0;
            font-size: 12px;
        }
        
        .kas-masuk {
            color: #28a745;
        }
        
        .kas-keluar {
            color: #dc3545;
        }
        
        .footer {
            position: fixed;
            bottom: 10mm;
            right: 15mm;
            font-size: 10px;
            color: #666;
        }
        
        @media print {
            body {
                margin: 0;
            }
            
            .report-container {
                margin: 0;
                padding: 10mm;
                box-shadow: none;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
                 .print-button:hover {
             background: #0056b3;
         }
         
         
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fa fa-print"></i> Cetak
    </button>
    
    <div class="report-container">
        <!-- Header -->
        <div class="header">
            <div class="school-info">
                <div class="school-name">{{ $schoolProfile->nama_sekolah ?? 'NAMA SEKOLAH' }}</div>
                <div class="school-address">{{ $schoolProfile->alamat ?? 'Alamat Sekolah' }}</div>
                <div class="school-address">Telp: {{ $schoolProfile->no_telp ?? 'No. Telepon' }}</div>
            </div>
            
            <div class="report-title">LAPORAN ARUS KAS</div>
            <div class="report-period">
                Periode: {{ date('d F Y', strtotime($startDate)) }} s/d {{ date('d F Y', strtotime($endDate)) }}
            </div>
        </div>
        
        <!-- Summary -->
        @php
            $totalSaldoAwal = array_sum(array_column($cashflowData, 'saldo_awal'));
            $totalKasMasuk = array_sum(array_column($cashflowData, 'total_masuk'));
            $totalKasKeluar = array_sum(array_column($cashflowData, 'total_keluar'));
            $totalSaldoAkhir = array_sum(array_column($cashflowData, 'saldo_akhir'));
        @endphp
        
        <div class="summary-section">
            <div class="summary-title">RINGKASAN ARUS KAS</div>
            <table class="summary-table">
                <tr>
                    <td class="label">Total Saldo Awal</td>
                    <td class="value">Rp {{ number_format($totalSaldoAwal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Total Kas Masuk</td>
                    <td class="value">Rp {{ number_format($totalKasMasuk, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Total Kas Keluar</td>
                    <td class="value">Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}</td>
                </tr>
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td class="label">Total Saldo Akhir</td>
                    <td class="value">Rp {{ number_format($totalSaldoAkhir, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
        
        <!-- Detail per Kas -->
        @foreach($cashflowData as $kasData)
        <div class="kas-section">
            <div class="kas-header">
                {{ $kasData['nama_kas'] }} ({{ $kasData['jenis_kas'] == 'cash' ? 'Tunai' : 'Bank' }})
            </div>
            
            <!-- Summary per Kas -->
            <div class="kas-summary">
                <table class="kas-summary-table">
                    <tr>
                        <td style="font-weight: bold; background-color: #f9f9f9;">Saldo Awal:</td>
                        <td style="text-align: right;">Rp {{ number_format($kasData['saldo_awal'], 0, ',', '.') }}</td>
                        <td style="font-weight: bold; background-color: #f9f9f9;">Total Masuk:</td>
                        <td style="text-align: right; color: #28a745;">Rp {{ number_format($kasData['total_masuk'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; background-color: #f9f9f9;">Total Keluar:</td>
                        <td style="text-align: right; color: #dc3545;">Rp {{ number_format($kasData['total_keluar'], 0, ',', '.') }}</td>
                        <td style="font-weight: bold; background-color: #f9f9f9;">Saldo Akhir:</td>
                        <td style="text-align: right; font-weight: bold;">Rp {{ number_format($kasData['saldo_akhir'], 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Kas Masuk -->
            @if(count($kasData['kas_masuk']) > 0)
            <div class="section-title kas-masuk">KAS MASUK ({{ count($kasData['kas_masuk']) }} transaksi)</div>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th class="no-col">No</th>
                        <th class="date-col">Tanggal</th>
                        <th class="ref-col">Referensi</th>
                        <th class="desc-col">Keterangan</th>
                        <th class="type-col">Jenis</th>
                        <th class="method-col">Metode</th>
                        <th class="amount-col">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kasData['kas_masuk'] as $index => $masuk)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ date('d/m/Y', strtotime($masuk['tanggal'])) }}</td>
                        <td>{{ $masuk['referensi'] }}</td>
                        <td>{{ $masuk['keterangan'] }}</td>
                        <td>{{ $masuk['jenis'] }}</td>
                        <td>{{ $masuk['metode'] }}</td>
                        <td class="text-right">{{ number_format($masuk['jumlah'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr style="background-color: #d4edda; font-weight: bold;">
                        <td colspan="6" class="text-center">TOTAL KAS MASUK</td>
                        <td class="text-right">{{ number_format($kasData['total_masuk'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
            @endif
            
            <!-- Kas Keluar -->
            @if(count($kasData['kas_keluar']) > 0)
            <div class="section-title kas-keluar">KAS KELUAR ({{ count($kasData['kas_keluar']) }} transaksi)</div>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th class="no-col">No</th>
                        <th class="date-col">Tanggal</th>
                        <th class="ref-col">Referensi</th>
                        <th class="desc-col">Keterangan</th>
                        <th class="type-col">Jenis</th>
                        <th class="method-col">Metode</th>
                        <th class="amount-col">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kasData['kas_keluar'] as $index => $keluar)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ date('d/m/Y', strtotime($keluar['tanggal'])) }}</td>
                        <td>{{ $keluar['referensi'] }}</td>
                        <td>{{ $keluar['keterangan'] }}</td>
                        <td>{{ $keluar['jenis'] }}</td>
                        <td>{{ $keluar['metode'] }}</td>
                        <td class="text-right">{{ number_format($keluar['jumlah'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr style="background-color: #f8d7da; font-weight: bold;">
                        <td colspan="6" class="text-center">TOTAL KAS KELUAR</td>
                        <td class="text-right">{{ number_format($kasData['total_keluar'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
            @endif
            
            @if(count($kasData['kas_masuk']) == 0 && count($kasData['kas_keluar']) == 0)
            <p style="text-align: center; color: #666; font-style: italic; margin: 20px 0;">
                Tidak ada transaksi dalam periode ini
            </p>
            @endif
        </div>
        @endforeach
    </div>
    
                        
     <div class="footer">
         Dicetak pada: {{ date('d F Y H:i:s') }}
     </div>
    
    <script>
        // Auto focus for print
        window.onload = function() {
            // Optional: Auto print when page loads
            // window.print();
        };
        
        // Print function
        function printReport() {
            window.print();
        }
        
        // Keyboard shortcut Ctrl+P
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
