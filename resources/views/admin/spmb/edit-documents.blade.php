<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Upload Dokumen - SPMB Admin</title>
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

        .step-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
            animation: slideInUp 1s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .step-header {
            background: linear-gradient(135deg, #008060, #00a86b);
            color: white;
            padding: 30px;
            border-radius: 25px 25px 0 0;
            position: relative;
            overflow: hidden;
        }

        .step-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .step-body {
            padding: 30px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #008060, #00a86b);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            padding: 12px 30px;
            font-size: 16px;
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
            background: linear-gradient(135deg, #006b4f, #008060);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 128, 96, 0.3);
        }

        .document-item {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 2px solid #008060;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: block;
            padding: 20px;
            background: #e9ecef;
            border: 2px dashed #6c757d;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            background: #dee2e6;
            border-color: #008060;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 128, 96, 0.2);
        }

        .file-input-label:active {
            transform: translateY(0);
        }

        .file-selected {
            background: #d4edda !important;
            border-color: #28a745 !important;
            border-style: solid !important;
        }

        .file-selected:hover {
            background: #c3e6cb !important;
            border-color: #1e7e34 !important;
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
        }

        .existing-document {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 2px solid #28a745;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('manage.spmb.index') }}">
                <i class="fas fa-upload me-2"></i>Edit Dokumen SPMB
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
            <div class="col-md-10">
                <div class="step-card">
                    <div class="step-header text-center">
                        <i class="fas fa-upload fa-3x mb-3"></i>
                        <h3 class="mb-0">Edit Upload Dokumen</h3>
                        <p class="mb-0">Upload dokumen untuk pendaftaran #{{ $registration->id }}</p>
                        <small class="mt-2 d-block">Nama: <strong>{{ $registration->name }}</strong> | No. HP: <strong>{{ $registration->phone }}</strong></small>
                    </div>
                    <div class="step-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>Informasi Upload
                            </h6>
                            <ul class="mb-0">
                                <li>Format file yang diperbolehkan: PDF, JPG, JPEG, PNG</li>
                                <li>Ukuran maksimal file: 2MB</li>
                                <li>Pastikan dokumen jelas dan dapat dibaca</li>
                                <li>Semua dokumen wajib diupload</li>
                            </ul>
                        </div>

                        <form method="POST" action="{{ route('manage.spmb.update-documents', $registration->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="document-item">
                                        <h6 class="mb-2">
                                            <i class="fas fa-home text-primary me-2"></i>Kartu Keluarga
                                        </h6>
                                        @php
                                            $kk = $registration->documents->where('document_type', 'kk')->first();
                                        @endphp
                                        @if($kk)
                                            <div class="existing-document mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1 text-success">
                                                            <i class="fas fa-check-circle me-1"></i>Sudah diupload
                                                        </h6>
                                                        <small class="text-muted">{{ $kk->file_name }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $kk->getFileSizeHumanAttribute() }}</small>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('manage.spmb.view-document', $kk->id) }}" 
                                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('manage.spmb.download-document', $kk->id) }}" 
                                                           class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="file-input-wrapper">
                                            <input type="file" class="file-input" id="kk" name="documents[kk]" 
                                                   accept=".pdf,.jpg,.jpeg,.png">
                                            <label for="kk" class="file-input-label">
                                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                                <div>Klik untuk {{ $kk ? 'ganti' : 'upload' }} Kartu Keluarga</div>
                                                <small class="text-muted">PDF, JPG, JPEG, PNG (Max 2MB)</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="document-item">
                                        <h6 class="mb-2">
                                            <i class="fas fa-birthday-cake text-primary me-2"></i>Akte Kelahiran
                                        </h6>
                                        @php
                                            $akteLahir = $registration->documents->where('document_type', 'akte_lahir')->first();
                                        @endphp
                                        @if($akteLahir)
                                            <div class="existing-document mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1 text-success">
                                                            <i class="fas fa-check-circle me-1"></i>Sudah diupload
                                                        </h6>
                                                        <small class="text-muted">{{ $akteLahir->file_name }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $akteLahir->getFileSizeHumanAttribute() }}</small>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('manage.spmb.view-document', $akteLahir->id) }}" 
                                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('manage.spmb.download-document', $akteLahir->id) }}" 
                                                           class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="file-input-wrapper">
                                            <input type="file" class="file-input" id="akte_lahir" name="documents[akte_lahir]" 
                                                   accept=".pdf,.jpg,.jpeg,.png">
                                            <label for="akte_lahir" class="file-input-label">
                                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                                <div>Klik untuk {{ $akteLahir ? 'ganti' : 'upload' }} Akte Kelahiran</div>
                                                <small class="text-muted">PDF, JPG, JPEG, PNG (Max 2MB)</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="document-item">
                                        <h6 class="mb-2">
                                            <i class="fas fa-graduation-cap text-primary me-2"></i>Ijazah
                                        </h6>
                                        @php
                                            $ijazah = $registration->documents->where('document_type', 'ijazah')->first();
                                        @endphp
                                        @if($ijazah)
                                            <div class="existing-document mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1 text-success">
                                                            <i class="fas fa-check-circle me-1"></i>Sudah diupload
                                                        </h6>
                                                        <small class="text-muted">{{ $ijazah->file_name }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $ijazah->getFileSizeHumanAttribute() }}</small>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('manage.spmb.view-document', $ijazah->id) }}" 
                                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('manage.spmb.download-document', $ijazah->id) }}" 
                                                           class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="file-input-wrapper">
                                            <input type="file" class="file-input" id="ijazah" name="documents[ijazah]" 
                                                   accept=".pdf,.jpg,.jpeg,.png">
                                            <label for="ijazah" class="file-input-label">
                                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                                <div>Klik untuk {{ $ijazah ? 'ganti' : 'upload' }} Ijazah</div>
                                                <small class="text-muted">PDF, JPG, JPEG, PNG (Max 2MB)</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="document-item">
                                        <h6 class="mb-2">
                                            <i class="fas fa-file-alt text-primary me-2"></i>Surat Keterangan Lulus
                                        </h6>
                                        @php
                                            $skl = $registration->documents->where('document_type', 'skl')->first();
                                        @endphp
                                        @if($skl)
                                            <div class="existing-document mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1 text-success">
                                                            <i class="fas fa-check-circle me-1"></i>Sudah diupload
                                                        </h6>
                                                        <small class="text-muted">{{ $skl->file_name }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $skl->getFileSizeHumanAttribute() }}</small>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('manage.spmb.view-document', $skl->id) }}" 
                                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('manage.spmb.download-document', $skl->id) }}" 
                                                           class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="file-input-wrapper">
                                            <input type="file" class="file-input" id="skl" name="documents[skl]" 
                                                   accept=".pdf,.jpg,.jpeg,.png">
                                            <label for="skl" class="file-input-label">
                                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                                <div>Klik untuk {{ $skl ? 'ganti' : 'upload' }} SKL</div>
                                                <small class="text-muted">PDF, JPG, JPEG, PNG (Max 2MB)</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="document-item">
                                        <h6 class="mb-2">
                                            <i class="fas fa-camera text-primary me-2"></i>Foto
                                        </h6>
                                        @php
                                            $foto = $registration->documents->where('document_type', 'foto')->first();
                                        @endphp
                                        @if($foto)
                                            <div class="existing-document mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1 text-success">
                                                            <i class="fas fa-check-circle me-1"></i>Sudah diupload
                                                        </h6>
                                                        <small class="text-muted">{{ $foto->file_name }}</small>
                                                        <br>
                                                        <small class="text-muted">{{ $foto->getFileSizeHumanAttribute() }}</small>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('manage.spmb.view-document', $foto->id) }}" 
                                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('manage.spmb.download-document', $foto->id) }}" 
                                                           class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="file-input-wrapper">
                                            <input type="file" class="file-input" id="foto" name="documents[foto]" 
                                                   accept=".jpg,.jpeg,.png">
                                            <label for="foto" class="file-input-label">
                                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                                <div>Klik untuk {{ $foto ? 'ganti' : 'upload' }} Foto</div>
                                                <small class="text-muted">JPG, JPEG, PNG (Max 2MB)</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>Simpan Dokumen
                                </button>
                                <a href="{{ route('manage.spmb.show', $registration->id) }}" class="btn btn-outline-secondary ms-3">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Detail
                                </a>
                            </div>
                            
                            <!-- Loading indicator -->
                            <div id="loadingIndicator" class="text-center mt-3" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Menyimpan dokumen...</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File input change handler
        document.addEventListener('DOMContentLoaded', function() {
            const fileInputs = document.querySelectorAll('.file-input');
            
            fileInputs.forEach(function(input) {
                input.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    const label = document.querySelector(`label[for="${e.target.id}"]`);
                    
                    if (file) {
                        // Update label to show selected file
                        const fileName = file.name;
                        const fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                        const fileType = file.type;
                        
                        // Check if it's an image
                        const isImage = fileType.startsWith('image/');
                        let previewHtml = '';
                        
                        if (isImage) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const previewImg = label.querySelector('.file-preview');
                                if (previewImg) {
                                    previewImg.src = e.target.result;
                                }
                            };
                            reader.readAsDataURL(file);
                            previewHtml = '<img class="file-preview" style="max-width: 100px; max-height: 100px; border-radius: 8px; margin-bottom: 10px;" />';
                        }
                        
                        label.innerHTML = `
                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                            <div class="text-success fw-bold">File Dipilih!</div>
                            ${previewHtml}
                            <div class="text-muted small">${fileName}</div>
                            <div class="text-muted small">${fileSize}</div>
                            <small class="text-muted">Klik untuk ganti file</small>
                        `;
                        
                        // Add success styling class
                        label.classList.add('file-selected');
                        
                        // Add pulse animation
                        label.style.animation = 'pulse 0.5s ease-in-out';
                        setTimeout(() => {
                            label.style.animation = '';
                        }, 500);
                    }
                });
            });
            
            // Add pulse animation CSS
            const style = document.createElement('style');
            style.textContent = `
                @keyframes pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.05); }
                    100% { transform: scale(1); }
                }
                
                .file-selected {
                    background: #d4edda !important;
                    border-color: #28a745 !important;
                    border-style: solid !important;
                }
            `;
            document.head.appendChild(style);
            
            // Form submission handler
            const form = document.querySelector('form');
            const submitBtn = document.getElementById('submitBtn');
            const loadingIndicator = document.getElementById('loadingIndicator');
            
            form.addEventListener('submit', function(e) {
                // Check if any files are selected
                const fileInputs = form.querySelectorAll('input[type="file"]');
                let hasFiles = false;
                
                fileInputs.forEach(input => {
                    if (input.files.length > 0) {
                        hasFiles = true;
                    }
                });
                
                if (hasFiles) {
                    // Show loading state
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
                    loadingIndicator.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>
