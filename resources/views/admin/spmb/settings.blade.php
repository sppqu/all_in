<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan SPMB - Admin</title>
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

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('manage.spmb.index') }}">
                <i class="fas fa-cogs me-2"></i>Pengaturan SPMB
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('manage.spmb.index') }}">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center p-4">
                    <div class="text-primary mb-2">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['total_pendaftar'] }}</h3>
                    <p class="text-muted mb-0">Total Pendaftar</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center p-4">
                    <div class="text-warning mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['pending'] }}</h3>
                    <p class="text-muted mb-0">Pending</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center p-4">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['diterima'] }}</h3>
                    <p class="text-muted mb-0">Diterima</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center p-4">
                    <div class="text-danger mb-2">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['ditolak'] }}</h3>
                    <p class="text-muted mb-0">Ditolak</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Pengaturan SPMB</h4>
                    <div class="btn-group">
                        @if(menuCan('menu.spmb.waves'))
                        <a href="{{ route('manage.spmb.waves.index') }}" class="btn btn-outline-success me-2">
                            <i class="fas fa-wave-square me-1"></i>Gelombang Pendaftaran
                        </a>
                        @endif
                        <a href="{{ route('manage.spmb.additional-fees.index') }}" class="btn btn-outline-info me-2">
                            <i class="fas fa-plus-circle me-1"></i>Biaya Tambahan
                        </a>
                        <a href="{{ route('manage.spmb.settings.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Tambah Pengaturan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Table -->
        <div class="settings-card">
            <div class="p-4">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tahun Pelajaran</th>
                                <th>Status Pendaftaran</th>
                                <th>Tanggal Buka</th>
                                <th>Tanggal Tutup</th>
                                <th>Biaya Pendaftaran</th>
                                <th>Biaya SPMB</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settings as $setting)
                            <tr>
                                <td>
                                    <strong>{{ $setting->tahun_pelajaran }}</strong>
                                    @if($setting->deskripsi)
                                        <br><small class="text-muted">{{ $setting->deskripsi }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($setting->pendaftaran_dibuka)
                                        <span class="status-badge bg-success text-white">Dibuka</span>
                                    @else
                                        <span class="status-badge bg-danger text-white">Ditutup</span>
                                    @endif
                                </td>
                                <td>
                                    @if($setting->tanggal_buka)
                                        {{ $setting->tanggal_buka->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($setting->tanggal_tutup)
                                        {{ $setting->tanggal_tutup->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($setting->biaya_pendaftaran, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($setting->biaya_spmb, 0, ',', '.') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('manage.spmb.settings.show', $setting->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('manage.spmb.settings.edit', $setting->id) }}" 
                                           class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('manage.spmb.settings.toggle-registration', $setting->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-{{ $setting->pendaftaran_dibuka ? 'danger' : 'success' }}">
                                                <i class="fas fa-{{ $setting->pendaftaran_dibuka ? 'lock' : 'unlock' }}"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteSetting({{ $setting->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada pengaturan SPMB</p>
                                    <a href="{{ route('manage.spmb.settings.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i>Tambah Pengaturan
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kejuruan Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="settings-card">
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="fas fa-graduation-cap me-2"></i>Data Kejuruan
                            </h5>
                            <a href="{{ route('manage.spmb.kejuruan.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-cogs me-1"></i>Kelola Kejuruan
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Kejuruan</th>
                                        <th>Kuota</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kejuruan as $k)
                                    <tr>
                                        <td><span class="badge bg-primary">{{ $k->kode_kejuruan }}</span></td>
                                        <td>{{ $k->nama_kejuruan }}</td>
                                        <td>{{ $k->kuota ?? 'Tidak Terbatas' }}</td>
                                        <td>
                                            @if($k->aktif)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3">
                                            <span class="text-muted">Belum ada data kejuruan</span>
                                            <br>
                                            <a href="{{ route('manage.spmb.kejuruan.create') }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="fas fa-plus me-1"></i>Tambah Kejuruan
                                            </a>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus pengaturan SPMB ini?</p>
                    <p class="text-danger"><strong>Data yang dihapus tidak dapat dikembalikan!</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteSetting(id) {
            document.getElementById('deleteForm').action = '{{ route("manage.spmb.settings.destroy", ":id") }}'.replace(':id', id);
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
