<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Gelombang Pendaftaran - Admin</title>
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

        .fs-6 {
            font-size: 1rem !important;
        }

        .border-end {
            border-right: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('manage.spmb.index') }}">
                <i class="fas fa-wave-square me-2"></i>Detail Gelombang Pendaftaran
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
                        <a href="{{ route('manage.spmb.waves.edit', $wave->id) }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="{{ route('manage.spmb.settings') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali ke Pengaturan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="settings-card">
            <div class="p-4">
                <!-- Wave Information -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h5 class="mb-3">{{ $wave->name }}</h5>
                        @if($wave->description)
                            <p class="text-muted mb-3">{{ $wave->description }}</p>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Tanggal Mulai:</strong><br>
                                    <span class="text-primary">{{ $wave->start_date->format('d F Y') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Tanggal Berakhir:</strong><br>
                                    <span class="text-primary">{{ $wave->end_date->format('d F Y') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Biaya Pendaftaran:</strong><br>
                                    <span class="badge bg-info fs-6">{{ $wave->formatted_registration_fee }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Biaya SPMB:</strong><br>
                                    <span class="badge bg-success fs-6">{{ $wave->formatted_spmb_fee }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Kuota:</strong><br>
                                    @if($wave->quota)
                                        <span class="badge bg-warning fs-6">{{ $wave->quota }}</span>
                                    @else
                                        <span class="text-muted">Tidak Terbatas</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Status:</strong><br>
                                    {!! $wave->status_badge !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title">Ringkasan Pendaftaran</h6>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <h4 class="text-primary mb-1">{{ $stats['total_registrations'] }}</h4>
                                            <small class="text-muted">Total Pendaftar</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success mb-1">{{ $stats['diterima'] }}</h4>
                                        <small class="text-muted">Diterima</small>
                                    </div>
                                </div>
                                <hr>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <h6 class="text-warning mb-1">{{ $stats['pending'] }}</h6>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                    <div class="col-4">
                                        <h6 class="text-danger mb-1">{{ $stats['ditolak'] }}</h6>
                                        <small class="text-muted">Ditolak</small>
                                    </div>
                                    <div class="col-4">
                                        <h6 class="text-info mb-1">{{ $stats['paid_registration'] }}</h6>
                                        <small class="text-muted">Lunas Pendaftaran</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registrations Table -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-users me-2"></i>Data Pendaftaran ({{ $wave->registrations->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($wave->registrations->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>No. Pendaftaran</th>
                                            <th>Nama</th>
                                            <th>Telepon</th>
                                            <th>Kejuruan</th>
                                            <th>Status Pendaftaran</th>
                                            <th>Biaya Pendaftaran</th>
                                            <th>Biaya SPMB</th>
                                            <th>Tanggal Daftar</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($wave->registrations as $index => $registration)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if($registration->nomor_pendaftaran)
                                                    <span class="badge bg-info">{{ $registration->nomor_pendaftaran }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $registration->name }}</strong>
                                            </td>
                                            <td>{{ $registration->phone }}</td>
                                            <td>
                                                @if($registration->kejuruan)
                                                    {{ $registration->kejuruan->name }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{!! $registration->getStatusPendaftaranBadge() !!}</td>
                                            <td>
                                                @if($registration->registration_fee_paid)
                                                    <span class="badge bg-success">Lunas</span>
                                                @else
                                                    <span class="badge bg-warning">Belum</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($registration->spmb_fee_paid)
                                                    <span class="badge bg-success">Lunas</span>
                                                @else
                                                    <span class="badge bg-warning">Belum</span>
                                                @endif
                                            </td>
                                            <td>{{ $registration->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('manage.spmb.show', $registration->id) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada pendaftaran untuk gelombang ini</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>