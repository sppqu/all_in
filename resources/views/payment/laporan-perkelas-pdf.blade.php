<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Perkelas</title>
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
        .status-lunas {
            color: #28a745;
            font-weight: bold;
            font-size: 7px;
        }
        .amount-unpaid {
            color: #dc3545;
            font-size: 7px;
        }
        .student-info {
            text-align: left;
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
        <h1>LAPORAN PERKELAS</h1>
        @if(isset($school))
            <p>{{ $school->nama_sekolah ?? 'NAMA SEKOLAH' }}</p>
        @endif
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>Kelas</td>
                <td>{{ $class->class_name ?? 'KELAS' }}</td>
            </tr>
            <tr>
                <td>Tahun Ajaran</td>
                <td>{{ $period->period_start }}/{{ $period->period_end }}</td>
            </tr>
            <tr>
                <td>Bulan</td>
                <td>{{ $months[$selectedMonth] ?? 'BULAN' }}</td>
            </tr>
            <tr>
                <td>Total Data</td>
                <td>{{ $data->count() }} siswa</td>
            </tr>
        </table>
    </div>

    @if($data->count() > 0)
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kelas</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    @foreach($posList as $pos)
                        <th>{{ $pos->pos_name }} - T.A {{ $period->period_start }}/{{ $period->period_end }}</th>
                    @endforeach
                    <th>Sub Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="student-info">{{ $item['class_name'] }}</td>
                        <td class="student-info">{{ $item['student_nis'] }}</td>
                        <td class="student-info">{{ $item['student_full_name'] }}</td>
                        @foreach($posList as $pos)
                            @php
                                $posData = $item['pos_data'][$pos->pos_id] ?? null;
                                $amount = $posData ? $posData['amount'] : 0;
                            @endphp
                            <td>
                                @if($amount > 0)
                                    <span class="amount-unpaid">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="status-lunas">LUNAS</span>
                                @endif
                            </td>
                        @endforeach
                        <td>
                            @if($item['subtotal'] > 0)
                                <span class="amount-unpaid">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                            @else
                                <span class="status-lunas">LUNAS</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr style="background-color: #e3f2fd;">
                    <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL</td>
                    @foreach($posList as $pos)
                        @php
                            $posTotal = 0;
                            foreach($data as $item) {
                                $posData = $item['pos_data'][$pos->pos_id] ?? null;
                                if ($posData && $posData['amount'] > 0) {
                                    $posTotal += $posData['amount'];
                                }
                            }
                        @endphp
                        <td style="text-align: right; font-weight: bold;">
                            @if($posTotal > 0)
                                <span class="amount-unpaid">Rp {{ number_format($posTotal, 0, ',', '.') }}</span>
                            @else
                                <span class="status-lunas">LUNAS</span>
                            @endif
                        </td>
                    @endforeach
                    <td style="text-align: right; font-weight: bold;">
                        <span class="amount-unpaid">Rp {{ number_format($data->sum('subtotal'), 0, ',', '.') }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 20px; color: #666;">
            <p>Tidak ada data kekurangan pembayaran untuk kriteria yang dipilih.</p>
        </div>
    @endif

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh sistem pada {{ date('d/m/Y H:i:s') }}</p>
        <p>Dokumen ini sah dan dapat digunakan sebagai bukti laporan pembayaran</p>
    </div>
</body>
</html> 