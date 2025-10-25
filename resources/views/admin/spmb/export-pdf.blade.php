<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data SPMB - {{ $schoolProfile->nama_sekolah ?? 'SPPQU' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #ffffff;
            color: #000;
            line-height: 1.4;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #008060;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            color: #008060;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 14px;
            color: #666;
        }

        .info-section {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .info-section h3 {
            font-size: 16px;
            color: #008060;
            margin-bottom: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }

        .info-label {
            font-weight: bold;
            color: #333;
        }

        .info-value {
            color: #666;
        }

        .table-container {
            margin-bottom: 30px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }

        .table th {
            background: #008060;
            color: white;
            font-weight: bold;
        }

        .table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .table tr:hover {
            background: #e9ecef;
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .summary-section {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .summary-item {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .summary-number {
            font-size: 20px;
            font-weight: bold;
            color: #008060;
        }

        .summary-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            body {
                font-size: 10px;
            }
            
            .table th,
            .table td {
                font-size: 8px;
                padding: 4px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>DATA PENDAFTARAN SPMB</h1>
        <p>{{ $schoolProfile->nama_sekolah ?? 'SPPQU' }}</p>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3 style="text-align: center; margin-bottom: 15px; color: #008060;">RINGKASAN DATA</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-number">{{ $registrations->count() }}</div>
                <div class="summary-label">Total Pendaftar</div>
            </div>
            <div class="summary-item">
                <div class="summary-number">{{ $registrations->where('status', 'approved')->count() }}</div>
                <div class="summary-label">Diterima</div>
            </div>
            <div class="summary-item">
                <div class="summary-number">{{ $registrations->where('status', 'pending')->count() }}</div>
                <div class="summary-label">Pending</div>
            </div>
            <div class="summary-item">
                <div class="summary-number">{{ $registrations->where('status', 'rejected')->count() }}</div>
                <div class="summary-label">Ditolak</div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-container">
        <h3 style="color: #008060; margin-bottom: 15px;">DETAIL PENDAFTARAN</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>No. HP</th>
                    <th>Email</th>
                    <th>Tanggal Daftar</th>
                    <th>Status</th>
                    <th>Step</th>
                    <th>Pembayaran</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registrations as $index => $registration)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $registration->name }}</td>
                    <td>{{ $registration->phone }}</td>
                    <td>{{ $registration->email }}</td>
                    <td>{{ $registration->created_at->format('d/m/Y') }}</td>
                    <td>
                        <span class="status-badge status-{{ $registration->status }}">
                            @if($registration->status == 'pending')
                                PENDING
                            @elseif($registration->status == 'approved')
                                DITERIMA
                            @elseif($registration->status == 'rejected')
                                DITOLAK
                            @elseif($registration->status == 'completed')
                                SELESAI
                            @else
                                {{ strtoupper($registration->status) }}
                            @endif
                        </span>
                    </td>
                    <td>{{ $registration->step }}/6</td>
                    <td>
                        @if($registration->payments->count() > 0)
                            {{ $registration->payments->where('status', 'paid')->count() }}/{{ $registration->payments->count() }} Lunas
                        @else
                            Belum Bayar
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Payment Summary -->
    @if($registrations->where('payments', '!=', null)->count() > 0)
    <div class="table-container">
        <h3 style="color: #008060; margin-bottom: 15px;">RINGKASAN PEMBAYARAN</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Jenis Pembayaran</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @php $paymentIndex = 1; @endphp
                @foreach($registrations as $registration)
                    @foreach($registration->payments as $payment)
                    <tr>
                        <td>{{ $paymentIndex++ }}</td>
                        <td>{{ $registration->name }}</td>
                        <td>{{ $payment->getTypeName() }}</td>
                        <td>{{ $payment->getAmountFormattedAttribute() }}</td>
                        <td>
                            <span class="status-badge status-{{ $payment->status }}">
                                @if($payment->status == 'paid')
                                    LUNAS
                                @elseif($payment->status == 'pending')
                                    PENDING
                                @elseif($payment->status == 'expired')
                                    KADALUARSA
                                @else
                                    GAGAL
                                @endif
                            </span>
                        </td>
                        <td>{{ $payment->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Document Summary -->
    @if($registrations->where('documents', '!=', null)->count() > 0)
    <div class="table-container">
        <h3 style="color: #008060; margin-bottom: 15px;">RINGKASAN DOKUMEN</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Jenis Dokumen</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Tanggal Upload</th>
                </tr>
            </thead>
            <tbody>
                @php $documentIndex = 1; @endphp
                @foreach($registrations as $registration)
                    @foreach($registration->documents as $document)
                    <tr>
                        <td>{{ $documentIndex++ }}</td>
                        <td>{{ $registration->name }}</td>
                        <td>{{ $document->getDocumentTypeName() }}</td>
                        <td>{{ $document->file_name }}</td>
                        <td>
                            <span class="status-badge status-{{ $document->status }}">
                                @if($document->status == 'approved')
                                    DISETUJUI
                                @elseif($document->status == 'pending')
                                    PENDING
                                @elseif($document->status == 'rejected')
                                    DITOLAK
                                @else
                                    {{ strtoupper($document->status) }}
                                @endif
                            </span>
                        </td>
                        <td>{{ $document->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>{{ $schoolProfile->nama_sekolah ?? 'SPPQU' }}</strong></p>
        <p>{{ $schoolProfile->alamat ?? 'Alamat Sekolah' }}</p>
        <p>Telp: {{ $schoolProfile->telepon ?? '-' }} | Email: {{ $schoolProfile->email ?? '-' }}</p>
        <p>Laporan ini dicetak pada {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>

