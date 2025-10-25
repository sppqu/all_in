<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tunggakan Siswa</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #000; }
        .header { text-align: left; margin-bottom: 16px; }
        .school-name { font-weight: bold; font-size: 14px; }
        .divider { border-top: 1px solid #000; margin: 10px 0 16px; }
        .info-table { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
        .info-table td { padding: 3px 0; vertical-align: top; border: none !important; }
        .info-table th { border: none !important; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .mb-8 { margin-bottom: 8px; }
    </style>
    <?php $monthNames = [1=>'Juli',2=>'Agustus',3=>'September',4=>'Oktober',5=>'November',6=>'Desember',7=>'Januari',8=>'Februari',9=>'Maret',10=>'April',11=>'Mei',12=>'Juni']; ?>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ $school->nama_sekolah ?? 'NAMA LEMBAGA ANDA' }}</div>
        <div>{{ $school->alamat ?? 'Alamat Lembaga Anda' }}</div>
        <div>Telp: {{ $school->no_telp ?? '-' }}</div>
    </div>
    <div class="divider"></div>

    <table class="info-table">
        <tr>
            <td style="width: 18%"><strong>NIS</strong></td>
            <td style="width: 32%">: {{ $studentData->student_nis ?? '-' }}</td>
            <td style="width: 18%"><strong>Nama Siswa</strong></td>
            <td style="width: 32%">: {{ $studentData->student_name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Kelas</strong></td>
            <td>: {{ $studentData->class_name ?? '-' }}</td>
            <td><strong>Status Siswa</strong></td>
            <td>: {{ (isset($studentData->student_status) && (int)$studentData->student_status === 1) ? 'Aktif' : 'Tidak Aktif' }}</td>
        </tr>
        
    </table>

    <div class="mb-8" style="font-weight:bold; text-align:center;">Rincian Tunggakan s.d. {{ !empty($monthId) ? ($monthNames[$monthId] ?? '-') : 'Semua Bulan' }}</div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%">No</th>
                <th style="width: 45%">POS</th>
                <th class="text-right" style="width: 15%">Tagihan</th>
                <th class="text-right" style="width: 15%">Terbayar</th>
                <th class="text-right" style="width: 20%">Tunggakan</th>
            </tr>
        </thead>
        <tbody>
            @php $totalBill = 0; $totalPay = 0; $totalArrear = 0; @endphp
            @php $details = is_array($studentData->detail_tunggakan ?? null) ? $studentData->detail_tunggakan : []; @endphp
            @forelse($details as $i => $item)
                @php
                    $totalBill += (float)($item['bill'] ?? 0);
                    $totalPay += (float)($item['pay'] ?? 0);
                    $totalArrear += (float)($item['tunggakan'] ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item['pos_name'] ?? '' }}</td>
                    <td class="text-right">Rp {{ number_format($item['bill'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['pay'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['tunggakan'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">Total</th>
                <th class="text-right">Rp {{ number_format($totalBill, 0, ',', '.') }}</th>
                <th class="text-right">Rp {{ number_format($totalPay, 0, ',', '.') }}</th>
                <th class="text-right">Rp {{ number_format($totalArrear, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="mb-8"></div>
    <div style="font-size: 11px;">Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</div>
</body>
</html>

