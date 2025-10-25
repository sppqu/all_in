<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pernyataan Pelanggaran - {{ $pelanggaranSiswa->siswa->student_full_name }}</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 2cm;
            }
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .header {
                display: flex !important;
                align-items: center !important;
                page-break-inside: avoid;
            }
            .header .logo {
                width: 100px !important;
                height: 100px !important;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
            background: #fff;
            padding: 20px;
        }

        .container {
            max-width: 21cm;
            margin: 0 auto;
            background: white;
            padding: 30px;
        }

        /* Kop Surat */
        .header {
            display: flex;
            align-items: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            gap: 20px;
        }

        .header .logo-container {
            flex-shrink: 0;
        }

        .header .logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }

        .header .school-info {
            flex-grow: 1;
            text-align: center;
        }

        .header h2 {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 5px 0;
        }

        .header h3 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
        }

        .header .address {
            font-size: 10pt;
            margin: 5px 0;
        }

        /* Judul Surat */
        .title {
            text-align: center;
            margin: 30px 0 20px;
        }

        .title h1 {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }

        .title .nomor {
            font-size: 11pt;
        }

        /* Isi Surat */
        .content {
            text-align: justify;
            margin: 20px 0;
        }

        .content p {
            margin-bottom: 15px;
            text-indent: 40px;
        }

        .content p.no-indent {
            text-indent: 0;
        }

        /* Tabel Data */
        table.data {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        table.data td {
            padding: 5px;
            vertical-align: top;
        }

        table.data td:first-child {
            width: 200px;
        }

        table.data td:nth-child(2) {
            width: 20px;
            text-align: center;
        }

        /* TTD */
        .signature {
            margin-top: 40px;
        }

        .signature-box {
            float: right;
            width: 300px;
            text-align: center;
        }

        .signature-box .place-date {
            margin-bottom: 10px;
        }

        .signature-box .position {
            margin-bottom: 80px;
            font-weight: bold;
        }

        .signature-box .name {
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-box .nip {
            font-size: 10pt;
        }

        /* Print Button */
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .btn-print {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .btn-print:hover {
            background: #0b5ed7;
        }

        .btn-close {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .btn-close:hover {
            background: #5c636a;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .text-center {
            text-align: center;
        }

        .text-bold {
            font-weight: bold;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .highlight {
            background-color: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
            🖨️ Cetak Surat
        </button>
        <button onclick="window.close()" class="btn-close">
            ✕ Tutup
        </button>
    </div>

    <div class="container">
        <!-- Kop Surat -->
        <div class="header">
            <div class="logo-container">
                @if($schoolProfile && $schoolProfile->logo_sekolah)
                    <img src="{{ asset('storage/' . $schoolProfile->logo_sekolah) }}" alt="Logo" class="logo">
                @else
                    <div style="width: 100px; height: 100px; border: 2px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 10pt; color: #999;">
                        LOGO
                    </div>
                @endif
            </div>
            <div class="school-info">
                <h2>{{ $schoolProfile->nama_sekolah ?? 'NAMA SEKOLAH' }}</h2>
                <h3>BIMBINGAN DAN KONSELING</h3>
                <div class="address">
                    {{ $schoolProfile->alamat ?? 'Alamat Sekolah' }}<br>
                    Telp: {{ $schoolProfile->no_telp ?? '-' }}
                </div>
            </div>
        </div>

        <!-- Judul Surat -->
        <div class="title">
            <h1>SURAT PERNYATAAN PELANGGARAN</h1>
            <div class="nomor">
                Nomor: {{ str_pad($pelanggaranSiswa->id, 4, '0', STR_PAD_LEFT) }}/BK/{{ $pelanggaranSiswa->created_at->format('Y') }}
            </div>
        </div>

        <!-- Isi Surat -->
        <div class="content">
            <p class="no-indent">Yang bertanda tangan di bawah ini:</p>

            <table class="data">
                <tr>
                    <td>Nama Lengkap</td>
                    <td>:</td>
                    <td><strong>{{ $pelanggaranSiswa->siswa->student_full_name }}</strong></td>
                </tr>
                <tr>
                    <td>Nomor Induk Siswa (NIS)</td>
                    <td>:</td>
                    <td><strong>{{ $pelanggaranSiswa->siswa->student_nis }}</strong></td>
                </tr>
                <tr>
                    <td>Kelas</td>
                    <td>:</td>
                    <td><strong>{{ $pelanggaranSiswa->siswa->class->class_name ?? '-' }}</strong></td>
                </tr>
            </table>

            <p>Dengan ini menyatakan bahwa saya telah melakukan pelanggaran tata tertib sekolah dengan rincian sebagai berikut:</p>

            <div class="highlight">
                <table class="data">
                    <tr>
                        <td>Tanggal Kejadian</td>
                        <td>:</td>
                        <td><strong>{{ $pelanggaranSiswa->tanggal_pelanggaran->format('d F Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Jenis Pelanggaran</td>
                        <td>:</td>
                        <td><strong>{{ $pelanggaranSiswa->pelanggaran->nama }}</strong></td>
                    </tr>
                    <tr>
                        <td>Kategori</td>
                        <td>:</td>
                        <td><strong>{{ $pelanggaranSiswa->pelanggaran->kategori->nama }}</strong></td>
                    </tr>
                    <tr>
                        <td>Poin Pelanggaran</td>
                        <td>:</td>
                        <td><strong>{{ $pelanggaranSiswa->pelanggaran->point }} Point</strong></td>
                    </tr>
                    @if($pelanggaranSiswa->tempat)
                    <tr>
                        <td>Tempat Kejadian</td>
                        <td>:</td>
                        <td>{{ $pelanggaranSiswa->tempat }}</td>
                    </tr>
                    @endif
                </table>

                @if($pelanggaranSiswa->keterangan)
                <p class="no-indent mt-20"><strong>Kronologi Kejadian:</strong></p>
                <p>{{ $pelanggaranSiswa->keterangan }}</p>
                @endif
            </div>

            <p>Dengan ini saya menyatakan dengan sesungguhnya bahwa:</p>
            
            <ol style="margin-left: 40px; margin-bottom: 15px;">
                <li>Saya mengakui telah melakukan pelanggaran tersebut di atas.</li>
                <li>Saya menyesali perbuatan yang telah saya lakukan.</li>
                <li>Saya berjanji tidak akan mengulangi perbuatan yang sama di kemudian hari.</li>
                <li>Saya bersedia menerima sanksi yang diberikan sesuai dengan peraturan sekolah yang berlaku.</li>
                <li>Apabila saya melanggar janji ini, saya bersedia menerima sanksi yang lebih berat.</li>
            </ol>

            <p>Demikian surat pernyataan ini saya buat dengan sebenar-benarnya tanpa ada paksaan dari pihak manapun.</p>
        </div>

        <!-- TTD -->
        <div class="signature clearfix">
            <!-- TTD Siswa -->
            <div class="signature-box">
                <div class="place-date">
                    @php
                        // Extract city from school address or use default
                        $city = 'Kota';
                        if ($schoolProfile && $schoolProfile->alamat) {
                            $alamatParts = explode(',', $schoolProfile->alamat);
                            $city = trim($alamatParts[count($alamatParts) - 1]);
                        }
                    @endphp
                    {{ $city }}, {{ $pelanggaranSiswa->created_at->format('d F Y') }}
                </div>
                <div class="position">
                    Yang Membuat Pernyataan,<br>
                    Siswa
                </div>
                <div class="name">
                    {{ $pelanggaranSiswa->siswa->student_full_name }}
                </div>
                <div class="nip">
                    NIS: {{ $pelanggaranSiswa->siswa->student_nis }}
                </div>
            </div>
        </div>

        <!-- Mengetahui -->
        <div class="mt-20 clearfix">
            <p class="no-indent text-bold">Mengetahui / Menyaksikan:</p>
            
            <div style="margin-top: 30px;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 33%; vertical-align: top;">
                            <div class="text-center">
                                <div class="text-bold">Guru BK</div>
                                <div style="height: 80px;"></div>
                                <div class="text-bold" style="text-decoration: underline;">
                                    {{ $pelanggaranSiswa->creator->name ?? '___________________' }}
                                </div>
                                <div style="font-size: 10pt;">NIP/NUPTK: _______________</div>
                            </div>
                        </td>
                        <td style="width: 33%; vertical-align: top;">
                            <div class="text-center">
                                <div class="text-bold">Saksi I</div>
                                <div style="height: 80px;"></div>
                                <div class="text-bold" style="text-decoration: underline;">
                                    ___________________
                                </div>
                                <div style="font-size: 10pt;">Wali Kelas / Guru</div>
                            </div>
                        </td>
                        <td style="width: 33%; vertical-align: top;">
                            <div class="text-center">
                                <div class="text-bold">Saksi II</div>
                                <div style="height: 80px;"></div>
                                <div class="text-bold" style="text-decoration: underline;">
                                    ___________________
                                </div>
                                <div style="font-size: 10pt;">Orang Tua / Wali Murid</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Auto print when opened in new window
        window.onload = function() {
            // Optional: auto print
            // window.print();
        }
    </script>
</body>
</html>

