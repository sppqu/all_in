<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Pembayaran - {{ $payment->getTypeName() }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #ffffff;
            color: #000;
            line-height: 1.4;
            font-size: 14px;
        }

        .receipt-container {
            max-width: 210mm;
            width: 210mm;
            height: auto;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #000;
            padding: 0;
            position: relative;
        }

        .receipt-header {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid #000;
        }

        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .receipt-subtitle {
            font-size: 12px;
            margin-bottom: 3px;
        }

        .receipt-body {
            padding: 20px;
        }

        .receipt-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px dotted #000;
            padding-bottom: 5px;
        }

        .receipt-line:last-child {
            border-bottom: none;
        }

        .receipt-label {
            font-weight: bold;
            font-size: 14px;
        }

        .receipt-value {
            text-align: right;
            font-size: 14px;
        }

        .receipt-amount {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border: 2px solid #000;
        }

        .receipt-footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #000;
            font-size: 12px;
        }

        .receipt-signature {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 40px;
            margin-bottom: 5px;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #000;
            color: white;
            border: none;
            padding: 10px 20px;
            font-weight: bold;
            cursor: pointer;
        }

        .print-button:hover {
            background: #333;
        }

        @media print {
            .print-button {
                display: none;
            }
            
            body {
                background: white;
                margin: 0;
                padding: 0;
            }
            
            .receipt-container {
                border: 1px solid #000;
                box-shadow: none;
                max-width: 210mm;
                width: 210mm;
                height: auto;
                margin: 0;
                transform: none;
                position: absolute;
                top: 0;
                left: 0;
            }
            
            @page {
                size: A4;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">CETAK</button>

    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <div class="receipt-title">{{ $schoolProfile->nama_sekolah ?? 'SPPQU' }}</div>
            <div class="receipt-subtitle">{{ $schoolProfile->alamat ?? 'Alamat Sekolah' }}</div>
            <div class="receipt-subtitle">Telp: {{ $schoolProfile->telepon ?? '-' }}</div>
            <div class="receipt-subtitle" style="margin-top: 10px; font-size: 14px; font-weight: bold;">KUITANSI PEMBAYARAN</div>
        </div>

        <!-- Body -->
        <div class="receipt-body">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div style="flex: 1; margin-right: 20px;">
                    <div class="receipt-line">
                        <span class="receipt-label">No. Kuitansi:</span>
                        <span class="receipt-value">#{{ $payment->id }}</span>
                    </div>
                    
                    <div class="receipt-line">
                        <span class="receipt-label">Tanggal:</span>
                        <span class="receipt-value">{{ $payment->created_at->format('d/m/Y') }}</span>
                    </div>
                    
                    <div class="receipt-line">
                        <span class="receipt-label">Nama:</span>
                        <span class="receipt-value">{{ $payment->registration->name }}</span>
                    </div>
                    
                    <div class="receipt-line">
                        <span class="receipt-label">No. HP:</span>
                        <span class="receipt-value">{{ $payment->registration->phone }}</span>
                    </div>
                </div>
                
                <div style="flex: 1; margin-left: 20px;">
                    <div class="receipt-line">
                        <span class="receipt-label">Jenis:</span>
                        <span class="receipt-value">{{ $payment->getTypeName() }}</span>
                    </div>
                    
                    <div class="receipt-line">
                        <span class="receipt-label">Metode:</span>
                        <span class="receipt-value">{{ $payment->getPaymentMethodName() }}</span>
                    </div>
                    
                    @if($payment->payment_reference)
                    <div class="receipt-line">
                        <span class="receipt-label">Ref:</span>
                        <span class="receipt-value">{{ $payment->payment_reference }}</span>
                    </div>
                    @endif
                    
                    <div class="receipt-line">
                        <span class="receipt-label">Status:</span>
                        <span class="receipt-value">
                            @if($payment->status == 'paid')
                                LUNAS
                            @elseif($payment->status == 'pending')
                                PENDING
                            @elseif($payment->status == 'expired')
                                KADALUARSA
                            @else
                                GAGAL
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="receipt-amount">
                {{ $payment->getAmountFormattedAttribute() }}
            </div>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <div class="receipt-signature">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div>Petugas</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div>Penerima</div>
                </div>
            </div>
            <div style="margin-top: 15px;">
                Dicetak: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</body>
</html>
