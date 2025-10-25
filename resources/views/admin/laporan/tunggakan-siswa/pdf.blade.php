<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tunggakan Siswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #000;
            width: 100%;
        }
        .school-info {
            padding: 0;
            background-color: transparent;
            width: 65%;
        }
        .report-title-box {
            border: none;
            padding: 0;
            background-color: transparent;
            text-align: right;
            width: 35%;
        }
        .school-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .school-address {
            font-size: 11px;
            margin-bottom: 3px;
        }
        .school-phone {
            font-size: 11px;
        }
        .report-title-box {
            border: none;
            padding: 0;
            background-color: transparent;
            text-align: right;
        }
        .report-title-text {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .filter-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .filter-info h6 {
            margin: 0 0 10px 0;
            font-weight: bold;
        }
                 .filter-info p {
             margin: 2px 0;
         }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            font-size: 11px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
         <div class="header">
         <div class="school-info">
             <div class="school-name">{{ $school->nama_sekolah ?? 'NAMA SEKOLAH' }}</div>
             <div class="school-address">{{ $school->alamat ?? 'ALAMAT SEKOLAH' }}</div>
             <div class="school-phone">Telp: {{ $school->no_telp ?? '-' }}</div>
         </div>
         
         <div class="report-title-box">
             <div class="report-title-text">LAPORAN TUNGGAKAN SISWA</div>
         </div>
     </div>

     <div class="report-title">
         @if($periodId || $monthId)
             @if($periodId)
                 @php
                     $period = \DB::table('periods')->where('period_id', $periodId)->first();
                     $periodName = $period ? $period->period_start . '/' . $period->period_end : '';
                 @endphp
                 Tahun Pelajaran: {{ $periodName }}
             @endif
             @if($monthId)
                 @php
                     $monthNames = [1=>'Juli',2=>'Agustus',3=>'September',4=>'Oktober',5=>'November',6=>'Desember',7=>'Januari',8=>'Februari',9=>'Maret',10=>'April',11=>'Mei',12=>'Juni'];
                     $monthName = $monthNames[$monthId] ?? '';
                 @endphp
                 Sampai Bulan: {{ $monthName }}
             @endif
         @else
             Semua Data Tunggakan
         @endif
     </div>

     @if(!empty($filterInfo))
     <div class="filter-info">
         <h6>Filter yang Diterapkan:</h6>
         @if(isset($filterInfo['period']))
             <p><strong>Tahun Pelajaran:</strong> {{ $filterInfo['period'] }}</p>
         @endif
         @if(isset($filterInfo['month']))
             <p><strong>Sampai Bulan:</strong> {{ $filterInfo['month'] }}</p>
         @endif
         @if(isset($filterInfo['student']))
             <p><strong>Siswa:</strong> {{ $filterInfo['student'] }}</p>
         @endif
         @if(isset($filterInfo['pos']))
             <p><strong>POS:</strong> {{ $filterInfo['pos'] }}</p>
         @endif
         @if(isset($filterInfo['class']))
             <p><strong>Kelas:</strong> {{ $filterInfo['class'] }}</p>
         @endif
         @if(isset($filterInfo['status']))
             <p><strong>Status:</strong> {{ $filterInfo['status'] }}</p>
         @endif
     </div>
     @endif

     @if($tunggakanData->count() > 0)

    @foreach($tunggakanData as $index => $data)
    <div style="margin-bottom: 30px;">
        <h5 style="margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
            {{ $index + 1 }}. {{ $data['student_name'] }} ({{ $data['student_nis'] }}) - {{ $data['class_name'] }}
        </h5>
        
                 <table style="margin-bottom: 15px; width: 100%;">
             <thead>
                 <tr>
                     <th style="width: 8%; text-align: center;">No.</th>
                     <th style="width: 35%; text-align: left;">POS</th>
                     <th style="width: 12%; text-align: center;">Jenis</th>
                     <th style="width: 15%; text-align: right;">Tagihan</th>
                     <th style="width: 15%; text-align: right;">Terbayar</th>
                     <th style="width: 15%; text-align: right;">Tunggakan</th>
                 </tr>
             </thead>
             <tbody>
                 @foreach($data['detail_tunggakan'] as $detailIndex => $detail)
                 <tr>
                     <td style="text-align: center;">{{ $detailIndex + 1 }}</td>
                     <td style="text-align: left;">{{ $detail['pos_name'] }}</td>
                     <td style="text-align: center;">{{ ucfirst($detail['jenis']) }}</td>
                     <td style="text-align: right;">Rp {{ number_format($detail['bill'], 0, ',', '.') }}</td>
                     <td style="text-align: right;">Rp {{ number_format($detail['pay'], 0, ',', '.') }}</td>
                     <td style="text-align: right;">Rp {{ number_format($detail['tunggakan'], 0, ',', '.') }}</td>
                 </tr>
                 @endforeach
             </tbody>
             <tfoot>
                 <tr style="background-color: #e9ecef; font-weight: bold; border-top: 2px solid #333;">
                     <td colspan="5" style="text-align: right; padding: 8px;">Total Tunggakan:</td>
                     <td style="text-align: right; padding: 8px; color: #dc3545;">Rp {{ number_format($data['total_tunggakan'], 0, ',', '.') }}</td>
                 </tr>
             </tfoot>
         </table>
    </div>
    @endforeach

    @else
    <div style="text-align: center; padding: 50px; color: #666;">
        <h4>Tidak ada data tunggakan untuk periode yang dipilih.</h4>
    </div>
    @endif

    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        <p>Oleh: {{ auth()->user()->name ?? 'System' }}</p>
    </div>
</body>
</html>
