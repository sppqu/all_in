<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran Online - <?php echo e($payment->reference); ?></title>
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
            font-size: 12px;
            font-weight: normal;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
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
            overflow: hidden;
            position: relative;
        }
        
        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 0;
            background: transparent;
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
            flex: 0 0 auto;
            margin-left: 20px;
        }
        
        .receipt-title h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
            border: 1px dashed #333;
            padding: 8px 15px;
            text-align: center;
        }
        
        .online-badge {
            background: linear-gradient(45deg, #007bff, #28a745);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 5px;
            text-align: center;
            width: 100%;
        }
        
        .divider {
            border-bottom: 2px solid #000;
            margin-bottom: 5px;
        }
        
        .payment-info {
            margin-bottom: 5px;
        }
        
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .info-left {
            flex: 1;
        }
        
        .info-right {
            flex: 0 0 auto;
            margin-left: auto;
            margin-right: 70px;
            width: 250px;
        }
        
        .info-row {
            margin-bottom: 4px;
            font-size: 12px;
        }
        
        .info-row:last-child {
            margin-bottom: 2px;
        }
        
        .info-label {
            font-weight: normal;
            color: #333;
        }
        
        .info-value {
            color: #333;
            font-weight: normal;
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
            font-size: 12px;
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
        
        .payment-details {
            margin-bottom: 2px;
        }
        
        .payment-details h3 {
            margin: 0 0 2px 0;
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }
        
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .payment-table th,
        .payment-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        
        .payment-table th {
            background-color: #f5f5f5;
            font-weight: normal;
            text-align: left;
        }
        
        .payment-table th:first-child {
            width: 60px;
        }
        
        .payment-table th:nth-child(2) {
            width: 400px;
        }
        
        .payment-table th:last-child {
            width: 200px;
            text-align: right;
        }
        
        .payment-table td:last-child {
            text-align: right;
        }
        
        .total-section {
            margin-bottom: 10px;
        }
        
        .total-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .total-table td {
            padding: 8px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .total-table td:last-child {
            text-align: right;
        }
        
        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .signature-section {
            text-align: center;
            font-size: 12px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 100px;
            margin: 20px 0 10px 0;
        }
        
        .signature-label {
            font-size: 12px;
            color: #333;
            font-weight: bold;
        }
        
        .additional-info {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 12px;
            color: #666;
        }
        
        .additional-info ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        
        .additional-info li {
            margin-bottom: 2px;
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
        <i class="fas fa-print"></i> Cetak Receipt
    </button>
    
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo">
                    <?php if(!empty($schoolProfile->logo_sekolah) && $schoolProfile->logo_sekolah !== 'Logo'): ?>
                        <img src="<?php echo e(asset('storage/'.$schoolProfile->logo_sekolah)); ?>" alt="Logo Sekolah">
                    <?php else: ?>
                        Q
                    <?php endif; ?>
                </div>
                <div class="school-info">
                    <h1><?php echo e($schoolProfile->nama_sekolah ?? 'SMK SPPQU DIGITAL PAYMENT'); ?></h1>
                    <p><?php echo e($schoolProfile->alamat ?? 'Jl. Bledak Anggur IV, No.22, Tlogosari Kulon, Kota Semarang'); ?></p>
                    <p>Telp: <?php echo e($schoolProfile->no_telp ?? '082188497818'); ?></p>
                </div>
            </div>
            <div class="receipt-title">
                <div class="online-badge">PEMBAYARAN ONLINE</div>
                <h2>BUKTI PEMBAYARAN</h2>
            </div>
        </div>
        
        <!-- Garis Lurus -->
        <div class="divider"></div>
        
        <!-- Payment Information -->
        <div class="payment-info">
            <table class="info-table">
                <tr>
                    <td class="info-label">NIS</td>
                    <td class="info-label">:</td>
                    <td class="info-value"><?php echo e($payment->student_nis); ?></td>
                    <td class="info-label">Tgl. Transaksi</td>
                    <td class="info-label">:</td>
                    <td class="info-value"><?php echo e(\Carbon\Carbon::parse($payment->created_at)->format('d/m/Y')); ?></td>
                </tr>
                <tr>
                    <td class="info-label">Nama</td>
                    <td class="info-label">:</td>
                    <td class="info-value"><?php echo e($payment->student_full_name); ?></td>
                    <td class="info-label">No. Ref</td>
                    <td class="info-label">:</td>
                    <td class="info-value"><?php echo e($payment->reference); ?></td>
                </tr>
                <tr>
                    <td class="info-label">Kelas</td>
                    <td class="info-label">:</td>
                    <td class="info-value"><?php echo e($payment->class_name ?? 'Kelas tidak ditemukan'); ?></td>
                    <td class="info-label">Status</td>
                    <td class="info-label">:</td>
                    <td class="info-value">
                        <span style="color: <?php echo e($payment->status == 1 ? '#28a745' : ($payment->status == 0 ? '#ffc107' : '#dc3545')); ?>; font-weight: bold;">
                            <?php echo e($payment->status == 1 ? 'BERHASIL' : ($payment->status == 0 ? 'MENUNGGU' : 'DITOLAK')); ?>

                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="info-label">Status Siswa</td>
                    <td class="info-label">:</td>
                    <td class="info-value">Aktif</td>
                    <td class="info-label">Metode</td>
                    <td class="info-label">:</td>
                    <td class="info-value">Transfer Bank</td>
                </tr>
            </table>
        </div>
        
    
        <!-- Payment Details -->
        <div class="payment-details">
            <h3>Dengan rincian transaksi sebagai berikut:</h3>
            
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>PEMBAYARAN</th>
                        <th>JUMLAH PENERIMAAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $transferDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td style="text-align: center;"><?php echo e($index + 1); ?></td>
                        <td>
                            <?php if($detail->payment_type == 1 && isset($detail->month_name)): ?>
                                <?php echo e($detail->pos_name); ?> - T.A <?php echo e($detail->period_name); ?> (<?php echo e($detail->month_name); ?>)
                            <?php else: ?>
                                <?php echo e($detail->desc); ?>

                            <?php endif; ?>
                        </td>
                        <td>Rp <?php echo e(number_format($detail->subtotal, 0, ',', '.')); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        
        <!-- Total -->
        <div class="total-section">
            <table class="total-table">
                <tr>
                    <td style="width: 60px;">&nbsp;</td>
                    <td style="width: 400px;">&nbsp;</td>
                    <td style="width: 200px;">Total Pembayaran : Rp. <?php echo e(number_format($payment->confirm_pay, 0, ',', '.')); ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Tanda Tangan -->
        <div class="footer">
            <div class="signature-section">
                <div class="signature-label">Penyetor,</div>
                <div class="signature-line"></div>
            </div>
            <div class="signature-section">
                <div class="signature-label">Petugas,</div>
                <div class="signature-line"></div>
                <div class="signature-label"><?php echo e($officerName ?? 'Sistem Pembayaran Online'); ?></div>
            </div>
        </div>
        
        <!-- Additional Information -->
        <div class="additional-info">
            <p><strong>Catatan:</strong></p>
            <ul>
                <li>Receipt ini adalah bukti pembayaran yang sah</li>
                <li>Simpan receipt ini sebagai bukti pembayaran</li>
                <li>Untuk informasi lebih lanjut, hubungi admin sekolah</li>
                <li>Pembayaran diproses secara otomatis oleh sistem</li>
            </ul>
        </div>
    </div>
    
    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html> <?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/online-payment/receipt.blade.php ENDPATH**/ ?>