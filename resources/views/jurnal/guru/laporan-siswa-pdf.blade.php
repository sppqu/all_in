<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Jurnal 7 Kebiasaan - {{ $siswa->student_full_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
        }
        
        .container {
            padding: 20px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .header p {
            font-size: 9pt;
            margin: 2px 0;
        }
        
        /* Student Info */
        .student-info {
            margin: 15px 0;
            font-size: 10pt;
        }
        
        .student-info table {
            width: 100%;
        }
        
        .student-info td {
            padding: 3px 0;
        }
        
        .student-info .label {
            width: 80px;
            font-weight: bold;
        }
        
        .student-info .colon {
            width: 15px;
        }
        
        /* Category Section */
        .category-section {
            margin-top: 20px;
            page-break-inside: avoid;
        }
        
        .category-header {
            background: #f0f0f0;
            padding: 8px;
            font-weight: bold;
            font-size: 11pt;
            border: 1px solid #000;
            margin-bottom: 5px;
        }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table.data-table {
            border: 1px solid #000;
        }
        
        table.data-table th {
            background: #e0e0e0;
            border: 1px solid #000;
            padding: 6px 4px;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
        }
        
        table.data-table td {
            border: 1px solid #000;
            padding: 5px 4px;
            font-size: 9pt;
            vertical-align: top;
        }
        
        table.data-table td.no {
            width: 30px;
            text-align: center;
        }
        
        table.data-table td.tanggal {
            width: 80px;
            text-align: center;
        }
        
        table.data-table td.waktu {
            width: 60px;
            text-align: center;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .signature-section {
            margin-top: 20px;
        }
        
        .signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
            vertical-align: top;
        }
        
        .signature-box.right {
            margin-left: 8%;
        }
        
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        
        /* Page Break */
        .page-break {
            page-break-after: always;
        }
        
        /* Checklist */
        .checklist {
            display: inline-block;
            margin-right: 8px;
        }
        
        .checked {
            font-weight: bold;
        }
        
        /* Notes */
        .notes {
            font-style: italic;
            color: #555;
            font-size: 8pt;
            margin-top: 3px;
        }
        
        .summary-box {
            border: 1px solid #000;
            padding: 10px;
            margin: 15px 0;
            background: #f9f9f9;
        }
        
        .summary-box h3 {
            font-size: 11pt;
            margin-bottom: 8px;
            text-align: center;
        }
        
        .summary-item {
            padding: 3px 0;
            border-bottom: 1px dashed #ccc;
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1 style="font-size: 14pt; margin-bottom: 3px; font-weight: bold;">
                {{ $schoolProfile && $schoolProfile->nama_sekolah ? strtoupper($schoolProfile->nama_sekolah) : 'NAMA SEKOLAH' }}
            </h1>
            @if($schoolProfile && $schoolProfile->alamat)
            <p style="font-size: 9pt; margin-bottom: 8px;">{{ $schoolProfile->alamat }}</p>
            @endif
            <div style="border-top: 2px solid #000; margin: 10px 0 8px 0;"></div>
            <h1 style="font-size: 12pt; margin-top: 5px; font-weight: bold;">JURNAL 7 KEBIASAAN ANAK INDONESIA HEBAT</h1>
            <h2 style="font-size: 10pt; font-weight: bold;">MURID DAN ORANG TUA</h2>
        </div>

        <!-- Student Info -->
        <div class="student-info">
            <table>
                <tr>
                    <td class="label">Nama</td>
                    <td class="colon">:</td>
                    <td><strong>{{ $siswa->student_full_name }}</strong></td>
                </tr>
                <tr>
                    <td class="label">NIS</td>
                    <td class="colon">:</td>
                    <td>{{ $siswa->student_nis }}</td>
                </tr>
                <tr>
                    <td class="label">Kelas</td>
                    <td class="colon">:</td>
                    <td>{{ $siswa->class->class_name ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <!-- Summary -->
        <div class="summary-box">
            <h3>RINGKASAN JURNAL</h3>
            <div class="summary-item">
                <strong>Periode:</strong> 
                {{ request('tanggal_dari') ? \Carbon\Carbon::parse(request('tanggal_dari'))->format('d M Y') : 'Awal' }} 
                s/d 
                {{ request('tanggal_sampai') ? \Carbon\Carbon::parse(request('tanggal_sampai'))->format('d M Y') : 'Sekarang' }}
            </div>
            <div class="summary-item">
                <strong>Total Jurnal:</strong> {{ $laporan->count() }} hari
            </div>
            <div class="summary-item">
                <strong>Terverifikasi:</strong> {{ $laporan->where('status', 'verified')->count() }} jurnal
            </div>
            <div class="summary-item">
                <strong>Pending:</strong> {{ $laporan->where('status', 'submitted')->count() }} jurnal
            </div>
        </div>

        <!-- Categories -->
        @foreach($dataPerKategori as $data)
            @if(count($data['entries']) > 0)
            <div class="category-section">
                <div class="category-header">
                    {{ $loop->iteration }}. {{ $data['kategori']->nama_kategori }}
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="no">No</th>
                            <th class="tanggal">Hari/Tanggal</th>
                            <th class="waktu">Waktu</th>
                            <th>Keterangan</th>
                            <th style="width: 80px;">Paraf Guru</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['entries'] as $index => $item)
                        <tr>
                            <td class="no">{{ $index + 1 }}</td>
                            <td class="tanggal">
                                {{ \Carbon\Carbon::parse($item['tanggal'])->isoFormat('dddd') }}<br>
                                {{ \Carbon\Carbon::parse($item['tanggal'])->format('d/m/Y') }}
                            </td>
                            <td class="waktu">
                                @if($item['entry']->jam)
                                    {{ $item['entry']->jam }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                {{-- Keterangan berdasarkan kategori --}}
                                @php
                                    $checklistData = $item['entry']->checklist_data ? json_decode($item['entry']->checklist_data, true) : [];
                                    $kategoriKode = $data['kategori']->kode;
                                @endphp

                                @if($kategoriKode == 'BANGUN' || $kategoriKode == 'TIDUR')
                                    {{-- Jam dan Keterangan --}}
                                    @if($item['entry']->keterangan)
                                        {{ $item['entry']->keterangan }}
                                    @endif

                                @elseif($kategoriKode == 'IBADAH')
                                    {{-- Hanya tampilkan sholat yang ter-check --}}
                                    @php
                                        $sholatList = [];
                                        if(isset($checklistData['subuh'])) $sholatList[] = 'Subuh';
                                        if(isset($checklistData['dzuhur'])) $sholatList[] = 'Dzuhur';
                                        if(isset($checklistData['asar'])) $sholatList[] = 'Asar';
                                        if(isset($checklistData['magrib'])) $sholatList[] = 'Magrib';
                                        if(isset($checklistData['isya'])) $sholatList[] = 'Isya';
                                    @endphp
                                    {{ count($sholatList) > 0 ? implode(', ', $sholatList) : '-' }}

                                @elseif($kategoriKode == 'OLAHRAGA')
                                    {{-- Status olahraga --}}
                                    @if(isset($checklistData['berolahraga']))
                                        Berolahraga
                                    @else
                                        Belum berolahraga
                                    @endif
                                    @if($item['entry']->keterangan)
                                        <br>{{ $item['entry']->keterangan }}
                                    @endif

                                @elseif($kategoriKode == 'MAKAN')
                                    {{-- Hanya tampilkan waktu makan yang ter-check --}}
                                    @php
                                        $makanList = [];
                                        if(isset($checklistData['pagi'])) $makanList[] = 'Pagi';
                                        if(isset($checklistData['siang'])) $makanList[] = 'Siang';
                                        if(isset($checklistData['malam'])) $makanList[] = 'Malam';
                                    @endphp
                                    {{ count($makanList) > 0 ? implode(', ', $makanList) : '-' }}
                                    @if($item['entry']->keterangan)
                                        <br>Menu: {{ $item['entry']->keterangan }}
                                    @endif

                                @elseif($kategoriKode == 'MEMBACA')
                                    {{-- Status belajar --}}
                                    @if(isset($checklistData['belajar']))
                                        Sudah belajar/membaca
                                    @else
                                        Belum belajar
                                    @endif
                                    @if($item['entry']->keterangan)
                                        <br>{{ $item['entry']->keterangan }}
                                    @endif

                                @elseif($kategoriKode == 'SOSIAL')
                                    {{-- Kegiatan sosial --}}
                                    {{ $item['entry']->keterangan ?? '-' }}
                                    @php
                                        $sosialList = [];
                                        if(isset($checklistData['keluarga'])) $sosialList[] = 'Keluarga';
                                        if(isset($checklistData['teman'])) $sosialList[] = 'Teman';
                                        if(isset($checklistData['tetangga'])) $sosialList[] = 'Tetangga';
                                    @endphp
                                    @if(count($sosialList) > 0)
                                        <br>Dengan: {{ implode(', ', $sosialList) }}
                                    @endif
                                @endif

                                {{-- Catatan Guru jika ada --}}
                                @if($item['jurnal']->catatan_guru)
                                    <div class="notes">
                                        <strong>Catatan Guru:</strong> {{ $item['jurnal']->catatan_guru }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center; font-size: 8pt;">
                                @if($item['jurnal']->status == 'verified')
                                    Terverifikasi
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        @endforeach

        <!-- Footer / Signatures -->
        <div class="footer">
            <p style="margin-bottom: 10px;">
                Dicetak pada: {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}
            </p>
            
            <div class="signature-section">
                <div class="signature-box">
                    <p>Orang Tua/Wali</p>
                    <div class="signature-line">
                        ( ........................... )
                    </div>
                </div>
                
                <div class="signature-box right">
                    <p>Guru/Wali Kelas</p>
                    <div class="signature-line">
                        ( ........................... )
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

