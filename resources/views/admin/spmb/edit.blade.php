<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pendaftaran - SPMB Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #ffffff;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: #ffffff !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid #e9ecef;
        }

        .navbar-brand {
            color: #008060 !important;
            font-weight: 700;
        }

        .navbar-text {
            color: #008060 !important;
            font-weight: 600;
        }

        .form-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #008060 0%, #006d52 100%);
            border: none;
            border-radius: 15px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 128, 96, 0.4);
        }

        .btn-outline-secondary {
            border-radius: 15px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(108, 117, 125, 0.4);
        }

        .form-control {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #008060;
            box-shadow: 0 0 0 0.2rem rgba(0, 128, 96, 0.25);
            transform: translateY(-2px);
        }

        .form-label {
            font-weight: 600;
            color: #495057;
        }

        .section-header {
            background: linear-gradient(135deg, rgb(8, 129, 45) 0%, #006d52 100%);
            color: white;
            padding: 20px 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
        }

        .form-check-input:checked {
            background-color: #008060;
            border-color: #008060;
        }

        .form-check-input:focus {
            border-color: #008060;
            box-shadow: 0 0 0 0.2rem rgba(0, 128, 96, 0.25);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('manage.spmb.index') }}">
                <i class="fas fa-edit me-2"></i>Edit Pendaftaran SPMB
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('manage.spmb.show', $registration->id) }}">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Detail
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-card">
                    <div class="p-4">
                        <div class="section-header">
                            <h4 class="mb-0">
                                <i class="fas fa-user-edit me-2"></i>Edit Data Pendaftaran #{{ $registration->id }}
                            </h4>
                            <p class="mb-0 mt-2">Perbarui informasi pendaftaran siswa</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <h6 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Terjadi Kesalahan
                                </h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('manage.spmb.update', $registration->id) }}">
                            @csrf
                            @method('PUT')

                            <!-- Basic Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-info-circle me-2"></i>Informasi Dasar
                                    </h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="{{ old('name', $registration->name) }}" required>
                                </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">No. HP</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="{{ old('phone', $registration->phone) }}" required>
                            </div>
                            @if($kejuruan && $kejuruan->count() > 0)
                            <div class="col-md-6 mb-3">
                                <label for="kejuruan_id" class="form-label">Kejuruan</label>
                                <select class="form-control" id="kejuruan_id" name="kejuruan_id" required>
                                    <option value="">Pilih Kejuruan</option>
                                    @foreach($kejuruan as $k)
                                        <option value="{{ $k->id }}" 
                                                {{ old('kejuruan_id', $registration->kejuruan_id) == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kejuruan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-md-6 mb-3">
                                <label for="status_pendaftaran" class="form-label">Status Pendaftaran</label>
                                    <select class="form-control" id="status_pendaftaran" name="status_pendaftaran" required>
                                        <option value="pending" {{ old('status_pendaftaran', $registration->status_pendaftaran) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="diterima" {{ old('status_pendaftaran', $registration->status_pendaftaran) == 'diterima' ? 'selected' : '' }}>Diterima</option>
                                        <option value="ditolak" {{ old('status_pendaftaran', $registration->status_pendaftaran) == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Payment Status -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="text-primary mb-3">
                                        <i class="fas fa-credit-card me-2"></i>Status Pembayaran
                                    </h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="registration_fee_paid" 
                                               name="registration_fee_paid" value="1" 
                                               {{ old('registration_fee_paid', $registration->registration_fee_paid) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="registration_fee_paid">
                                            Biaya Pendaftaran Dibayar
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="spmb_fee_paid" 
                                               name="spmb_fee_paid" value="1" 
                                               {{ old('spmb_fee_paid', $registration->spmb_fee_paid) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="spmb_fee_paid">
                                            Biaya SPMB Dibayar
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamic Form Fields -->
                            @if($formSettings && count($formSettings) > 0)
                                @foreach($formSettings as $section => $fields)
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h5 class="text-primary mb-3">
                                                <i class="fas fa-file-alt me-2"></i>{{ ucfirst(str_replace('_', ' ', $section)) }}
                                            </h5>
                                        </div>
                                        @foreach($fields as $field)
                                            @php
                                                $fieldName = is_array($field->field_name) ? implode(', ', $field->field_name) : $field->field_name;
                                                $fieldLabel = is_array($field->field_label) ? implode(', ', $field->field_label) : $field->field_label;
                                                $fieldType = is_array($field->field_type) ? implode(', ', $field->field_type) : $field->field_type;
                                                $isRequired = is_array($field->is_required) ? implode(', ', $field->is_required) : $field->is_required;
                                            @endphp
                                            <div class="col-md-6 mb-3">
                                                <label for="{{ $fieldName }}" class="form-label">
                                                    {{ $fieldLabel }}
                                                    @if($isRequired)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                                
                                                @php
                                                    // form_data is already cast as array in the model
                                                    $formData = $registration->form_data ?? [];
                                                    $fieldName = is_array($field->field_name) ? implode(', ', $field->field_name) : $field->field_name;
                                                    $rawValue = old($fieldName, $formData[$fieldName] ?? '');
                                                    
                                                    // Ensure we always have a string value for form inputs
                                                    if (is_array($rawValue)) {
                                                        $fieldValue = implode(', ', $rawValue);
                                                    } else {
                                                        $fieldValue = (string) $rawValue;
                                                    }
                                                @endphp

                                                @switch($fieldType)
                                                    @case('email')
                                                        <input type="email" class="form-control" id="{{ $fieldName }}" 
                                                               name="{{ $fieldName }}" value="{{ $fieldValue }}"
                                                               {{ $isRequired ? 'required' : '' }}>
                                                        @break
                                                    @case('tel')
                                                        <input type="tel" class="form-control" id="{{ $fieldName }}" 
                                                               name="{{ $fieldName }}" value="{{ $fieldValue }}"
                                                               {{ $isRequired ? 'required' : '' }}>
                                                        @break
                                                    @case('number')
                                                        <input type="number" class="form-control" id="{{ $fieldName }}" 
                                                               name="{{ $fieldName }}" value="{{ $fieldValue }}"
                                                               {{ $isRequired ? 'required' : '' }}>
                                                        @break
                                                    @case('date')
                                                        <input type="date" class="form-control" id="{{ $fieldName }}" 
                                                               name="{{ $fieldName }}" value="{{ $fieldValue }}"
                                                               {{ $isRequired ? 'required' : '' }}>
                                                        @break
                                                    @case('textarea')
                                                        <textarea class="form-control" id="{{ $fieldName }}" 
                                                                  name="{{ $fieldName }}" rows="3"
                                                                  {{ $isRequired ? 'required' : '' }}>{{ $fieldValue }}</textarea>
                                                        @break
                                                    @case('select')
                                                        <select class="form-control" id="{{ $fieldName }}" 
                                                                name="{{ $fieldName }}" {{ $isRequired ? 'required' : '' }}>
                                                            <option value="">Pilih {{ $fieldLabel }}</option>
                                                            @if($field->field_options)
                                                                @php
                                                                    $fieldOptions = is_array($field->field_options) ? $field->field_options : $field->field_options;
                                                                @endphp
                                                                @foreach($fieldOptions as $option)
                                                                    @php
                                                                        $optionValue = is_array($option) ? implode(', ', $option) : (string) $option;
                                                                    @endphp
                                                                    <option value="{{ $optionValue }}" {{ $fieldValue == $optionValue ? 'selected' : '' }}>
                                                                        {{ $optionValue }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        @break
                                                    @default
                                                        <input type="text" class="form-control" id="{{ $fieldName }}" 
                                                               name="{{ $fieldName }}" value="{{ $fieldValue }}"
                                                               {{ $isRequired ? 'required' : '' }}>
                                                @endswitch
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @endif

                            <!-- Action Buttons -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('manage.spmb.show', $registration->id) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>Batal
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Simpan Perubahan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
