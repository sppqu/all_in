<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran SPMB - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #008060 0%, #006644 100%);
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #008060 0%, #006644 100%);
            border: none;
            border-radius: 10px;
        }
        .btn-outline-primary {
            border: 2px solid #008060;
            color: #008060;
            border-radius: 10px;
        }
        .btn-outline-primary:hover {
            background: #008060;
            border-color: #008060;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('manage.spmb.index') }}">
                <i class="fas fa-graduation-cap me-2"></i>SPMB Admin
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
                    <div class="stats-icon text-primary">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['total'] }}</h3>
                    <p class="text-muted mb-0">Total Pembayaran</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center p-4">
                    <div class="stats-icon text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['paid'] }}</h3>
                    <p class="text-muted mb-0">Lunas</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center p-4">
                    <div class="stats-icon text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['pending'] }}</h3>
                    <p class="text-muted mb-0">Pending</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center p-4">
                    <div class="stats-icon text-info">
                        <i class="fas fa-bank"></i>
                    </div>
                    <h3 class="mb-1">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</h3>
                    <p class="text-muted mb-0">Total Pemasukan</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="table-card">
                    <div class="p-4">
                        <h5 class="mb-3">Filter Data</h5>
                        <form method="GET" action="{{ route('manage.spmb.payments') }}">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" name="start_date" class="form-control" 
                                           value="{{ request('start_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tanggal Akhir</label>
                                    <input type="date" name="end_date" class="form-control" 
                                           value="{{ request('end_date') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Jenis</label>
                                    <select name="type" class="form-select">
                                        <option value="">Semua Jenis</option>
                                        @foreach($filterOptions['types'] as $type)
                                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                                {{ $type == 'registration_fee' ? 'Biaya Pendaftaran' : 'Biaya SPMB' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Metode</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="">Semua Metode</option>
                                        @foreach($filterOptions['payment_methods'] as $method)
                                            <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $method)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">Semua Status</option>
                                        @foreach($filterOptions['statuses'] as $status)
                                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-filter me-1"></i>Filter
                                    </button>
                                    <a href="{{ route('manage.spmb.payments') }}" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-times me-1"></i>Reset
                                    </a>
                                    <a href="{{ route('manage.spmb.export-payments-pdf') }}?{{ http_build_query(request()->query()) }}" 
                                       class="btn btn-danger">
                                        <i class="fas fa-file-pdf me-1"></i>Unduh PDF
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-card">
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">Data Pembayaran SPMB</h4>
                            <small class="text-muted">Total: {{ $payments->total() }} data</small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pendaftar</th>
                                        <th>No. Pendaftaran</th>
                                        <th>Jenis</th>
                                        <th>Jumlah</th>
                                        <th>Metode</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Referensi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->id }}</td>
                                        <td>
                                            <div>
                                                <strong>{{ $payment->registration->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $payment->registration->phone }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($payment->registration->nomor_pendaftaran)
                                                <span class="badge bg-info">{{ $payment->registration->nomor_pendaftaran }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $payment->getTypeName() }}</td>
                                        <td>{{ $payment->getAmountFormattedAttribute() }}</td>
                                        <td>{{ $payment->getPaymentMethodName() }}</td>
                                        <td>
                                            @if($payment->status == 'paid')
                                                <span class="status-badge bg-success text-white">Lunas</span>
                                            @elseif($payment->status == 'skipped')
                                                <span class="status-badge bg-info text-white">Di-skip</span>
                                            @elseif($payment->status == 'pending')
                                                <span class="status-badge bg-warning text-white">Pending</span>
                                            @elseif($payment->status == 'expired')
                                                <span class="status-badge bg-danger text-white">Kadaluarsa</span>
                                            @elseif($payment->status == 'failed')
                                                <span class="status-badge bg-danger text-white">Gagal</span>
                                            @else
                                                <span class="status-badge bg-secondary text-white">{{ $payment->getStatusName() }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <small class="text-muted">{{ $payment->payment_reference }}</small>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Belum ada data pembayaran</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($payments->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $payments->links() }}
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



