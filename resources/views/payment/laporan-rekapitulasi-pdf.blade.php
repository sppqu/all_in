<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .header p {
            margin: 3px 0;
            font-size: 10px;
        }
        .info-section {
            margin-bottom: 15px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 3px;
            border: 1px solid #ddd;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 25%;
            background-color: #f5f5f5;
        }
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8px;
        }
        .transactions-table th,
        .transactions-table td {
            border: 1px solid #ddd;
            padding: 3px;
            text-align: center;
        }
        .transactions-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .transactions-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .student-info {
            text-align: left;
            font-size: 8px;
        }
        .amount-info {
            text-align: right;
            font-size: 8px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .page-break {
            page-break-before: always;
        }
        @page {
            size: landscape;
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN REKAPITULASI</h1>
        @if(isset($school))
            <p>{{ $school->nama_sekolah ?? 'NAMA SEKOLAH' }}</p>
        @endif
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>Periode</td>
                <td>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</td>
            </tr>
            @if($paymentType)
            <tr>
                <td>Jenis Pembayaran</td>
                <td>{{ $paymentType }}</td>
            </tr>
            @endif
            @if($posId)
            <tr>
                <td>Pos Pembayaran</td>
                <td>
                    @php
                        $selectedPos = ($posList ?? collect())->where('pos_id', $posId)->first();
                    @endphp
                    {{ $selectedPos ? $selectedPos->pos_name : 'N/A' }}
                </td>
            </tr>
            @endif
            @if($classId)
            <tr>
                <td>Kelas</td>
                <td>
                    @php
                        $selectedClass = ($classList ?? collect())->where('class_id', $classId)->first();
                    @endphp
                    {{ $selectedClass ? $selectedClass->class_name : 'N/A' }}
                </td>
            </tr>
            @endif
            </tr>
            <tr>
                <td>Total Data</td>
                <td>{{ ($data ?? collect())->count() }} transaksi</td>
            </tr>
            <tr>
                <td>Total Penerimaan</td>
                <td>
                    @if(!$paymentType || $paymentType == 'Tunai')
                        • Tunai: Rp {{ number_format(($data ?? collect())->sum('cash_amount'), 0, ',', '.') }}<br>
                    @endif
                    @if(!$paymentType || $paymentType == 'Transfer Bank')
                        • Transfer Bank: Rp {{ number_format(($data ?? collect())->sum('transfer_amount'), 0, ',', '.') }}<br>
                    @endif
                    @if(!$paymentType || $paymentType == 'Payment Gateway')
                        • Payment Gateway: Rp {{ number_format(($data ?? collect())->sum('gateway_amount'), 0, ',', '.') }}<br>
                    @endif
                    <strong>Grand Total: Rp {{ number_format(($data ?? collect())->sum('cash_amount') + ($data ?? collect())->sum('transfer_amount') + ($data ?? collect())->sum('gateway_amount'), 0, ',', '.') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    @if(($data ?? collect())->count() > 0)
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Pos Pembayaran</th>
                    <th>Tanggal Bayar</th>
                    @if(!$paymentType || $paymentType == 'Tunai')
                        <th>Penerimaan Tunai</th>
                    @endif
                    @if(!$paymentType || $paymentType == 'Transfer Bank')
                        <th>Penerimaan Transfer Bank</th>
                    @endif
                    @if(!$paymentType || $paymentType == 'Payment Gateway')
                        <th>Penerimaan Payment Gateway</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($data ?? [] as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="student-info">{{ $item['student_name'] }}</td>
                        <td class="student-info">{{ $item['class_name'] }}</td>
                        <td class="student-info">{{ $item['pos_name'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($item['payment_date'])->format('d/m/Y') }}</td>
                        @if(!$paymentType || $paymentType == 'Tunai')
                            <td class="amount-info">
                                @if($item['cash_amount'] > 0)
                                    Rp {{ number_format($item['cash_amount'], 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        @endif
                        @if(!$paymentType || $paymentType == 'Transfer Bank')
                            <td class="amount-info">
                                @if($item['transfer_amount'] > 0)
                                    Rp {{ number_format($item['transfer_amount'], 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        @endif
                        @if(!$paymentType || $paymentType == 'Payment Gateway')
                            <td class="amount-info">
                                @if($item['gateway_amount'] > 0)
                                    Rp {{ number_format($item['gateway_amount'], 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
                <tr style="background-color: #e3f2fd;">
                    @php
                        $colspan = 5; // No, Nama Siswa, Kelas, Pos Pembayaran, Tanggal Bayar
                        $totalColumns = 0;
                        if (!$paymentType || $paymentType == 'Tunai') {
                            $colspan++;
                            $totalColumns++;
                        }
                        if (!$paymentType || $paymentType == 'Transfer Bank') {
                            $colspan++;
                            $totalColumns++;
                        }
                        if (!$paymentType || $paymentType == 'Payment Gateway') {
                            $colspan++;
                            $totalColumns++;
                        }
                    @endphp
                    <td colspan="{{ $colspan - $totalColumns }}" style="text-align: right; font-weight: bold;">TOTAL</td>
                    @if(!$paymentType || $paymentType == 'Tunai')
                        <td class="amount-info" style="font-weight: bold;">
                            Rp {{ number_format(($data ?? collect())->sum('cash_amount'), 0, ',', '.') }}
                        </td>
                    @endif
                    @if(!$paymentType || $paymentType == 'Transfer Bank')
                        <td class="amount-info" style="font-weight: bold;">
                            Rp {{ number_format(($data ?? collect())->sum('transfer_amount'), 0, ',', '.') }}
                        </td>
                    @endif
                    @if(!$paymentType || $paymentType == 'Payment Gateway')
                        <td class="amount-info" style="font-weight: bold;">
                            Rp {{ number_format(($data ?? collect())->sum('gateway_amount'), 0, ',', '.') }}
                        </td>
                    @endif
                </tr>
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 20px; color: #666;">
            <p>Tidak ada data pembayaran untuk kriteria yang dipilih.</p>
        </div>
    @endif

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh sistem pada {{ date('d/m/Y H:i:s') }}</p>
        <p>Dokumen ini sah dan dapat digunakan sebagai bukti laporan pembayaran</p>
    </div>
</body>
</html> 