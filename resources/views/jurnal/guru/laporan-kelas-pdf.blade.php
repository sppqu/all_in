<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Jurnal 7KAIH - {{ $kelas->class_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
        }
        
        .container {
            padding: 15px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 8px;
        }
        
        .header h1 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .header h2 {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .header p {
            font-size: 8pt;
            margin: 1px 0;
        }
        
        /* Class Info */
        .class-info {
            margin: 10px 0;
            font-size: 9pt;
        }
        
        .class-info table {
            width: 100%;
        }
        
        .class-info td {
            padding: 2px 0;
        }
        
        .class-info .label {
            width: 100px;
            font-weight: bold;
        }
        
        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        
        .data-table thead th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 8pt;
        }
        
        .data-table tbody td {
            font-size: 8pt;
        }
        
        .data-table tfoot th {
            background: #e0e0e0;
            font-weight: bold;
            font-size: 8pt;
        }
        
        .data-table .student-name {
            text-align: left;
            padding-left: 6px;
        }
        
        .data-table .category-header {
            font-size: 7pt;
            line-height: 1.1;
        }
        
        /* Badge */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            color: white;
            font-size: 7pt;
            font-weight: bold;
        }
        
        .badge-status {
            font-size: 6pt;
            display: block;
            margin: 1px 0;
        }
        
        /* Footer */
        .footer {
            margin-top: 15px;
            font-size: 8pt;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            @if($schoolProfile && $schoolProfile->nama_sekolah)
            <h1>{{ strtoupper($schoolProfile->nama_sekolah) }}</h1>
            @if($schoolProfile->alamat)
            <p>{{ $schoolProfile->alamat }}</p>
            @endif
            @endif
            <div style="border-top: 2px solid #000; margin: 8px 0;"></div>
            <h2>LAPORAN JURNAL 7 KEBIASAAN ANAK INDONESIA HEBAT</h2>
            
        </div>

        <!-- Class Info -->
        <div class="class-info">
            <table>
                <tr>
                    <td class="label">Kelas</td>
                    <td>: {{ $kelas->class_name }}</td>
                    <td class="label" style="width: 120px;">Tanggal Cetak</td>
                    <td style="width: 150px;">: {{ date('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Total Siswa</td>
                    <td>: {{ count($laporan) }} siswa</td>
                    <td class="label">Total Jurnal</td>
                    <td>: {{ array_sum(array_column($laporan, 'total_jurnal')) }} jurnal</td>
                </tr>
            </table>
        </div>

        <!-- Data Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 3%;">No</th>
                    <th rowspan="2" style="width: 20%;">Nama Siswa</th>
                    <th colspan="7">7 Kebiasaan Anak Indonesia Hebat</th>
                    <th rowspan="2" style="width: 7%;">Total<br>Jurnal</th>
                </tr>
                <tr>
                    @foreach($kategori as $kat)
                    <th class="category-header" style="width: 8%; background: {{ $kat->warna }}20;">
                        {{ $kat->nama_kategori }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($laporan as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="student-name">
                        <strong>{{ $item['siswa']->student_full_name }}</strong><br>
                        <small style="color: #666;">NIS: {{ $item['siswa']->student_nis }}</small>
                    </td>
                    @foreach($kategori as $kat)
                    <td style="background: {{ $kat->warna }}10;">
                        @if(isset($item['count_per_kategori'][$kat->kategori_id]) && $item['count_per_kategori'][$kat->kategori_id] > 0)
                            <span class="badge" style="background: {{ $kat->warna }};">
                                {{ $item['count_per_kategori'][$kat->kategori_id] }}x
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    @endforeach
                    <td><strong>{{ $item['total_jurnal'] }}</strong></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" style="text-align: right; padding-right: 10px;">TOTAL:</th>
                    @php
                        $totalPerKategori = [];
                        foreach ($kategori as $kat) {
                            $totalPerKategori[$kat->kategori_id] = 0;
                        }
                        foreach ($laporan as $item) {
                            foreach ($item['count_per_kategori'] as $katId => $count) {
                                if (isset($totalPerKategori[$katId])) {
                                    $totalPerKategori[$katId] += $count;
                                }
                            }
                        }
                    @endphp
                    @foreach($kategori as $kat)
                    <th style="background: {{ $kat->warna }}20;">
                        {{ $totalPerKategori[$kat->kategori_id] ?? 0 }}
                    </th>
                    @endforeach
                    <th>{{ array_sum(array_column($laporan, 'total_jurnal')) }}</th>
                </tr>
            </tfoot>
        </table>

        <!-- Signature Section -->
        <div style="margin-top: 30px; text-align: right; padding-right: 50px;">
            <p style="margin-bottom: 60px;">Semarang, {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY') }}</p>
            <p style="font-weight: bold; margin: 0;">Wali Kelas</p>
            <div style="margin-top: 60px; border-top: 1px solid #000; width: 200px; display: inline-block; padding-top: 5px;">
                <p style="margin: 0;">(___________________________)</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin-top: 10px; font-style: italic;">
                Dicetak pada {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY [pukul] HH:mm') }}
            </p>
        </div>
    </div>
</body>
</html>

