<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Perpos Bulanan</title>
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
        }
        .transactions-table th,
        .transactions-table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: center;
            font-size: 9px;
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
            font-size: 8px;
        }
        .status-belum {
            color: #dc3545;
            font-weight: bold;
            font-size: 8px;
        }
        .student-info {
            text-align: left;
            font-size: 9px;
        }
        .summary {
            margin-top: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
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
        <h1>LAPORAN PERPOS BULANAN</h1>
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
                <td>Bulanan</td>
            </tr>
            <tr>
                <td>Total Data</td>
                <td>{{ $data->count() }} siswa</td>
            </tr>
        </table>
    </div>

    @if($data->count() > 0)
        <h3>Laporan Pembayaran Bulanan</h3>
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Juli</th>
                    <th>Agustus</th>
                    <th>September</th>
                    <th>Oktober</th>
                    <th>November</th>
                    <th>Desember</th>
                    <th>Januari</th>
                    <th>Februari</th>
                    <th>Maret</th>
                    <th>April</th>
                    <th>Mei</th>
                    <th>Juni</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $groupedData = $data->groupBy('student_nis');
                @endphp
                @foreach($groupedData as $studentNis => $studentData)
                    @php
                        $firstRecord = $studentData->first();
                        $monthlyData = $studentData->keyBy('month_month_id');
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="student-info">{{ $firstRecord->student_nis }}</td>
                        <td class="student-info">{{ $firstRecord->student_full_name }}</td>
                        <td class="student-info">{{ $firstRecord->class_name }}</td>
                        @for($month = 1; $month <= 12; $month++)
                            @php
                                $monthData = $monthlyData->get($month);
                            @endphp
                            <td>
                                @if($monthData)
                                    @if($monthData->bulan_date_pay)
                                        <span class="status-lunas">LUNAS</span>
                                    @else
                                        <span class="status-belum">Rp {{ number_format($monthData->bulan_bill, 0, ',', '.') }}</span>
                                    @endif
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <h4>Ringkasan:</h4>
            <table class="info-table">
                <tr>
                    <td>Total Siswa</td>
                    <td>{{ $data->unique('student_nis')->count() }} siswa</td>
                </tr>
                <tr>
                    <td>Total Belum Lunas</td>
                    <td>Rp {{ number_format($data->where('bulan_date_pay', null)->sum('bulan_bill'), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Penerimaan</td>
                    <td>Rp {{ number_format($data->where('bulan_date_pay', '!=', null)->sum('bulan_bill'), 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    @else
        <div style="text-align: center; padding: 20px; color: #666;">
            <p>Tidak ada data pembayaran bulanan untuk kriteria yang dipilih.</p>
        </div>
    @endif

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh sistem pada {{ date('d/m/Y H:i:s') }}</p>
        <p>Dokumen ini sah dan dapat digunakan sebagai bukti laporan pembayaran</p>
    </div>
</body>
</html> 