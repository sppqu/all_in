<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langkah 4 - SPMB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/spmb-steps.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('spmb.dashboard') }}">
                <i class="fas fa-graduation-cap me-2"></i>SPMB
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i>{{ session('spmb_name') }}
                </span>
                <form method="POST" action="{{ route('spmb.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
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
                <div class="step-item completed">
                    <div class="step-circle"><i class="fas fa-check"></i></div>
                    <div class="step-line"></div>
                    <div class="step-label">Formulir</div>
                </div>
                <div class="step-item active">
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
            <div class="col-md-10">
                <div class="step-card">
                    <div class="step-header text-center">
                        <div class="step-icon">
                            <i class="fas fa-upload"></i>
                        </div>
                        <h4>Upload Dokumen Pendaftaran</h4>
                        <p class="mb-0">Upload dokumen yang diperlukan untuk pendaftaran</p>
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

                        <!-- Existing Documents Section -->
                        @if($registration->documents->count() > 0)
                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="fas fa-check-circle me-2"></i>Dokumen yang Sudah Diupload
                            </h6>
                            <div class="row">
                                @foreach($registration->documents as $document)
                                <div class="col-md-6 mb-3">
                                    <div class="document-item existing-document">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1 text-success">
                                                    <i class="fas fa-check-circle me-1"></i>{{ $document->getDocumentTypeName() }}
                                                </h6>
                                                <small class="text-muted">{{ $document->file_name }}</small>
                                                <br>
                                                <small class="text-muted">{{ $document->getFileSizeHumanAttribute() }}</small>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('spmb.view-document', $document->id) }}" 
                                                   target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('spmb.download-document', $document->id) }}" 
                                                   class="btn btn-sm btn-outline-success" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        onclick="replaceDocument('{{ $document->document_type }}')" title="Ganti File">
                                                    <i class="fas fa-upload"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteDocument({{ $document->id }}, '{{ $document->getDocumentTypeName() }}')" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Catatan:</strong> Dokumen di atas sudah diupload sebelumnya (baik oleh Anda maupun admin). 
                                    Anda dapat menggantinya dengan mengupload file baru di bawah ini.
                                </div>
                            </div>
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
                                <li><strong>Upload dokumen bersifat opsional</strong> - Anda dapat melewatkan langkah ini dan mengupload dokumen nanti</li>
                            </ul>
                        </div>

                        <form method="POST" action="{{ route('spmb.step4.post') }}" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="document-item">
                                        <h6 class="mb-2">
                                            <i class="fas fa-home text-primary me-2"></i>Kartu Keluarga
                                        </h6>
                                        <div class="file-input-wrapper">
                                            <input type="file" class="file-input" id="kk" name="documents[kk]" 
                                                   accept=".pdf,.jpg,.jpeg,.png">
                                            <label for="kk" class="file-input-label">
                                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                                <div>Klik untuk upload Kartu Keluarga</div>
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
                                        <div class="file-input-wrapper">
                                            <input type="file" class="file-input" id="akte_lahir" name="documents[akte_lahir]" 
                                                   accept=".pdf,.jpg,.jpeg,.png">
                                            <label for="akte_lahir" class="file-input-label">
                                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                                <div>Klik untuk upload Akte Kelahiran</div>
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
                                        <div class="file-input-wrapper">
                                            <input type="file" class="file-input" id="ijazah" name="documents[ijazah]" 
                                                   accept=".pdf,.jpg,.jpeg,.png">
                                            <label for="ijazah" class="file-input-label">
                                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                                <div>Klik untuk upload Ijazah</div>
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
                                        <div class="file-input-wrapper">
                                            <input type="file" class="file-input" id="skl" name="documents[skl]" 
                                                   accept=".pdf,.jpg,.jpeg,.png">
                                            <label for="skl" class="file-input-label">
                                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                                <div>Klik untuk upload SKL</div>
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
                                        <div class="file-input-wrapper">
                                            <input type="file" class="file-input" id="foto" name="documents[foto]" 
                                                   accept=".jpg,.jpeg,.png">
                                            <label for="foto" class="file-input-label">
                                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                                <div>Klik untuk upload Foto</div>
                                                <small class="text-muted">JPG, JPEG, PNG (Max 2MB)</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="document-item">
                                        <h6 class="mb-2">
                                            <i class="fas fa-chart-line text-primary me-2"></i>Raport
                                        </h6>
                                        <div class="file-input-wrapper">
                                            <input type="file" class="file-input" id="raport" name="documents[raport]" 
                                                   accept=".pdf,.jpg,.jpeg,.png">
                                            <label for="raport" class="file-input-label">
                                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                                <div>Klik untuk upload Raport</div>
                                                <small class="text-muted">PDF, JPG, JPEG, PNG (Max 2MB)</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('spmb.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="action" value="skip" class="btn btn-warning">
                                        <i class="fas fa-forward me-1"></i>Lewati & Lanjutkan
                                    </button>
                                    <button type="submit" name="action" value="upload" class="btn btn-primary">
                                        <i class="fas fa-upload me-1"></i>Upload & Lanjutkan
                                    </button>
                                </div>
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
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', function() {
                const label = this.nextElementSibling;
                if (this.files.length > 0) {
                    label.innerHTML = `
                        <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                        <div>File dipilih: ${this.files[0].name}</div>
                        <small class="text-success">File berhasil dipilih</small>
                    `;
                    label.style.background = '#d4edda';
                    label.style.borderColor = '#28a745';
                }
            });
        });

        // Replace document function
        function replaceDocument(documentType) {
            const fileInput = document.getElementById(documentType);
            if (fileInput) {
                fileInput.click();
            }
        }

        // Delete document function
        function deleteDocument(documentId, documentName) {
            if (confirm(`Apakah Anda yakin ingin menghapus dokumen "${documentName}"?`)) {
                // Create a form to submit delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("spmb.delete-document", ":id") }}'.replace(':id', documentId);
                
                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                // Add method override for DELETE
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>

