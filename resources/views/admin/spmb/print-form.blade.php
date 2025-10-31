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
            flex-shrink: 0;
        }
        .form-value {
            flex: 1;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
            min-height: 20px;
        }
        .form-value-empty {
            color: #999;
            font-style: italic;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-button:hover {
            background: #0056b3;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                margin: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Cetak</button>

    <div class="header">
        @if($schoolProfile)
            <div class="school-name">{{ $schoolProfile->nama_sekolah ?? 'Nama Sekolah' }}</div>
            <div class="school-address">{{ $schoolProfile->alamat ?? 'Alamat Sekolah' }}</div>
        @else
            <div class="school-name">Nama Sekolah</div>
            <div class="school-address">Alamat Sekolah</div>
        @endif
        <div class="form-title">FORMULIR PENDAFTARAN SISWA BARU</div>
    </div>

    <!-- Data Dasar -->
    <div class="form-section">
        <div class="section-title">A. DATA DASAR</div>
        
        <div class="form-row">
            <div class="form-label">Nama Lengkap:</div>
            <div class="form-value">{{ $registration->name }}</div>
        </div>
        
        <div class="form-row">
            <div class="form-label">No. HP:</div>
            <div class="form-value">{{ $registration->phone }}</div>
        </div>
        
        <div class="form-row">
            <div class="form-label">Nomor Pendaftaran:</div>
            <div class="form-value">{{ $registration->nomor_pendaftaran ?? '-' }}</div>
        </div>
    </div>

    <!-- Dynamic Form Fields -->
    @foreach($printFields as $section => $fields)
        @if(count($fields) > 0)
            <div class="form-section">
                <div class="section-title">
                    {{ $section == 'personal' ? 'B. DATA PRIBADI' : ($section == 'parent' ? 'C. DATA ORANG TUA' : 'D. DATA AKADEMIK') }}
                </div>
                
                @foreach($fields as $field)
                    <div class="form-row">
                        <div class="form-label">{{ is_array($field->field_label) ? implode(', ', $field->field_label) : $field->field_label }}:</div>
                        <div class="form-value">
                            @if(isset($registration->form_data[is_array($field->field_name) ? implode(', ', $field->field_name) : $field->field_name]))
                                @php
                                    $fieldName = is_array($field->field_name) ? implode(', ', $field->field_name) : $field->field_name;
                                    $rawValue = $registration->form_data[$fieldName];
                                    $value = is_array($rawValue) ? implode(', ', $rawValue) : $rawValue;
                                @endphp
                                @if((is_array($field->field_type) ? implode(', ', $field->field_type) : $field->field_type) == 'select' && $field->field_options)
                                    @php
                                        $fieldOptions = is_array($field->field_options) ? $field->field_options : $field->field_options;
                                        $option = collect($fieldOptions)->firstWhere('value', $value);
                                        $displayValue = $option ? $option['label'] : $value;
                                        // Ensure displayValue is string
                                        $displayValue = is_array($displayValue) ? implode(', ', $displayValue) : (string) $displayValue;
                                    @endphp
                                    {{ $displayValue }}
                                @elseif((is_array($field->field_type) ? implode(', ', $field->field_type) : $field->field_type) == 'date')
                                    @php
                                        $dateValue = is_array($value) ? implode(', ', $value) : $value;
                                    @endphp
                                    {{ \Carbon\Carbon::parse($dateValue)->format('d/m/Y') }}
                                @else
                                    @php
                                        $outputValue = is_array($value) ? implode(', ', $value) : (string) $value;
                                    @endphp
                                    {{ $outputValue }}
                                @endif
                            @else
                                <span class="form-value-empty">-</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach

    <!-- Pilihan Kejuruan -->
    <div class="form-section">
        <div class="section-title">E. PILIHAN KEJURUAN</div>
        
        <div class="form-row">
            <div class="form-label">Kejuruan:</div>
            <div class="form-value">{{ $registration->kejuruan->nama_kejuruan ?? '-' }} ({{ $registration->kejuruan->kode_kejuruan ?? '' }})</div>
        </div>
    </div>

    <!-- Status Pendaftaran -->
    <div class="form-section">
        <div class="section-title">F. STATUS PENDAFTARAN</div>
        
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
        <div class="section-title">G. TANDA TANGAN</div>
        
        <div style="display: flex; justify-content: space-between; margin-top: 40px;">
            <div style="text-align: center; width: 200px;">
                <div style="border-bottom: 1px solid #333; height: 60px; margin-bottom: 10px;"></div>
                <div style="font-size: 12px;">Pendaftar</div>
                <div style="font-size: 12px;">({{ $registration->name }})</div>
            </div>
            
            <div style="text-align: center; width: 200px;">
                <div style="border-bottom: 1px solid #333; height: 60px; margin-bottom: 10px;"></div>
                <div style="font-size: 12px;">Panitia Penerimaan</div>
                <div style="font-size: 12px;">(___________________)</div>
            </div>
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>