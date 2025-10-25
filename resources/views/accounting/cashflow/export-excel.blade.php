<?php
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Laporan_Arus_Kas_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Arus Kas</title>
    <style type="text/css">
        table {
            border-collapse: collapse;
            width: 100%;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .school-info {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .period {
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .kas-section {
            margin-bottom: 30px;
        }
        
        .kas-header {
            font-weight: bold;
            font-size: 12px;
            background-color: #e9ecef;
            padding: 8px;
            border: 1px solid #000;
            margin-bottom: 10px;
        }
        
        .summary-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .number {
            text-align: right;
        }
    </style>
</head>
<body>
    <!-- Header Sekolah -->
    <div class="school-info">
        @if(isset($schoolData))
            <div class="header">{{ strtoupper($schoolData->school_name ?? 'NAMA SEKOLAH') }}</div>
            <div>{{ $schoolData->school_address ?? 'ALAMAT SEKOLAH' }}</div>
            @if($schoolData->school_phone)
                <div>Telp: {{ $schoolData->school_phone }}</div>
            @endif
        @else
            <div class="header">NAMA SEKOLAH</div>
            <div>ALAMAT SEKOLAH</div>
        @endif
    </div>
    
    <!-- Judul Laporan -->
    <div class="header">LAPORAN ARUS KAS</div>
    <div class="period text-center">
        Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
    </div>
    
    <!-- Summary Total -->
    <table>
        <thead>
            <tr>
                <th width="40%">Keterangan</th>
                <th width="20%">Saldo Awal</th>
                <th width="20%">Kas Masuk</th>
                <th width="20%">Kas Keluar</th>
                <th width="20%">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            <tr class="summary-row">
                <td><strong>TOTAL SEMUA KAS</strong></td>
                <td class="number">{{ number_format($grandTotal['saldo_awal'], 0, ',', '.') }}</td>
                <td class="number">{{ number_format($grandTotal['kas_masuk'], 0, ',', '.') }}</td>
                <td class="number">{{ number_format($grandTotal['kas_keluar'], 0, ',', '.') }}</td>
                <td class="number">{{ number_format($grandTotal['saldo_akhir'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    
    <br><br>
    
    <!-- Detail per Kas -->
    @foreach($cashflowData as $kasData)
    <div class="kas-section">
        <div class="kas-header">
            {{ $kasData['nama_kas'] }} ({{ $kasData['jenis_kas'] == 'cash' ? 'Tunai' : 'Bank' }})
        </div>
        
        <!-- Summary Kas -->
        <table>
            <thead>
                <tr>
                    <th width="40%">Keterangan</th>
                    <th width="15%">Saldo Awal</th>
                    <th width="15%">Kas Masuk</th>
                    <th width="15%">Kas Keluar</th>
                    <th width="15%">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>{{ $kasData['nama_kas'] }}</strong></td>
                    <td class="number">{{ number_format($kasData['saldo_awal'], 0, ',', '.') }}</td>
                    <td class="number">{{ number_format($kasData['total_masuk'], 0, ',', '.') }}</td>
                    <td class="number">{{ number_format($kasData['total_keluar'], 0, ',', '.') }}</td>
                    <td class="number">{{ number_format($kasData['saldo_akhir'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        
        <br>
        
        <!-- Detail Kas Masuk -->
        @if(count($kasData['kas_masuk']) > 0)
        <table>
            <thead>
                <tr>
                    <th colspan="5" style="background-color: #d4edda;">KAS MASUK</th>
                </tr>
                <tr>
                    <th width="15%">Tanggal</th>
                    <th width="20%">Referensi</th>
                    <th width="30%">Keterangan</th>
                    <th width="15%">Metode</th>
                    <th width="20%">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kasData['kas_masuk'] as $masuk)
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($masuk->tanggal)->format('d/m/Y') }}</td>
                    <td>{{ $masuk->referensi }}</td>
                    <td>{{ $masuk->keterangan }}</td>
                    <td class="text-center">{{ $masuk->metode ?? '-' }}</td>
                    <td class="number">{{ number_format($masuk->jumlah, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="summary-row">
                    <td colspan="4" class="text-center"><strong>TOTAL KAS MASUK</strong></td>
                    <td class="number"><strong>{{ number_format($kasData['total_masuk'], 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
        <br>
        @endif
        
        <!-- Detail Kas Keluar -->
        @if(count($kasData['kas_keluar']) > 0)
        <table>
            <thead>
                <tr>
                    <th colspan="5" style="background-color: #f8d7da;">KAS KELUAR</th>
                </tr>
                <tr>
                    <th width="15%">Tanggal</th>
                    <th width="20%">Referensi</th>
                    <th width="30%">Keterangan</th>
                    <th width="15%">Metode</th>
                    <th width="20%">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kasData['kas_keluar'] as $keluar)
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($keluar->tanggal)->format('d/m/Y') }}</td>
                    <td>{{ $keluar->referensi }}</td>
                    <td>{{ $keluar->keterangan }}</td>
                    <td class="text-center">{{ $keluar->metode ?? '-' }}</td>
                    <td class="number">{{ number_format($keluar->jumlah, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="summary-row">
                    <td colspan="4" class="text-center"><strong>TOTAL KAS KELUAR</strong></td>
                    <td class="number"><strong>{{ number_format($kasData['total_keluar'], 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
        @endif
    </div>
    
    @if(!$loop->last)
        <br><br>
    @endif
    @endforeach
    
    <!-- Footer -->
    <br>
    <div style="font-size: 10px; color: #666;">
        Dicetak pada: {{ date('d F Y H:i:s') }}
    </div>
</body>
</html>

