<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langkah 3 - SPMB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/spmb-steps.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('spmb.dashboard') }}">
                <i class="fas fa-graduation-cap me-2"></i>SPMB - Langkah 3
            </a>
            <div class="navbar-nav ms-auto">
                <form method="POST" action="{{ route('spmb.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Progress Indicator -->
        <div class="step-progress">
            <div class="steps-indicator">
                <div class="step-item completed">
                    <div class="step-circle"><i class="fas fa-check"></i></div>
                    <div class="step-line"></div>
                    <div class="step-label">Pendaftaran</div>
                </div>
                <div class="step-item completed">
                    <div class="step-circle"><i class="fas fa-check"></i></div>
                    <div class="step-line"></div>
                    <div class="step-label">Pembayaran</div>
                </div>
                <div class="step-item active">
                    <div class="step-circle">3</div>
                    <div class="step-line"></div>
                    <div class="step-label">Formulir</div>
                </div>
                <div class="step-item">
                    <div class="step-circle">4</div>
                    <div class="step-line"></div>
                    <div class="step-label">Dokumen</div>
                </div>
                <div class="step-item">
                    <div class="step-circle">5</div>
                    <div class="step-line"></div>
                    <div class="step-label">Biaya SPMB</div>
                </div>
                <div class="step-item">
                    <div class="step-circle">6</div>
                    <div class="step-label">Selesai</div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="step-card">
                    <div class="step-header text-center">
                        <div class="step-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <h4>Lengkapi Formulir Pendaftaran</h4>
                        <p class="mb-0">Silakan lengkapi formulir pendaftaran dengan data yang benar</p>
                    </div>
                    
                    <div class="step-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informasi Penting:</strong> Setelah berhasil mengisi formulir, Anda akan mendapatkan nomor pendaftaran otomatis yang dapat digunakan untuk melacak status pendaftaran Anda.
                        </div>

                        <form method="POST" action="{{ route('spmb.step3.post') }}">
                            @csrf
                            
                            @foreach($formSettings as $section => $fields)
                                @if(count($fields) > 0)
                                    <div class="section-header mb-4">
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
                            
                            <!-- Kejuruan Selection - Only show if there are available kejuruans -->
                            @if($kejuruans && $kejuruans->count() > 0)
                            <div class="section-header mb-4">
                                <h5 class="mb-0">
                                    <i class="fas fa-graduation-cap me-2"></i>Pilihan Kejuruan
                                </h5>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="kejuruan_id" class="form-label">Pilihan Kejuruan <span class="text-danger">*</span></label>
                                    <select class="form-control @error('kejuruan_id') is-invalid @enderror" 
                                            id="kejuruan_id" name="kejuruan_id" required>
                                        <option value="">Pilih Kejuruan</option>
                                        @foreach($kejuruans as $kejuruan)
                                            <option value="{{ $kejuruan->id }}" {{ old('kejuruan_id') == $kejuruan->id ? 'selected' : '' }}>
                                                {{ $kejuruan->nama_kejuruan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kejuruan_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @endif

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('spmb.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Simpan Formulir
                                </button>
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