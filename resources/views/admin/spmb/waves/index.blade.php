<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gelombang Pendaftaran SPMB - Admin</title>
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
            background: linear-gradient(135deg, #008060 0%, #006644 100%) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
        }

        .navbar-nav .nav-link {
            color: white !important;
            font-weight: 600;
            border: none;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .navbar-nav .btn-link {
            color: white !important;
            font-weight: 600;
            border: none;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .navbar-nav .btn-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            border-left: 4px solid #008060;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
        }

        .settings-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: none;
            overflow: hidden;
        }

        .btn {
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #008060 0%, #006644 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #006644 0%, #004d33 100%);
            transform: translateY(-2px);
        }

        .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            background: transparent;
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            color: white;
        }

        .btn-outline-success {
            border: 2px solid #008060;
            color: #008060;
            background: transparent;
        }

        .btn-outline-success:hover {
            background: #008060;
            border-color: #008060;
            color: white;
        }

        .btn-outline-secondary {
            border: 2px solid #6c757d;
            color: #6c757d;
            background: transparent;
        }

        .btn-outline-secondary:hover {
            background: #6c757d;
            border-color: #6c757d;
            color: white;
        }

        .table thead th {
            background: white;
            color: #333;
            border: none;
            font-weight: 600;
            border-bottom: 2px solid #008060;
        }

        .badge {
            font-size: 0.75em;
        }

        .card-header {
            background: linear-gradient(135deg, #008060 0%, #006644 100%);
            color: white;
            border: none;
        }

        .card-header h4 {
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('manage.spmb.index') }}">
                <i class="fas fa-wave-square me-2"></i>Gelombang Pendaftaran SPMB
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('manage.spmb.index') }}">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-end">
                    <div class="btn-group">
                        <a href="{{ route('manage.spmb.settings') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i>Kembali ke Pengaturan
                        </a>
                        <a href="{{ route('manage.spmb.waves.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Tambah Gelombang
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="settings-card">
            <div class="p-4">
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

                    @if($waves->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Gelombang</th>
                                        <th>Periode</th>
                                        <th>Biaya Pendaftaran</th>
                                        <th>Biaya SPMB</th>
                                        <th>Kuota</th>
                                        <th>Pendaftar</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($waves as $index => $wave)
                                    <tr>
                                        <td>{{ $waves->firstItem() + $index }}</td>
                                        <td>
                                            <div>
                                                <strong>{{ $wave->name }}</strong>
                                                @if($wave->description)
                                                    <br><small class="text-muted">{{ Str::limit($wave->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <small class="text-muted">Mulai:</small><br>
                                                <strong>{{ $wave->start_date->format('d/m/Y') }}</strong>
                                                <br>
                                                <small class="text-muted">Berakhir:</small><br>
                                                <strong>{{ $wave->end_date->format('d/m/Y') }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $wave->formatted_registration_fee }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ $wave->formatted_spmb_fee }}</span>
                                        </td>
                                        <td>
                                            @if($wave->quota)
                                                <span class="badge bg-warning">{{ $wave->quota }}</span>
                                            @else
                                                <span class="text-muted">Tidak Terbatas</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $wave->registrations_count }}</span>
                                            @if($wave->quota && $wave->registrations_count >= $wave->quota)
                                                <br><small class="text-danger">Kuota Penuh</small>
                                            @endif
                                        </td>
                                        <td>{!! $wave->status_badge !!}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('manage.spmb.waves.show', $wave->id) }}" 
                                                   class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('manage.spmb.waves.edit', $wave->id) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('manage.spmb.waves.toggle-status', $wave->id) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $wave->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                            title="{{ $wave->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                            onclick="return confirm('{{ $wave->is_active ? 'Nonaktifkan' : 'Aktifkan' }} gelombang {{ $wave->name }}?')">
                                                        <i class="fas fa-{{ $wave->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                @if($wave->registrations_count == 0)
                                                <form method="POST" action="{{ route('manage.spmb.waves.destroy', $wave->id) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"
                                                            onclick="return confirm('Hapus gelombang {{ $wave->name }}?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $waves->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-wave-square fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada gelombang pendaftaran</h5>
                            <p class="text-muted">Silakan tambahkan gelombang pendaftaran pertama Anda</p>
                            <a href="{{ route('manage.spmb.waves.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Tambah Gelombang
                            </a>
                        </div>
                    @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
