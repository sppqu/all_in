<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pengeluaran - {{ $transaction->no_transaksi }}</title>
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
        
        .receipt-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 15mm;
            background: white;
            position: relative;
        }
        
        .header {
            text-align: left;
            margin-bottom: 20px;
            position: relative;
        }
        
        .school-info {
            font-size: 11px;
            line-height: 1.3;
        }
        
        .school-name {
            font-weight: bold;
            font-size: 12px;
        }
        
        .receipt-title {
            position: absolute;
            top: 0;
            right: 0;
            border: 2px solid #000;
            padding: 8px 15px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        
        .transaction-info {
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 3px;
            align-items: flex-start;
        }
        
        .info-label {
            width: 120px;
            font-weight: normal;
        }
        
        .info-separator {
            width: 20px;
            text-align: center;
        }
        
        .info-value {
            flex: 1;
        }
        
        .right-info {
            margin-left: auto;
            margin-right: 70px;
            width: 250px;
        }
        
        .right-info .info-label {
            width: 90px;
        }
        
        .right-info .info-separator {
            width: 15px;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #000;
        }
        
        .details-table th,
        .details-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        .details-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        
        .details-table .text-center {
            text-align: center;
        }
        
        .details-table .text-right {
            text-align: right;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 200px;
            text-align: center;
        }
        
        .signature-label {
            margin-bottom: 50px;
            font-weight: bold;
        }
        
        .signature-name {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
        }
        
        .divider {
            border-top: 1px solid #000;
            margin: 15px 0;
        }
        
        @media print {
            body {
                margin: 0;
            }
            
            .receipt-container {
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
    
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="school-info">
                <div class="school-name">{{ $schoolProfile->nama_sekolah ?? 'NAMA LEMBAGA ANDA' }}</div>
                <div>{{ $schoolProfile->alamat ?? 'Alamat Lembaga Anda' }}</div>
                <div>Telp: {{ $schoolProfile->no_telp ?? 'No. Telepon' }}</div>
                @if($schoolProfile && !empty($schoolProfile->email))
                <div>Email: {{ $schoolProfile->email }}</div>
                @endif
                @if($schoolProfile && !empty($schoolProfile->website))
                <div>Website: {{ $schoolProfile->website }}</div>
                @endif
            </div>
            
            <div class="receipt-title">
                BUKTI PENGELUARAN
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Transaction Information -->
        <div class="transaction-info">
            <div class="info-row">
                <div class="info-label">Cara Transaksi</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $paymentMethod->nama_metode ?? 'TUNAI' }}</div>
                <div class="right-info">
                    <div class="info-row">
                        <div class="info-label">Tgl Transaksi</div>
                        <div class="info-separator">:</div>
                        <div class="info-value">{{ date('d-m-Y', strtotime($transaction->tanggal_pengeluaran)) }}</div>
                    </div>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Keterangan</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $transaction->keterangan_transaksi ?? 'Dana Sosial' }}</div>
                <div class="right-info">
                    <div class="info-row">
                        <div class="info-label">Nomor Bukti</div>
                        <div class="info-separator">:</div>
                        <div class="info-value">{{ $transaction->no_transaksi }}</div>
                    </div>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Terbilang</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ ucwords(\App\Helpers\NumberHelper::terbilang($transaction->total_pengeluaran)) }} Rupiah</div>
                <div class="right-info">
                    <div class="info-row">
                        <div class="info-label">Petugas</div>
                        <div class="info-separator">:</div>
                        <div class="info-value">{{ $transaction->operator ?? 'ADMIN' }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Details Table -->
        <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">
            Dengan rincian transaksi sebagai berikut:
        </div>
        
        <table class="details-table">
            <thead>
                <tr>
                    <th style="width: 40px;">NO</th>
                    <th style="width: 120px;">POS SUMBER DANA</th>
                    <th style="width: 120px;">POS PENGELUARAN</th>
                    <th style="width: 200px;">KETERANGAN</th>
                    <th style="width: 100px;">JUMLAH</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->pos_sumber_dana_name ?? '' }}</td>
                    <td>{{ $detail->pos_pengeluaran_name ?? '' }}</td>
                    <td>{{ $detail->keterangan_item }}</td>
                    <td class="text-right">{{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                
                <!-- Total Row -->
                <tr class="total-row">
                    <td colspan="4" class="text-center"><strong>TOTAL</strong></td>
                    <td class="text-right"><strong>{{ number_format($transaction->total_pengeluaran, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-label">Yang Mengeluarkan,</div>
                <div class="signature-name">( {{ $transaction->operator ?? 'Muhammad Akbar Wiguna' }} )</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-label">Penerima,</div>
                <div class="signature-name">( {{ $transaction->dibayar_ke ?? 'PT. ABC Jaya' }} )</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-label">Menyetujui,</div>
                <div class="signature-name">( ........................... )</div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto focus for print
        window.onload = function() {
            // Optional: Auto print when page loads
            // window.print();
        };
        
        // Print function
        function printReceipt() {
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
