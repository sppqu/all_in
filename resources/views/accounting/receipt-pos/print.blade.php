<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Penerimaan - {{ $transaction->no_transaksi }}</title>
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
            width: 250px;
        }
        
        .divider {
            border-bottom: 2px solid #000;
            margin: 15px 0;
        }
        
        .details-section {
            margin-bottom: 15px;
        }
        
        .details-title {
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .details-table th,
        .details-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 11px;
        }
        
        .details-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        
        .details-table td {
            vertical-align: top;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .signature-box {
            text-align: center;
            flex: 1;
            margin: 0 20px;
        }
        
        .signature-label {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .signature-name {
            margin-top: 20px;
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
        
        @media print {
            .print-button {
                display: none;
            }
            
            body {
                background: white;
            }
            
            .receipt-container {
                box-shadow: none;
                border-radius: 0;
            }
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
                BUKTI PENERIMAAN
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Transaction Information -->
        <div class="transaction-info">
            <div class="info-row">
                <div class="info-label">Diterima dari:</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $transaction->diterima_dari ?? '-' }}</div>
                <div class="right-info">
                    <div class="info-row">
                        <div class="info-label">Tgl. Transaksi:</div>
                        <div class="info-separator">:</div>
                        <div class="info-value">{{ date('d/m/Y', strtotime($transaction->tanggal_penerimaan)) ?? '-' }}</div>
                    </div>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Cara Transaksi:</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $paymentMethod->nama_metode ?? '-' }}</div>
                <div class="right-info">
                    <div class="info-row">
                        <div class="info-label">Nomor Bukti:</div>
                        <div class="info-separator">:</div>
                        <div class="info-value">{{ $transaction->no_transaksi ?? '-' }}</div>
                    </div>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Terbilang:</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $terbilang ?? '-' }}</div>
                <div class="right-info">
                    <div class="info-row">
                        <div class="info-label">Petugas:</div>
                        <div class="info-separator">:</div>
                        <div class="info-value">{{ $transaction->operator ?? '-' }}</div>
                    </div>
                </div>
            </div>
            
            @if($transaction->keterangan_transaksi)
            <div class="info-row">
                <div class="info-label">Keterangan:</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $transaction->keterangan_transaksi }}</div>
            </div>
            @endif
        </div>
        
        <!-- Details Section -->
        <div class="details-section">
            <div class="details-title">
                Dengan rincian transaksi sebagai berikut:
            </div>
            
            <table class="details-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">NO</th>
                        <th style="width: 120px;">POS PENERIMAAN</th>
                        <th style="width: 200px;">KETERANGAN ITEM</th>
                        <th style="width: 100px;">JUMLAH PENERIMAAN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $index => $detail)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $detail->pos_penerimaan_name ?? '' }}</td>
                        <td>{{ $detail->keterangan_item }}</td>
                        <td class="text-right">{{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    
                    <!-- Total Row -->
                    <tr class="total-row">
                        <td colspan="3" class="text-center"><strong>TOTAL</strong></td>
                        <td class="text-right"><strong>{{ number_format($transaction->total_penerimaan, 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-label">Penyetor,</div>
                <div class="signature-name">( {{ $transaction->diterima_dari ?? '...........................' }} )</div>
            </div>
            
            <div class="signature-box">
                <div class="signature-label">Penerima,</div>
                <div class="signature-name">( {{ $transaction->operator ?? '...........................' }} )</div>
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
