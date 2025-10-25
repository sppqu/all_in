<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Realisasi POS Pembayaran</title>
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
        .realisasi-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .realisasi-table th,
        .realisasi-table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: center;
            font-size: 9px;
        }
        .realisasi-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .realisasi-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-row {
            background-color: #e8f5e8 !important;
            font-weight: bold;
            font-size: 10px;
        }
        .achievement-high {
            color: #28a745;
            font-weight: bold;
        }
        .achievement-medium {
            color: #ffc107;
            font-weight: bold;
        }
        .achievement-low {
            color: #dc3545;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .signature-section {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #333;
            width: 200px;
            display: inline-block;
        }
        .signature-name {
            margin-top: 5px;
            font-weight: bold;
        }
        /* Excel-specific styling */
        .excel-export {
            page-break-inside: avoid;
        }
        .excel-header {
            background-color: #f5f5f5 !important;
            color: #333 !important;
        }
        .excel-total {
            background-color: #e8f5e8 !important;
            font-weight: bold;
        }
        .excel-success {
            background-color: #d4edda !important;
        }
        .excel-warning {
            background-color: #fff3cd !important;
        }
        .excel-danger {
            background-color: #f8d7da !important;
        }
    </style>
</head>
<body>
    <div class="container excel-export">
        <div class="header">
            <h1>LAPORAN REALISASI POS PEMBAYARAN</h1>
            <p>Sistem Pembayaran SPPQU</p>
            <p>Tanggal Export: {{ date('d/m/Y H:i') }}</p>
        </div>

        <div class="info-section">
            <table class="info-table">
                <tr>
                    <td>Tahun Ajaran</td>
                    <td>{{ $period->period_start }}/{{ $period->period_end }}</td>
                </tr>
                <tr>
                    <td>Range Tanggal</td>
                    <td>{{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}</td>
                </tr>
                <tr>
                    <td>Jenis Laporan</td>
                    <td>Realisasi POS Pembayaran</td>
                </tr>
                <tr>
                    <td>Total Data</td>
                    <td>{{ count($realisasiData) }} POS pembayaran</td>
                </tr>
            </table>
        </div>

        @if(count($realisasiData) > 0)
            <h3>Data Realisasi POS Pembayaran</h3>
            <table class="realisasi-table">
                <thead>
                    <tr>
                        <th width="5%">No.</th>
                        <th width="25%">POS Pembayaran</th>
                        <th width="20%">Target</th>
                        <th width="20%">Terbayar</th>
                        <th width="20%">Belum Terbayar</th>
                        <th width="10%">Pencapaian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($realisasiData as $index => $data)
                        @if(isset($data['is_total']) && $data['is_total'])
                            <!-- Total Row -->
                            <tr class="total-row excel-total">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $data['pos_name'] }}</td>
                                <td>Rp {{ number_format($data['target'] ?? $data['tagihan'], 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($data['terbayar'], 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($data['belum_terbayar'], 0, ',', '.') }}</td>
                                <td>
                                    @if($data['pencapaian'] >= 80)
                                        <span class="achievement-high">{{ $data['pencapaian'] }}%</span>
                                    @elseif($data['pencapaian'] >= 50)
                                        <span class="achievement-medium">{{ $data['pencapaian'] }}%</span>
                                    @else
                                        <span class="achievement-low">{{ $data['pencapaian'] }}%</span>
                                    @endif
                                </td>
                            </tr>
                        @else
                            <!-- Data Row -->
                            <tr class="{{ $data['pencapaian'] >= 80 ? 'excel-success' : ($data['pencapaian'] >= 50 ? 'excel-warning' : 'excel-danger') }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $data['pos_name'] }}</td>
                                <td>Rp {{ number_format($data['target'] ?? $data['tagihan'], 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($data['terbayar'], 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($data['belum_terbayar'], 0, ',', '.') }}</td>
                                <td>
                                    @if($data['pencapaian'] >= 80)
                                        <span class="achievement-high">{{ $data['pencapaian'] }}%</span>
                                    @elseif($data['pencapaian'] >= 50)
                                        <span class="achievement-medium">{{ $data['pencapaian'] }}%</span>
                                    @else
                                        <span class="achievement-low">{{ $data['pencapaian'] }}%</span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            <div class="summary" style="margin-top: 15px; padding: 8px; background-color: #f8f9fa; border: 1px solid #dee2e6;">
                <h4 style="margin: 0 0 10px 0; font-size: 12px;">Ringkasan Pencapaian:</h4>
                <table class="info-table" style="margin: 0;">
                                    @php
                    $totalData = collect($realisasiData)->where('is_total', true)->first();
                    $nonTotalData = collect($realisasiData)->where('is_total', false);
                    $avgPencapaian = $nonTotalData->avg('pencapaian');
                @endphp
                <tr>
                    <td>Target Total</td>
                    <td>Rp {{ number_format($totalData['target'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Terbayar Total</td>
                    <td>Rp {{ number_format($totalData['terbayar'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Belum Terbayar Total</td>
                    <td>Rp {{ number_format($totalData['belum_terbayar'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Rata-rata Pencapaian</td>
                    <td>{{ number_format($avgPencapaian, 1) }}%</td>
                </tr>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 20px; color: #666;">
                <p>Tidak ada data realisasi POS pembayaran untuk kriteria yang dipilih.</p>
            </div>
        @endif

        <div class="footer">
            <p>Dokumen ini diexport secara otomatis oleh sistem pada {{ date('d/m/Y H:i:s') }}</p>
            <p>Dokumen ini sah dan dapat digunakan sebagai bukti laporan realisasi POS pembayaran</p>
        </div>

        <div class="signature-section">
            <div class="signature-line"></div>
            <div class="signature-name">Petugas</div>
        </div>
    </div>
</body>
</html>
