<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Form SPMB - Admin</title>
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
            padding: 12px 24px;
            font-weight: 600;
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

        .section-header {
            background: linear-gradient(135deg, rgb(8, 129, 45) 0%, #006d52 100%);
            color: white;
            padding: 20px 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .field-item {
            border: 2px solid rgba(0, 128, 96, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.95);
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .field-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-color: rgba(0, 128, 96, 0.3);
        }

        .field-item.inactive {
            opacity: 0.6;
            background: rgba(248, 249, 250, 0.8);
            border-color: rgba(0, 128, 96, 0.05);
        }

        .field-type-badge {
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 10px;
            font-weight: 600;
        }

        .sortable-handle {
            cursor: move;
            color: #008060;
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .sortable-handle:hover {
            color: #006d52;
            transform: scale(1.1);
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
                <i class="fas fa-cogs me-2"></i>Pengaturan Form SPMB
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('manage.spmb.index') }}">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="form-card">
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">
                                <i class="fas fa-cogs me-2"></i>Pengaturan Form SPMB
                            </h4>
                            <a href="{{ route('manage.spmb.form-settings.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Tambah Field
                            </a>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($fieldsBySection->count() > 0)
                            @foreach($fieldsBySection as $section => $fields)
                                <div class="section-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-folder me-2"></i>{{ ucfirst($section) }}
                                        <span class="badge bg-light text-dark ms-2">{{ $fields->count() }} field</span>
                                    </h5>
                                </div>

                                <div class="sortable-fields" data-section="{{ $section }}">
                                    @foreach($fields as $field)
                                        <div class="field-item {{ !$field->is_active ? 'inactive' : '' }}" data-id="{{ $field->id }}">
                                            <div class="row align-items-center">
                                                <div class="col-md-1">
                                                    <i class="fas fa-grip-vertical sortable-handle"></i>
                                                </div>
                                                <div class="col-md-2">
                                                    <strong>{{ $field->field_label }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $field->field_name }}</small>
                                                </div>
                                                <div class="col-md-2">
                                                    <span class="badge bg-info field-type-badge">{{ $field->field_type }}</span>
                                                    @if($field->is_required)
                                                        <span class="badge bg-warning field-type-badge">Required</span>
                                                    @endif
                                                </div>
                                                <div class="col-md-2">
                                                    @if($field->field_placeholder)
                                                        <small class="text-muted">Placeholder: {{ $field->field_placeholder }}</small>
                                                    @endif
                                                </div>
                                                <div class="col-md-2">
                                                    <span class="badge {{ $field->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $field->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                                    </span>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('manage.spmb.form-settings.show', $field->id) }}" 
                                                           class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('manage.spmb.form-settings.edit', $field->id) }}" 
                                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('manage.spmb.form-settings.toggle-status', $field->id) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm {{ $field->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" 
                                                                    title="{{ $field->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                                <i class="fas {{ $field->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('manage.spmb.form-settings.destroy', $field->id) }}" 
                                                              class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus field ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada field form</h5>
                                <p class="text-muted">Klik tombol "Tambah Field" untuk menambahkan field form baru.</p>
                                <a href="{{ route('manage.spmb.form-settings.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Tambah Field Pertama
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        // Initialize sortable for each section
        document.querySelectorAll('.sortable-fields').forEach(function(container) {
            new Sortable(container, {
                handle: '.sortable-handle',
                animation: 150,
                onEnd: function(evt) {
                    const fields = Array.from(container.children).map((item, index) => ({
                        id: item.dataset.id,
                        order: index
                    }));
                    
                    fetch('{{ route("manage.spmb.form-settings.update-order") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ fields: fields })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            const alert = document.createElement('div');
                            alert.className = 'alert alert-success alert-dismissible fade show';
                            alert.innerHTML = '<i class="fas fa-check-circle me-2"></i>Urutan field berhasil diperbarui.<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                            document.querySelector('.container').insertBefore(alert, document.querySelector('.form-card'));
                        }
                    });
                }
            });
        });
    </script>
    
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
