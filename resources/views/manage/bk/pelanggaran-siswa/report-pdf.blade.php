<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Pelanggaran Siswa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header-top {
            padding-bottom: 10px;
            border-bottom: 3px solid #000;
            margin-bottom: 15px;
        }
        
        .header h1 {
            font-size: 14px;
            margin-bottom: 5px;
            color: #000;
            font-weight: bold;
        }
        
        .header h2 {
            font-size: 12px;
            margin-bottom: 3px;
            color: #000;
        }
        
        .header p {
            font-size: 10px;
            color: #000;
        }
        
        .info-box {
            background: #f5f5f5;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-left: 4px solid #000;
        }
        
        .info-box table {
            width: 100%;
        }
        
        .info-box td {
            padding: 3px 0;
            font-size: 11px;
        }
        
        .info-box td:first-child {
            width: 120px;
            font-weight: bold;
            color: #555;
        }
        
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f9f9f9;
        }
        
        .summary-card h4 {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .summary-card .value {
            font-size: 20px;
            font-weight: bold;
            color: #2e7d32;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        table.data-table thead {
            background: #2e7d32;
            color: white;
        }
        
        table.data-table th,
        table.data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        table.data-table th {
            font-weight: bold;
            text-align: center;
        }
        
        table.data-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        table.data-table tbody tr:hover {
            background-color: #f0f0f0;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: #4caf50;
            color: white;
        }
        
        .badge-warning {
            background-color: #ff9800;
            color: white;
        }
        
        .badge-orange {
            background-color: #ff5722;
            color: white;
        }
        
        .badge-danger {
            background-color: #f44336;
            color: white;
        }
        
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        
        .footer table {
            margin-left: auto;
            margin-top: 20px;
        }
        
        .footer td {
            padding: 5px 20px;
            text-align: center;
            font-size: 11px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
        }
        
        .crown {
            color: #ffc107;
            font-weight: bold;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-top">
            @if($schoolProfile && $schoolProfile->nama_sekolah)
            <h1 style="font-size: 14px; margin-bottom: 3px;">{{ strtoupper($schoolProfile->nama_sekolah) }}</h1>
            @if($schoolProfile->alamat)
            <p style="font-size: 9px; margin-bottom: 0;">{{ $schoolProfile->alamat }}</p>
            @endif
            @endif
        </div>
        <h1 style="font-size: 14px;">LAPORAN REKAP PELANGGARAN SISWA</h1>
        <h2>BIMBINGAN KONSELING</h2>
    </div>

    <!-- Info Box -->
    <div class="info-box">
        <table>
            <tr>
                <td>Kelas</td>
                <td>: {{ $kelasName }}</td>
            </tr>
            <tr>
                <td>Tanggal Cetak</td>
                <td>: {{ $tanggal }}</td>
            </tr>
            <tr>
                <td>Total Siswa</td>
                <td>: {{ $students->count() }} siswa</td>
            </tr>
        </table>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h4>Total Siswa</h4>
            <div class="value">{{ $students->count() }}</div>
        </div>
        <div class="summary-card">
            <h4>Siswa Bermasalah</h4>
            <div class="value">{{ $students->where('total_point', '>', 0)->count() }}</div>
        </div>
        <div class="summary-card">
            <h4>Total Pelanggaran</h4>
            <div class="value">{{ $students->sum('jumlah_pelanggaran') }}</div>
        </div>
        <div class="summary-card">
            <h4>Total Point</h4>
            <div class="value">{{ number_format($students->sum('total_point')) }}</div>
        </div>
    </div>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">NIS</th>
                <th width="25%">Nama Siswa</th>
                <th width="15%">Kelas</th>
                <th width="13%">Jumlah<br>Pelanggaran</th>
                <th width="12%">Total<br>Point</th>
                <th width="18%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
            <tr>
                <td class="text-center">
                    @if($index == 0 && $student->total_point > 0)
                        <span class="crown">👑</span>
                    @else
                        {{ $index + 1 }}
                    @endif
                </td>
                <td>{{ $student->student_nis }}</td>
                <td><strong>{{ $student->student_full_name }}</strong></td>
                <td>
                    @if($student->class)
                        {{ $student->class->class_name }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">
                    @if($student->jumlah_pelanggaran > 0)
                        {{ $student->jumlah_pelanggaran }}x
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">
                    <strong>{{ $student->total_point }}</strong>
                </td>
                <td class="text-center">
                    @if($student->total_point == 0)
                        <span class="badge badge-success">Baik</span>
                    @elseif($student->total_point < 50)
                        <span class="badge badge-warning">Perlu Perhatian</span>
                    @elseif($student->total_point < 100)
                        <span class="badge badge-orange">Bermasalah</span>
                    @else
                        <span class="badge badge-danger">Sangat Bermasalah</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer / Signature -->
    <div class="footer">
        <table>
            <tr>
                <td>
                    Mengetahui,<br>
                    Kepala Sekolah
                    <div class="signature-line">
                        (....................................)
                    </div>
                </td>
                <td>
                    {{ now()->format('d F Y') }}<br>
                    Guru BK
                    <div class="signature-line">
                        (....................................)
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

