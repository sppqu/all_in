<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Perpos Bebas</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .header p {
            margin: 3px 0;
            font-size: 11px;
        }
        .info-section {
            margin-bottom: 15px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table td {
            padding: 4px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 25%;
            background-color: #f5f5f5;
        }
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            table-layout: fixed;
        }
        .transactions-table th,
        .transactions-table td {
            border: 1px solid #ddd;
            padding: 6px 4px;
            text-align: left;
            word-wrap: break-word;
        }
        .transactions-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
        }
        .transactions-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        /* Optimasi kolom untuk landscape */
        .transactions-table th:nth-child(1), .transactions-table td:nth-child(1) { width: 4%; }  /* No */
        .transactions-table th:nth-child(2), .transactions-table td:nth-child(2) { width: 10%; text-align: center; } /* NIS - Center */
        .transactions-table th:nth-child(3), .transactions-table td:nth-child(3) { width: 25%; } /* Nama */
        .transactions-table th:nth-child(4), .transactions-table td:nth-child(4) { width: 8%; text-align: center; }  /* Kelas - Center */
        .transactions-table th:nth-child(5), .transactions-table td:nth-child(5) { width: 15%; } /* Tagihan */
        .transactions-table th:nth-child(6), .transactions-table td:nth-child(6) { width: 15%; } /* Bayar */
        .transactions-table th:nth-child(7), .transactions-table td:nth-child(7) { width: 15%; } /* Sisa */
        .transactions-table th:nth-child(8), .transactions-table td:nth-child(8) { width: 8%; }  /* Status */
        .status-lunas {
            color: #28a745;
            font-weight: bold;
        }
        .status-belum {
            color: #dc3545;
            font-weight: bold;
        }
        .summary {
            margin-top: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .summary h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PERPOS BEBAS</h1>
        @if(isset($school))
            <p>{{ $school->nama_sekolah ?? 'NAMA SEKOLAH' }}</p>
        @endif
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>Pos Pembayaran</td>
                <td>{{ $pos->pos_name }}</td>
            </tr>
            <tr>
                <td>Tahun Ajaran</td>
                <td>{{ $period->period_start }}/{{ $period->period_end }}</td>
            </tr>
            <tr>
                <td>Jenis Laporan</td>
                <td>Bebas</td>
            </tr>
            <tr>
                <td>Total Data</td>
                <td>{{ $data->count() }} siswa</td>
            </tr>
        </table>
    </div>

    @if($data->count() > 0)
        <h3>Data Pembayaran Bebas</h3>
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Jumlah Tagihan</th>
                    <th>Total Bayar</th>
                    <th>Sisa</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                    @php
                        $sisa = $item->bebas_bill - $item->bebas_total_pay;
                        $status = $sisa <= 0 ? 'LUNAS' : 'BELUM LUNAS';
                        $statusClass = $sisa <= 0 ? 'status-lunas' : 'status-belum';
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $item->student_nis }}</td>
                        <td>{{ $item->student_full_name }}</td>
                        <td>{{ $item->class_name }}</td>
                        <td style="text-align: right;">Rp {{ number_format($item->bebas_bill, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($item->bebas_total_pay, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                        <td style="text-align: center;">
                            <span class="{{ $statusClass }}">{{ $status }}</span>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <h4>Ringkasan:</h4>
            <table class="info-table">
                <tr>
                    <td>Total Siswa</td>
                    <td>{{ $data->count() }} siswa</td>
                </tr>
                <tr>
                    <td>Total Belum Lunas</td>
                    <td>Rp {{ number_format($data->sum('bebas_bill') - $data->sum('bebas_total_pay'), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Tagihan</td>
                    <td>Rp {{ number_format($data->sum('bebas_bill'), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Penerimaan</td>
                    <td>Rp {{ number_format($data->sum('bebas_total_pay'), 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 20px; color: #666;">
            <p>Tidak ada data pembayaran bebas untuk kriteria yang dipilih.</p>
        </div>
    @endif

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh sistem pada {{ date('d/m/Y H:i:s') }}</p>
        <p>Dokumen ini sah dan dapat digunakan sebagai bukti laporan pembayaran</p>
    </div>
</body>
</html> 