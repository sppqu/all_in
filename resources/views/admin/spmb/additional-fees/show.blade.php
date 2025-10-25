<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Biaya Tambahan SPMB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #ffffff;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, #008060 0%, #00a86b 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
        
        .navbar-nav .nav-link {
            color: white !important;
        }
        
        .navbar-nav .nav-link:hover {
            color: #f8f9fa !important;
        }
        
        .main-content {
            padding: 2rem 0;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .btn {
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #008060 0%, #00a86b 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #006b4f 0%, #008060 100%);
            transform: translateY(-1px);
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
        
        .btn-outline-info {
            border: 2px solid #17a2b8;
            color: #17a2b8;
            background: transparent;
        }
        
        .btn-outline-info:hover {
            background: #17a2b8;
            border-color: #17a2b8;
            color: white;
        }
        
        .btn-outline-warning {
            border: 2px solid #ffc107;
            color: #ffc107;
            background: transparent;
        }
        
        .btn-outline-warning:hover {
            background: #ffc107;
            border-color: #ffc107;
            color: white;
        }
        
        .btn-outline-danger {
            border: 2px solid #dc3545;
            color: #dc3545;
            background: transparent;
        }
        
        .btn-outline-danger:hover {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background: white;
            color: #333;
            border: none;
            font-weight: 600;
            border-bottom: 2px solid #008060;
            padding: 1rem;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
        }
        
        .badge.bg-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        }
        
        .badge.bg-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
        }
        
        .badge.bg-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
        }
        
        .badge.bg-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        }
        
        .badge.bg-success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
        }
        
        .badge.bg-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #545b62 100%) !important;
        }
        
        .info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #008060;
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
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .pagination {
            justify-content: center;
        }
        
        .page-link {
            border: none;
            color: #008060;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .page-link:hover {
            background: #008060;
            color: white;
        }
        
        .page-item.active .page-link {
            background: #008060;
            border-color: #008060;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('manage.spmb.index') }}">
                <i class="fas fa-eye me-2"></i>Detail Biaya Tambahan SPMB
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('manage.spmb.index') }}">
                    <i class="fas fa-arrow-left me-1"></i><span class="text-white">Kembali ke Dashboard</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container main-content">
        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">{{ $additionalFee->name }}</h2>
                        <p class="text-muted mb-0">{{ $additionalFee->code }}</p>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('manage.spmb.additional-fees.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar
                        </a>
                        <a href="{{ route('manage.spmb.additional-fees.edit', $additionalFee->id) }}" class="btn btn-outline-warning me-2">
                            <i class="fas fa-edit me-1"></i>Edit Biaya
                        </a>
                        <form method="POST" action="{{ route('manage.spmb.additional-fees.toggle-status', $additionalFee->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-{{ $additionalFee->is_active ? 'secondary' : 'success' }}">
                                <i class="fas fa-{{ $additionalFee->is_active ? 'pause' : 'play' }} me-1"></i>
                                {{ $additionalFee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card p-4 text-center">
                    <i class="fas fa-tag fa-2x text-primary mb-3"></i>
                    <h4 class="mb-1">{{ $additionalFee->formatted_amount }}</h4>
                    <p class="text-muted mb-0">Jumlah Biaya</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card p-4 text-center">
                    <i class="fas fa-layer-group fa-2x text-info mb-3"></i>
                    <h4 class="mb-1">{!! $additionalFee->category_badge !!}</h4>
                    <p class="text-muted mb-0">Kategori</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card p-4 text-center">
                    <i class="fas fa-info-circle fa-2x text-warning mb-3"></i>
                    <h4 class="mb-1">{!! $additionalFee->type_badge !!}</h4>
                    <p class="text-muted mb-0">Jenis Biaya</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card p-4 text-center">
                    <i class="fas fa-power-off fa-2x {{ $additionalFee->is_active ? 'text-success' : 'text-secondary' }} mb-3"></i>
                    <h4 class="mb-1">
                        @if($additionalFee->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </h4>
                    <p class="text-muted mb-0">Status</p>
                </div>
            </div>
        </div>

        <!-- Detail Information -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Detail</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Informasi Dasar</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Nama Biaya:</strong></td>
                                        <td>{{ $additionalFee->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Kode:</strong></td>
                                        <td><code>{{ $additionalFee->code }}</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Kategori:</strong></td>
                                        <td>{!! $additionalFee->category_badge !!}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jenis:</strong></td>
                                        <td>{!! $additionalFee->type_badge !!}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jumlah:</strong></td>
                                        <td><strong class="text-primary">{{ $additionalFee->formatted_amount }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Urutan:</strong></td>
                                        <td>{{ $additionalFee->sort_order }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Status & Kondisi</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            @if($additionalFee->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dibuat:</strong></td>
                                        <td>{{ $additionalFee->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Diupdate:</strong></td>
                                        <td>{{ $additionalFee->updated_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    @if($additionalFee->conditions)
                                        <tr>
                                            <td><strong>Kondisi:</strong></td>
                                            <td>
                                                @foreach($additionalFee->conditions as $key => $value)
                                                    <span class="badge bg-info me-1">{{ ucfirst($key) }}: {{ $value }}</span>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                        
                        @if($additionalFee->description)
                            <hr>
                            <h6 class="text-primary">Deskripsi</h6>
                            <p class="text-muted">{{ $additionalFee->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-wave-square me-2"></i>Gelombang yang Menggunakan</h5>
                    </div>
                    <div class="card-body">
                        @if($waves->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Gelombang</th>
                                            <th>Biaya</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($waves as $wave)
                                            <tr>
                                                <td>
                                                    <strong>{{ $wave->name }}</strong>
                                                    <br><small class="text-muted">{{ $wave->description }}</small>
                                                </td>
                                                <td>
                                                    <strong class="text-primary">
                                                        Rp {{ number_format($wave->pivot->amount, 0, ',', '.') }}
                                                    </strong>
                                                </td>
                                                <td>
                                                    @if($wave->pivot->is_active)
                                                        <span class="badge bg-success">Aktif</span>
                                                    @else
                                                        <span class="badge bg-secondary">Nonaktif</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            @if($waves->hasPages())
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $waves->links() }}
                                </div>
                            @endif
                        @else
                            <div class="empty-state">
                                <i class="fas fa-wave-square"></i>
                                <h6>Belum Digunakan</h6>
                                <p class="text-muted">Biaya ini belum digunakan di gelombang manapun.</p>
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
