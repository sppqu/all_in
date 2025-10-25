<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Perpustakaan - {{ $class->class_name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
        }
        
        .page {
            width: 100%;
        }
        
        /* Table untuk layout kanan-kiri */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
        }
        
        td {
            width: 50%;
            padding: 2mm;
            vertical-align: top;
        }
        
        /* Kartu - Ukuran ATM: 85.6mm × 53.98mm */
        .card {
            width: 85.6mm;
            height: 53.98mm;
            border: 2px solid #333;
            border-radius: 3mm;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4mm;
            position: relative;
        }
        
        .card-logo {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        
        .card-school {
            font-size: 7pt;
            margin-bottom: 4mm;
            line-height: 1.2;
            opacity: 0.9;
        }
        
        .card-name {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 3mm;
            line-height: 1.2;
            border-bottom: 1px solid rgba(255,255,255,0.3);
            padding-bottom: 2mm;
        }
        
        .card-detail {
            font-size: 8pt;
            margin-bottom: 1.5mm;
            line-height: 1.3;
        }
        
        .card-detail strong {
            display: inline-block;
            width: 12mm;
        }
        
        .card-footer {
            position: absolute;
            bottom: 4mm;
            left: 4mm;
            right: 4mm;
            font-size: 6pt;
            padding-top: 2mm;
            border-top: 1px solid rgba(255,255,255,0.3);
            text-align: center;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @php
        $cardsPerPage = 10; // 5 rows × 2 columns
        $totalStudents = $students->count();
        $totalPages = ceil($totalStudents / $cardsPerPage);
    @endphp
    
    @for($page = 0; $page < $totalPages; $page++)
        @php
            $pageStudents = $students->slice($page * $cardsPerPage, $cardsPerPage);
            $rows = $pageStudents->chunk(2);
        @endphp
        
        <div class="page @if($page < $totalPages - 1) page-break @endif">
            <table>
                @foreach($rows as $rowStudents)
                <tr>
                    @foreach($rowStudents as $student)
                    <td>
                        <div class="card">
                            <div class="card-logo">📚 E-PERPUSTAKAAN</div>
                            <div class="card-school">{{ strtoupper($schoolProfile->nama_sekolah ?? 'PERPUSTAKAAN SEKOLAH') }}</div>
                            
                            <div class="card-name">{{ strtoupper($student->student_full_name) }}</div>
                            
                            <div class="card-detail"><strong>NIS</strong>: {{ $student->student_nis }}</div>
                            <div class="card-detail"><strong>Kelas</strong>: {{ $class->class_name }}</div>
                            <div class="card-detail"><strong>ID</strong>: LIB{{ str_pad($student->student_id, 6, '0', STR_PAD_LEFT) }}</div>
                            
                            <div class="card-footer">
                                Member {{ date('Y') }} • Valid s/d {{ date('Y')+1 }}
                            </div>
                        </div>
                    </td>
                    @endforeach
                    
                    @if($rowStudents->count() == 1)
                    <td><!-- Empty --></td>
                    @endif
                </tr>
                @endforeach
            </table>
        </div>
    @endfor
</body>
</html>
