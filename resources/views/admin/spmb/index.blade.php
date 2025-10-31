<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPMB Admin - Dashboard</title>
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

        .navbar-nav .btn-link {
            color: #008060 !important;
            font-weight: 600;
            border: none;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .navbar-nav .btn-link:hover {
            color: #006644 !important;
            background: rgba(0, 128, 96, 0.1);
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

        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #008060;
        }

        .table-card {
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

        .btn-outline-primary {
            border: 2px solid #008060;
            color: #008060;
            background: transparent;
            border-radius: 15px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: #008060;
            border-color: #008060;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 128, 96, 0.4);
        }

        /* Standardize all button styles */
        .btn {
            border-radius: 15px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            border: none;
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #1e7e34 0%, #155724 100%);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
        }

        .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border: none;
            color: white;
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #138496 0%, #0f6674 100%);
            box-shadow: 0 10px 25px rgba(23, 162, 184, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            border: none;
            color: #212529;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #e0a800 0%, #d39e00 100%);
            box-shadow: 0 10px 25px rgba(255, 193, 7, 0.4);
        }

        .btn-primary {
            background: linear-gradient(135deg, #008060 0%, #006d52 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #006d52 0%, #004d3a 100%);
            box-shadow: 0 10px 25px rgba(0, 128, 96, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
            box-shadow: 0 10px 25px rgba(220, 53, 69, 0.4);
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

        .table {
            border-radius: 15px;
            overflow: hidden;
        }

        .table thead th {
            background: white;
            color: #333;
            border: none;
            font-weight: 600;
            border-bottom: 2px solid #008060;
        }

        .table tbody tr:hover {
            background: rgba(0, 128, 96, 0.05);
        }

        /* Action Buttons Styling */
        .action-buttons {
            display: flex;
            gap: 2px;
            align-items: center;
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(0, 128, 96, 0.1);
            border-radius: 15px;
            padding: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .action-buttons:hover {
            border-color: rgba(0, 128, 96, 0.3);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .action-btn {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* Bulk Actions Styling */
        .bulk-actions {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 15px;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }

        .bulk-actions.show {
            background: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.5);
        }

        .selected-count {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .dropdown-menu {
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border: none;
        }

        .dropdown-item {
            border-radius: 8px;
            margin: 2px 8px;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .dropdown-item.text-danger:hover {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }

        .dropdown-item.text-success:hover {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
        }

        .dropdown-item.text-warning:hover {
            background: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }

        /* Checkbox styling */
        .form-check-input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #008060;
            border-color: #008060;
        }

        /* Mobile Responsive Menu */
        .mobile-menu-container {
            display: none;
        }

        .mobile-menu-container.show {
            display: block;
        }

        .mobile-menu-toggle {
            display: none;
            background: #008060;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 15px;
        }

        .mobile-menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }

        .mobile-menu-item {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 10px;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .mobile-menu-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            text-decoration: none;
            color: #333;
        }

        .mobile-menu-item i {
            font-size: 24px;
            margin-bottom: 8px;
            display: block;
        }

        .mobile-menu-item .btn-text {
            font-size: 12px;
            font-weight: 600;
            line-height: 1.2;
        }

        /* Button colors for mobile */
        .mobile-menu-item.btn-success { border-color: #28a745; color: #28a745; }
        .mobile-menu-item.btn-info { border-color: #17a2b8; color: #17a2b8; }
        .mobile-menu-item.btn-warning { border-color: #ffc107; color: #e0a800; }
        .mobile-menu-item.btn-primary { border-color: #007bff; color: #007bff; }
        .mobile-menu-item.btn-danger { border-color: #dc3545; color: #dc3545; }

        .mobile-menu-item.btn-success:hover { background: #28a745; color: white; }
        .mobile-menu-item.btn-info:hover { background: #17a2b8; color: white; }
        .mobile-menu-item.btn-warning:hover { background: #ffc107; color: #333; }
        .mobile-menu-item.btn-primary:hover { background: #007bff; color: white; }
        .mobile-menu-item.btn-danger:hover { background: #dc3545; color: white; }

        /* Desktop menu - hide on mobile */
        .desktop-menu {
            display: flex;
        }

        /* Mobile responsive breakpoints */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block !important;
            }
            
            .mobile-menu-container {
                display: block;
            }
            
            .desktop-menu {
                display: none !important;
            }
            
            .mobile-menu-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 769px) {
            .mobile-menu-toggle {
                display: none !important;
            }
            
            .mobile-menu-container {
                display: none !important;
            }
            
            .desktop-menu {
                display: flex !important;
            }
        }

        @media (max-width: 480px) {
            .mobile-menu-grid {
                grid-template-columns: 1fr;
            }
            
            .mobile-menu-item {
                padding: 20px 15px;
            }
            
            .mobile-menu-item i {
                font-size: 28px;
            }
            
            .mobile-menu-item .btn-text {
                font-size: 14px;
            }
        }

    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('manage.admin.dashboard') }}">
                <i class="fas fa-graduation-cap me-2"></i>Dashboard SPMB Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('manage.admin.dashboard') }}">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="nav-link btn btn-link text-decoration-none" 
                            onclick="return confirm('Apakah Anda yakin ingin logout?')">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center p-4">
                    <div class="stats-icon text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['total'] }}</h3>
                    <p class="text-muted mb-0">Total Pendaftar</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center p-4">
                    <div class="stats-icon text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['completed'] }}</h3>
                    <p class="text-muted mb-0">Diterima</p>
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
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['paid_registration'] }}</h3>
                    <p class="text-muted mb-0">Bayar Pendaftaran</p>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
            <i class="fas fa-bars me-2"></i>Menu Admin SPMB
        </button>

        <!-- Mobile Menu Container -->
        <div class="mobile-menu-container" id="mobileMenu">
            <div class="mobile-menu-grid">
                <a href="{{ route('manage.spmb.create') }}" class="mobile-menu-item btn-success d-none">
                    <i class="fas fa-user-plus"></i>
                    <div class="btn-text">Tambah Pendaftar</div>
                </a>
                <a href="{{ route('manage.spmb.settings') }}" class="mobile-menu-item btn-info">
                    <i class="fas fa-cogs"></i>
                    <div class="btn-text">Pengaturan</div>
                </a>
                <a href="{{ route('manage.spmb.transfer-to-students') }}" class="mobile-menu-item btn-warning">
                    <i class="fas fa-exchange-alt"></i>
                    <div class="btn-text">Transfer ke Students</div>
                </a>
                <a href="{{ route('manage.spmb.form-settings.index') }}" class="mobile-menu-item btn-warning">
                    <i class="fas fa-edit"></i>
                    <div class="btn-text">Pengaturan Form</div>
                </a>
                <a href="{{ route('manage.spmb.payments') }}" class="mobile-menu-item btn-primary">
                    <i class="fas fa-credit-card"></i>
                    <div class="btn-text">Lihat Pembayaran</div>
                </a>
                <a href="{{ route('manage.spmb.export-excel', request()->query()) }}" class="mobile-menu-item btn-primary">
                    <i class="fas fa-file-excel"></i>
                    <div class="btn-text">Export Excel</div>
                </a>
                <a href="{{ route('manage.spmb.export-pdf', request()->query()) }}" class="mobile-menu-item btn-danger">
                    <i class="fas fa-file-pdf"></i>
                    <div class="btn-text">Export PDF</div>
                </a>
            </div>
        </div>

        <!-- Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-end align-items-center mb-3 desktop-menu">
                    <div class="d-flex gap-2 align-items-center">
                        <!-- Bulk Actions -->
                        <div class="bulk-actions d-none">
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-danger dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-trash me-1"></i>Bulk Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item text-danger" href="#" onclick="bulkDelete()">
                                        <i class="fas fa-trash me-2"></i>Hapus Terpilih
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-success" href="#" onclick="bulkUpdateStatus('diterima')">
                                        <i class="fas fa-check me-2"></i>Setuju Terpilih
                                    </a></li>
                                    <li><a class="dropdown-item text-warning" href="#" onclick="bulkUpdateStatus('ditolak')">
                                        <i class="fas fa-times me-2"></i>Tolak Terpilih
                                    </a></li>
                                </ul>
                            </div>
                            <span class="ms-2 text-muted selected-count">0 dipilih</span>
                        </div>
                        <!-- Action Buttons -->
                        <a href="{{ route('manage.spmb.create') }}" class="btn btn-success d-none">
<i class="fas fa-user-plus me-1"></i>Tambah Pendaftar
                        </a>
                        <a href="{{ route('manage.spmb.settings') }}" class="btn btn-info">
                            <i class="fas fa-cogs me-1"></i>Pengaturan
                        </a>
                        <a href="{{ route('manage.spmb.transfer-to-students') }}" class="btn btn-warning">
                            <i class="fas fa-exchange-alt me-1"></i>Transfer ke Students
                        </a>
                        <a href="{{ route('manage.spmb.form-settings.index') }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Pengaturan Form
                        </a>
                        <a href="{{ route('manage.spmb.payments') }}" class="btn btn-primary">
                            <i class="fas fa-credit-card me-1"></i>Lihat Pembayaran
                        </a>
                        <a href="{{ route('manage.spmb.export-excel', request()->query()) }}" class="btn btn-primary">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                        <a href="{{ route('manage.spmb.export-pdf', request()->query()) }}" class="btn btn-danger">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                    </div>
                </div>
                
                <!-- Title Section -->
                <div class="mb-3">
                    <h4 class="mb-0 text-center">Data Pendaftaran SPMB</h4>
                </div>
                
                <!-- Filter Form -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <select name="status_pendaftaran" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status_pendaftaran') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="diterima" {{ request('status_pendaftaran') == 'diterima' ? 'selected' : '' }}>Diterima</option>
                            <option value="ditolak" {{ request('status_pendaftaran') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="kejuruan_id" class="form-select">
                            <option value="">Semua Kejuruan</option>
                            @foreach($kejuruan as $k)
                                <option value="{{ $k->id }}" {{ request('kejuruan_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kejuruan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama, phone, atau nomor pendaftaran..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Registrations Table -->
        <div class="table-card">
            <div class="p-4">
                <div class="table-responsive">
                    <form id="bulkForm" method="POST" action="{{ route('manage.spmb.bulk-action') }}">
                        @csrf
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                <th>No.</th>
                                <th>No. Pendaftaran</th>
                                <th>Nama</th>
                                <th>No. HP</th>
                                <th>Kejuruan</th>
                                <th>Status Pendaftaran</th>
                                <th>Langkah</th>
                                <th>Biaya Pendaftaran</th>
                                <th>Biaya SPMB</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $registration)
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_ids[]" value="{{ $registration->id }}" class="form-check-input row-checkbox">
                                </td>
                                <td>{{ $registration->id }}</td>
                                <td>
                                    @if($registration->nomor_pendaftaran)
                                        <span class="badge bg-info">{{ $registration->nomor_pendaftaran }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $registration->name }}</td>
                                <td>{{ $registration->phone }}</td>
                                <td>
                                    @if($registration->kejuruan)
                                        <span class="badge bg-secondary">{{ $registration->kejuruan->nama_kejuruan }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {!! $registration->getStatusPendaftaranBadge() !!}
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $registration->step }}</span>
                                </td>
                                <td>
                                    @if($registration->registration_fee_paid)
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i> Lunas
                                        </span>
                                    @else
                                        <span class="text-danger">
                                            <i class="fas fa-times-circle"></i> Belum
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($registration->spmb_fee_paid)
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i> Lunas
                                        </span>
                                    @else
                                        <span class="text-danger">
                                            <i class="fas fa-times-circle"></i> Belum
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $registration->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('manage.spmb.show', $registration->id) }}" 
                                           class="action-btn btn-outline-primary" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('manage.spmb.print-form', $registration->id) }}" 
                                           class="action-btn btn-outline-info" title="Cetak Formulir" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <button type="button" class="action-btn btn-outline-danger" 
                                                onclick="deleteRegistration({{ $registration->id }})" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data pendaftaran</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </form>
                </div>

                <!-- Pagination -->
                @if($registrations->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $registrations->links() }}
                </div>
                @endif
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
                    <p>Apakah Anda yakin ingin menghapus data pendaftaran ini?</p>
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
        // Mobile Menu Toggle Function
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            
            if (mobileMenu.classList.contains('show')) {
                mobileMenu.classList.remove('show');
                toggleBtn.innerHTML = '<i class="fas fa-bars me-2"></i>Menu Admin SPMB';
            } else {
                mobileMenu.classList.add('show');
                toggleBtn.innerHTML = '<i class="fas fa-times me-2"></i>Tutup Menu';
            }
        }

        // Auto-hide mobile menu on window resize
        window.addEventListener('resize', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth > 768) {
                mobileMenu.classList.remove('show');
                toggleBtn.innerHTML = '<i class="fas fa-bars me-2"></i>Menu Admin SPMB';
            }
        });

        // Initialize mobile menu state
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            
            // Hide mobile menu by default
            mobileMenu.classList.remove('show');
            
            // Show/hide toggle button based on screen size
            if (window.innerWidth <= 768) {
                toggleBtn.style.display = 'block';
            } else {
                toggleBtn.style.display = 'none';
            }
        });

        function deleteRegistration(id) {
            document.getElementById('deleteForm').action = '{{ route("manage.spmb.destroy", ":id") }}'.replace(':id', id);
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function updateStatus(id, status) {
            if (confirm(`Apakah Anda yakin ingin mengubah status menjadi ${status}?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("manage.spmb.update-registration-status", ":id") }}'.replace(':id', id);
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status_pendaftaran';
                statusInput.value = status;
                
                form.appendChild(csrfToken);
                form.appendChild(statusInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Bulk Actions
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const bulkActions = document.querySelector('.bulk-actions');
            const selectedCount = document.querySelector('.selected-count');

            // Select All functionality
            selectAllCheckbox.addEventListener('change', function() {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActions();
            });

            // Individual checkbox functionality
            rowCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateBulkActions();
                    
                    // Update select all checkbox
                    const totalCheckboxes = rowCheckboxes.length;
                    const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked').length;
                    
                    selectAllCheckbox.checked = checkedCheckboxes === totalCheckboxes;
                    selectAllCheckbox.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
                });
            });

            function updateBulkActions() {
                const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
                const count = checkedCheckboxes.length;
                
                if (count > 0) {
                    bulkActions.classList.remove('d-none');
                    selectedCount.textContent = `${count} dipilih`;
                } else {
                    bulkActions.classList.add('d-none');
                }
            }
        });

        function bulkDelete() {
            const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
            if (checkedCheckboxes.length === 0) {
                alert('Pilih minimal satu data untuk dihapus!');
                return;
            }

            if (confirm(`Apakah Anda yakin ingin menghapus ${checkedCheckboxes.length} data yang dipilih?\n\nData yang dihapus tidak dapat dikembalikan!`)) {
                const form = document.getElementById('bulkForm');
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                form.appendChild(actionInput);
                form.submit();
            }
        }

        function bulkUpdateStatus(status) {
            const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
            if (checkedCheckboxes.length === 0) {
                alert('Pilih minimal satu data untuk diubah statusnya!');
                return;
            }

            const statusText = status === 'diterima' ? 'diterima' : 'ditolak';
            if (confirm(`Apakah Anda yakin ingin mengubah status ${checkedCheckboxes.length} data menjadi ${statusText}?`)) {
                const form = document.getElementById('bulkForm');
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'update_status';
                form.appendChild(actionInput);
                
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status_pendaftaran';
                statusInput.value = status;
                form.appendChild(statusInput);
                
                form.submit();
            }
        }
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
