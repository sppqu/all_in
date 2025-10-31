<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pendaftaran SPMB - {{ $registration->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .school-address {
            font-size: 12px;
            color: #666;
        }
        .form-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
        }
        .form-section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            background: #f5f5f5;
            padding: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
        }
        .form-row {
            display: flex;
            margin-bottom: 10px;
        }
        .form-label {
            width: 150px;
            font-weight: bold;
            font-size: 12px;
        }
        .form-value {
            flex: 1;
            font-size: 12px;
            border-bottom: 1px solid #333;
            padding-bottom: 2px;
        }
        .form-value:empty::after {
            content: "_________________";
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .registration-number {
            background: #007bff;
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .download-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .download-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
        }
        .download-btn:hover {
            background: #218838;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- Download Section -->
    <div class="download-section no-print">
        <h3>Formulir Pendaftaran SPMB</h3>
        <p>Anda dapat mengunduh atau mencetak formulir pendaftaran Anda di bawah ini.</p>
        <a href="javascript:window.print()" class="download-btn">
            <i class="fas fa-print"></i> Cetak Formulir
        </a>
        <a href="{{ route('spmb.dashboard') }}" class="download-btn" style="background: #6c757d;">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- Header Sekolah -->
    <div class="header">
        <div class="school-name">{{ $schoolProfile->nama_sekolah ?? 'NAMA SEKOLAH' }}</div>
        <div class="school-address">{{ $schoolProfile->alamat ?? 'ALAMAT SEKOLAH' }}</div>
        <div class="school-address">Telp: {{ $schoolProfile->telepon ?? 'TELEPON SEKOLAH' }} | Email: {{ $schoolProfile->email ?? 'EMAIL SEKOLAH' }}</div>
        <div class="form-title">FORMULIR PENDAFTARAN SPMB (SISTEM PENERIMAAN MURID BARU)</div>
    </div>

    <!-- Nomor Pendaftaran -->
    @if($registration->nomor_pendaftaran)
    <div class="registration-number">
        NOMOR PENDAFTARAN: {{ $registration->nomor_pendaftaran }}
    </div>
    @endif

    <!-- Data Pribadi -->
    <div class="form-section">
        <div class="section-title">A. DATA PRIBADI</div>
        
        <div class="form-row">
            <div class="form-label">Nama Lengkap:</div>
            <div class="form-value">{{ $registration->name }}</div>
        </div>
        
        <div class="form-row">
            <div class="form-label">No. HP:</div>
            <div class="form-value">{{ $registration->phone }}</div>
        </div>
        
        <div class="form-row">
            <div class="form-label">Tanggal Lahir:</div>
            <div class="form-value">{{ is_array($registration->form_data['birth_date'] ?? null) ? implode(', ', $registration->form_data['birth_date']) : ($registration->form_data['birth_date'] ?? '-') }}</div>
        </div>
        
        <div class="form-row">
            <div class="form-label">Tempat Lahir:</div>
            <div class="form-value">{{ is_array($registration->form_data['birth_place'] ?? null) ? implode(', ', $registration->form_data['birth_place']) : ($registration->form_data['birth_place'] ?? '-') }}</div>
        </div>
        
        <div class="form-row">
            <div class="form-label">Jenis Kelamin:</div>
            <div class="form-value">
                @php
                    $gender = $registration->form_data['gender'] ?? null;
                    $genderValue = is_array($gender) ? implode(', ', $gender) : $gender;
                @endphp
                {{ $genderValue == 'male' ? 'Laki-laki' : ($genderValue == 'female' ? 'Perempuan' : '-') }}
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-label">Alamat:</div>
            <div class="form-value">{{ is_array($registration->form_data['address'] ?? null) ? implode(', ', $registration->form_data['address']) : ($registration->form_data['address'] ?? '-') }}</div>
        </div>
    </div>

    <!-- Data Orang Tua -->
    <div class="form-section">
        <div class="section-title">B. DATA ORANG TUA</div>
        
        <div class="form-row">
            <div class="form-label">Nama Orang Tua:</div>
            <div class="form-value">{{ is_array($registration->form_data['parent_name'] ?? null) ? implode(', ', $registration->form_data['parent_name']) : ($registration->form_data['parent_name'] ?? '-') }}</div>
        </div>
        
        <div class="form-row">
            <div class="form-label">No. HP Orang Tua:</div>
            <div class="form-value">{{ is_array($registration->form_data['parent_phone'] ?? null) ? implode(', ', $registration->form_data['parent_phone']) : ($registration->form_data['parent_phone'] ?? '-') }}</div>
        </div>
        
        <div class="form-row">
            <div class="form-label">Pekerjaan Orang Tua:</div>
            <div class="form-value">{{ is_array($registration->form_data['parent_occupation'] ?? null) ? implode(', ', $registration->form_data['parent_occupation']) : ($registration->form_data['parent_occupation'] ?? '-') }}</div>
        </div>
    </div>

    <!-- Data Akademik -->
    <div class="form-section">
        <div class="section-title">C. DATA AKADEMIK</div>
        
        <div class="form-row">
            <div class="form-label">Asal Sekolah:</div>
            <div class="form-value">{{ is_array($registration->form_data['school_origin'] ?? null) ? implode(', ', $registration->form_data['school_origin']) : ($registration->form_data['school_origin'] ?? '-') }}</div>
        </div>
        
        <div class="form-row">
            <div class="form-label">Pilihan Kejuruan:</div>
            <div class="form-value">{{ $registration->kejuruan->nama_kejuruan ?? '-' }} ({{ $registration->kejuruan->kode_kejuruan ?? '' }})</div>
        </div>
    </div>

    <!-- Status Pendaftaran -->
    <div class="form-section">
        <div class="section-title">D. STATUS PENDAFTARAN</div>
        
        <div class="form-row">
            <div class="form-label">Status:</div>
            <div class="form-value">
                @if($registration->status_pendaftaran == 'pending')
                    Pending
                @elseif($registration->status_pendaftaran == 'diterima')
                    Diterima
                @elseif($registration->status_pendaftaran == 'ditolak')
                    Ditolak
                @else
                    -
                @endif
            </div>
        </div>
    </div>

    <!-- Tanda Tangan -->
    <div class="form-section">
        <div style="margin-top: 40px;">
            <div style="display: flex; justify-content: space-between;">
                <div style="text-align: center; width: 200px;">
                    <div style="border-bottom: 1px solid #333; height: 50px; margin-bottom: 10px;"></div>
                    <div style="font-size: 12px;">Pendaftar</div>
                    <div style="font-size: 12px;">({{ $registration->name }})</div>
                </div>
                <div style="text-align: center; width: 200px;">
                    <div style="border-bottom: 1px solid #333; height: 50px; margin-bottom: 10px;"></div>
                    <div style="font-size: 12px;">Petugas Penerima</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Formulir ini dicetak pada: {{ now()->format('d F Y H:i:s') }}</p>
        <p>Dicetak dari sistem SPMB {{ $schoolProfile->nama_sekolah ?? 'SEKOLAH' }}</p>
    </div>

    <script>
        // Auto focus untuk print
        window.onload = function() {
            // Focus ke halaman untuk memudahkan print
            window.focus();
        }
    </script>
</body>
</html>

