<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pendaftar SPMB - Admin</title>
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('manage.spmb.index') }}">
                <i class="fas fa-user-plus me-2"></i>Tambah Pendaftar SPMB
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('manage.spmb.index') }}">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-card">
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">
                                <i class="fas fa-user-plus me-2"></i>Tambah Pendaftar SPMB
                            </h4>
                            <span class="badge bg-primary">Admin Form</span>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('manage.spmb.store') }}">
                            @csrf
                            
                            <!-- Data Dasar -->
                            <div class="section-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user me-2"></i>Data Dasar
                                </h5>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user me-1"></i>Nama Lengkap *
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}"
                                           placeholder="Masukkan nama lengkap"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-1"></i>No. HP *
                                    </label>
                                    <input type="tel" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone') }}"
                                           placeholder="Masukkan nomor HP"
                                           required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Password akan dibuat otomatis dari 6 digit terakhir nomor HP</small>
                                </div>
                            </div>
                            
                            @foreach($formSettings as $section => $fields)
                                @if(count($fields) > 0)
                                    <div class="section-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-{{ $section == 'personal' ? 'user' : ($section == 'parent' ? 'users' : 'graduation-cap') }} me-2"></i>
                                            {{ $section == 'personal' ? 'Data Pribadi' : ($section == 'parent' ? 'Data Orang Tua' : 'Data Akademik') }}
                                        </h5>
                                    </div>
                                    
                                    <div class="row">
                                        @foreach($fields as $field)
                                            <div class="col-md-6 mb-3">
                                                <label for="{{ $field->field_name }}" class="form-label">
                                                    {{ $field->field_label }}
                                                    @if($field->is_required)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                                
                                                @if($field->field_type == 'textarea')
                                                    <textarea class="form-control @error($field->field_name) is-invalid @enderror" 
                                                              id="{{ $field->field_name }}" 
                                                              name="{{ $field->field_name }}" 
                                                              rows="3"
                                                              placeholder="{{ $field->field_placeholder }}"
                                                              {{ $field->is_required ? 'required' : '' }}>{{ old($field->field_name) }}</textarea>
                                                @elseif($field->field_type == 'select')
                                                    <select class="form-control @error($field->field_name) is-invalid @enderror" 
                                                            id="{{ $field->field_name }}" 
                                                            name="{{ $field->field_name }}"
                                                            {{ $field->is_required ? 'required' : '' }}>
                                                        <option value="">Pilih {{ $field->field_label }}</option>
                                                        @if($field->field_options)
                                                            @foreach($field->field_options as $option)
                                                                <option value="{{ $option['value'] ?? $option }}" 
                                                                        {{ old($field->field_name) == ($option['value'] ?? $option) ? 'selected' : '' }}>
                                                                    {{ $option['label'] ?? $option }}
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                @else
                                                    <input type="{{ $field->field_type }}" 
                                                           class="form-control @error($field->field_name) is-invalid @enderror" 
                                                           id="{{ $field->field_name }}" 
                                                           name="{{ $field->field_name }}" 
                                                           value="{{ old($field->field_name) }}"
                                                           placeholder="{{ $field->field_placeholder }}"
                                                           {{ $field->is_required ? 'required' : '' }}>
                                                @endif
                                                
                                                @if($field->field_help_text)
                                                    <small class="form-text text-muted">{{ $field->field_help_text }}</small>
                                                @endif
                                                
                                                @error($field->field_name)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                            
                            <!-- Pilihan Kejuruan - Only show if there are available kejuruans -->
                            @if($kejuruan && $kejuruan->count() > 0)
                            <div class="section-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-graduation-cap me-2"></i>Pilihan Kejuruan
                                </h5>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="kejuruan_id" class="form-label">
                                        <i class="fas fa-graduation-cap me-1"></i>Kejuruan *
                                    </label>
                                    <select class="form-control @error('kejuruan_id') is-invalid @enderror" 
                                            id="kejuruan_id" 
                                            name="kejuruan_id" 
                                            required>
                                        <option value="">Pilih Kejuruan</option>
                                        @foreach($kejuruan as $k)
                                            <option value="{{ $k->id }}" {{ old('kejuruan_id') == $k->id ? 'selected' : '' }}>
                                                {{ $k->nama_kejuruan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kejuruan_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                            @else
                            <div class="row">
                                <div class="col-md-12 mb-3">
                            @endif
                                    <label for="status_pendaftaran" class="form-label">
                                        <i class="fas fa-check-circle me-1"></i>Status Pendaftaran *
                                    </label>
                                    <select class="form-control @error('status_pendaftaran') is-invalid @enderror" 
                                            id="status_pendaftaran" 
                                            name="status_pendaftaran" 
                                            required>
                                        <option value="">Pilih Status</option>
                                        <option value="pending" {{ old('status_pendaftaran') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="diterima" {{ old('status_pendaftaran') == 'diterima' ? 'selected' : '' }}>Diterima</option>
                                        <option value="ditolak" {{ old('status_pendaftaran') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                    </select>
                                    @error('status_pendaftaran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status Pembayaran -->
                            <div class="section-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-credit-card me-2"></i>Status Pembayaran (Optional)
                                </h5>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="registration_fee_paid" name="registration_fee_paid" value="1" {{ old('registration_fee_paid') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="registration_fee_paid">
                                            <i class="fas fa-money-bill me-1"></i>Biaya Pendaftaran (Rp 50.000) - Lunas
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="spmb_fee_paid" name="spmb_fee_paid" value="1" {{ old('spmb_fee_paid') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="spmb_fee_paid">
                                            <i class="fas fa-money-bill me-1"></i>Biaya SPMB (Rp 100.000) - Lunas
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Informasi:</strong> Checklist biaya bersifat optional. Jika tidak dicentang, pendaftar dianggap belum membayar biaya tersebut.
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('manage.spmb.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Simpan Pendaftar
                                </button>
                            </div>
                        </form>

                        <!-- Info Section -->
                        <div class="alert alert-info mt-4">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>Informasi Penting:
                            </h6>
                            <ul class="mb-0">
                                <li>Password akan dibuat otomatis dari 6 digit terakhir nomor HP</li>
                                <li>Nomor pendaftaran akan dibuat otomatis (format: SPMB-YYYY-NNNN)</li>
                                <li>Status pembayaran dapat diatur sesuai kebutuhan</li>
                                <li>Mock payment akan dibuat jika pembayaran dicentang</li>
                                <li>Pendaftar akan langsung masuk ke step 6 (selesai)</li>
                                <li>Data yang diisi akan tersimpan sebagai form_data</li>
                                <li>Form menggunakan pengaturan dinamis dari admin</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p class="text-muted mb-0">
                        <i class="fas fa-graduation-cap me-1"></i>
                        SPMB {{ date('Y') }} powered by 
                        <strong class="text-primary">SPPQU</strong>
                    </p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>