<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran - SMK SPPQU</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            background: transparent;
            border-radius: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            font-size: 20px;
            font-weight: bold;
            overflow: visible;
            position: relative;
        }
        
        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 0;
            background: transparent;
            position: relative;
        }
        
        .school-info h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .school-info p {
            margin: 2px 0;
            font-size: 12px;
            color: #666;
        }
        
        .receipt-title {
            border: 1px dashed #333;
            padding: 8px 15px;
            text-align: center;
        }
        
        .receipt-title h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
        .student-info {
            margin-bottom: 5px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        
        .info-table td {
            padding: 0px 3px;
            border: none;
            vertical-align: top;
            font-size: 13px;
            text-align: left;
            white-space: nowrap;
        }
        
        .info-table .info-label {
            font-weight: normal;
            color: #333;
            width: 2px;
            padding-right: 2px;
        }
        
        .info-table .info-value {
            color: #333;
            padding-left: 2px !important;
            font-weight: normal;
            width: 130px;
            text-align: left !important;
            margin-left: 0;
            padding-right: 0;
        }
        
        .info-table td:nth-child(2),
        .info-table td:nth-child(6) {
            width: 8px;
            padding: 1px 1px;
            text-align: center;
            font-weight: normal;
        }
        
        .info-table td:nth-child(4) {
            text-align: left !important;
            padding-left: 90px !important;
            margin-left: 0;
            padding-right: 0;
        }
        
        .info-table td:nth-child(5),
        .info-table td:nth-child(6) {
            padding-left: 2px !important;
        }
        
        /* Mengatur jarak kolom tanda ":" agar lebih rapi */
        .info-table td:nth-child(2),
        .info-table td:nth-child(2) {
            width: 10px;
            padding: 1px 2px;
            text-align: center;
            font-weight: normal;
        }
        
        .divider {
            border-top: none;
            margin: 5px 0;
        }
        
        .payment-details h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #333;
            font-weight: normal;
        }
        
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        
        .payment-table th,
        .payment-table td {
            border: 1px solid #ddd;
            padding: 5px 10px;
            text-align: left;
            font-size: 14px;
        }
        
        .payment-table th:first-child,
        .payment-table td:first-child {
            width: 30px;
            text-align: center;
        }
        
        .payment-table th:last-child,
        .payment-table td:last-child {
            text-align: right;
        }
        
        .payment-table th {
            background-color: #f8f9fa;
            font-weight: normal;
            color: #333;
            text-align: left;
        }
        
        .total-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
            text-align: right;
        }
        
        .total-row {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .total-label {
            font-weight: bold;
            color: #333;
            font-size: 14px;
            font-style: italic;
            text-align: right;
        }
        
        .total-value {
            font-weight: bold;
            font-size: 14px;
            color: #333;
            font-style: italic;
        }
        
        .footer {
            display: flex;
            justify-content: flex-start;
            gap: 200px;
            margin-top: 40px;
            text-align: left;
        }
        
        .signature-section {
            text-align: left;
            font-size: 14px;
        }
        
        .signature-line {
            border-top: 1px dashed #333;
            width: 70px;
            margin: 30px 0 10px 0;
        }
        
        .signature-label {
            font-size: 14px;
            color: #666;
        }
        
        .signature-name {
            font-weight: normal;
            color: #333;
            margin-top: 5px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt-container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .no-print {
                display: none;
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
        }
        
        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Cetak Kuitansi
    </button>
    
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo">
                    @if(!empty($school_profile->logo_sekolah) && $school_profile->logo_sekolah !== 'Logo')
                        <img src="{{ asset('storage/'.$school_profile->logo_sekolah) }}" alt="Logo Sekolah">
                    @else
                        Q
                    @endif
                </div>
                <div class="school-info">
                    <h1>{{ $school_profile->nama_sekolah ?? 'SMK SPPQU DIGITAL PAYMENT' }}</h1>
                    <p>{{ $school_profile->alamat ?? 'Jl. Bledak Anggur IV, No.22, Tlogosari Kulon, Kota Semarang' }}</p>
                    <p>{{ $school_profile->no_telp ?? '082188497818' }}</p>
                </div>
            </div>
            <div class="receipt-title">
                <h2>BUKTI PEMBAYARAN</h2>
            </div>
        </div>
        
        <!-- Student Information -->
        <div class="payment-details">
            <table class="info-table">
                <tr>
                    <td class="info-label">NIS</td>
                    <td class="info-label">:</td>
                    <td class="info-value">{{ $student->nis ?? '-' }}</td>
                    <td class="info-label">Tanggal Pembayaran</td>
                    <td class="info-label">:</td>
                    <td class="info-value">{{ $payment_date ?? '11 Juli 2025' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Nama</td>
                    <td class="info-label">:</td>
                    <td class="info-value">{{ $student->nama ?? '-' }}</td>
                    <td class="info-label">No. Ref</td>
                    <td class="info-label">:</td>
                    <td class="info-value">{{ $payment_number ?? '20250700141' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Kelas</td>
                    <td class="info-label">:</td>
                    <td class="info-value">{{ $student->kelas ?? '-' }}</td>
                    <td class="info-label">Metode Pembayaran</td>
                    <td class="info-label">:</td>
                    <td class="info-value">{{ $payment_method ?? 'Tunai' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Status Siswa</td>
                    <td class="info-label">:</td>
                    <td class="info-value">{{ $student->status ?? '-' }}</td>
                    <td class="info-label">Petugas</td>
                    <td class="info-label">:</td>
                    <td class="info-value">{{ $officer }}</td>
                </tr>
            </table>
        </div>
        
        <div class="divider"></div>
        
        <!-- Payment Details -->
        <div class="payment-details">
            <h3>Dengan rincian pembayaran sebagai berikut:</h3>
            @if(isset($payment_details) && count($payment_details) > 0)
                <table class="payment-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Pembayaran</th>
                            <th>Jumlah Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment_details as $index => $detail)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $detail['description'] ?? 'Pembayaran' }}</td>
                            <td>Rp. {{ number_format($detail['amount'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center" style="padding: 20px; color: #666; font-style: italic;">
                    <p>Tidak ada transaksi pada tanggal yang dipilih</p>
                    <p>({{ $payment_date ?? 'Tanggal tidak ditentukan' }})</p>
                </div>
            @endif
        </div>
        
        <!-- Total -->
        @if(isset($payment_details) && count($payment_details) > 0)
            <div class="total-section">
                <div class="total-row">
                    <span class="total-label">Total Pembayaran:</span>
                    <span class="total-value">Rp. {{ number_format($total_amount ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        @endif
        
        <div class="divider"></div>
        
        <!-- Footer/Signature -->
        <div class="footer">
            <div class="signature-section">
                <div>{{ $current_date ?? '22 Juli 2025' }}</div>
                <div class="signature-label">Penyetor,</div><br>
                <div class="signature-line"></div>
            </div>
            <div class="signature-section">
                <div class="signature-label"></div><br>
                <div class="signature-label">Penerima,</div><br><br>
                <div class="signature-name">{{ $officer }}</div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html> 