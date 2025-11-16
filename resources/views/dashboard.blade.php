@extends('layouts.adminty')

@section('head')
<style>
    /* Fix untuk widget card icon tidak tertutup */
    .widget-card-1 {
        overflow: visible !important;
        margin-top: 20px;
    }
    
    .widget-card-1 .card-block {
        padding: 1.25rem;
        padding-top: 15px;
        padding-right: 80px; /* Beri ruang untuk icon di kanan */
        position: relative;
        text-align: left !important; /* Rata kiri */
    }
    
    .widget-card-1 .card1-icon {
        position: absolute;
        top: -15px;
        right: 20px;
        left: auto;
        width: 60px;
        height: 60px;
        font-size: 35px;
        border-radius: 8px;
        display: flex;
        color: #fff;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease-in-out;
        z-index: 10;
    }
    
    .widget-card-1:hover .card1-icon {
        top: -25px;
    }
    
    /* Pastikan semua teks di widget card rata kiri */
    .widget-card-1 .card-block {
        text-align: left !important;
    }
    
    .widget-card-1 .card-block h6,
    .widget-card-1 .card-block h4,
    .widget-card-1 .card-block span,
    .widget-card-1 .card-block small,
    .widget-card-1 .card-block div {
        text-align: left !important;
    }
    
    /* Pastikan teks di atas tidak tertutup icon */
    .widget-card-1 .card-block > h6:first-child {
        margin-right: 70px; /* Beri margin kanan agar tidak tertutup icon */
        padding-right: 0;
    }
    
    /* Khusus untuk card dengan background gradient (Pembayaran Bulan Ini) */
    .widget-card-1.bg-c-blue .card-block {
        padding-right: 80px;
    }
    
    .widget-card-1.bg-c-blue .card-block > h6:first-child {
        margin-right: 70px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Pratinjau Profil Sekolah -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center" style="padding: 1.5rem; border-radius: 15px 15px 0 0;">
            <div class="d-flex align-items-center">
                <div>
                    <h5 class="fw-bold mb-0" style="color: #1f2937;">
                        <i class="feather icon-home me-2" style="color: #01a9ac; font-size: 1.1rem;"></i>
                        Pratinjau Profil Sekolah
                    </h5>
                    <small class="text-muted" style="font-size: 0.75rem;">Informasi lengkap profil sekolah</small>
                </div>
            </div>
            @php
                $isFoundationLevel = auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan';
            @endphp
            <a href="{{ route('manage.foundation.schools.edit', $currentSchool->id) }}" class="btn btn-primary btn-sm" style="background: linear-gradient(135deg, #01a9ac 0%, #0ac282 100%); border: none; border-radius: 8px; padding: 0.5rem 1.25rem;">
                <i class="feather icon-edit me-2"></i>Lengkapi Profil
            </a>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="info-item p-3 mb-3" style="background: white; border-radius: 12px; border-left: 4px solid #01a9ac; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.05)';">
                        <div class="d-flex align-items-center mb-2">
                            <i class="feather icon-bookmark text-c-blue me-2"></i>
                            <label class="text-muted small mb-0" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Nama Sekolah</label>
                        </div>
                        <div class="fw-bold" style="color: #1f2937; font-size: 1rem;">{{ $currentSchool->nama_sekolah ?? 'Belum diisi' }}</div>
                    </div>
                    <div class="info-item p-3" style="background: white; border-radius: 12px; border-left: 4px solid #01a9ac; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.05)';">
                        <div class="d-flex align-items-center mb-2">
                            <i class="feather icon-user text-c-blue me-2"></i>
                            <label class="text-muted small mb-0" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Kepala Sekolah</label>
                        </div>
                        <div class="fw-bold" style="color: #1f2937; font-size: 1rem;">{{ $currentSchool->kepala_sekolah ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="info-item p-3 mb-3" style="background: white; border-radius: 12px; border-left: 4px solid #01a9ac; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.05)';">
                        <div class="d-flex align-items-center mb-2">
                            <i class="feather icon-hash text-c-blue me-2"></i>
                            <label class="text-muted small mb-0" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">NPSN</label>
                        </div>
                        <div class="fw-bold" style="color: #1f2937; font-size: 1rem;">{{ $currentSchool->npsn ?? '-' }}</div>
                    </div>
                    <div class="info-item p-3" style="background: white; border-radius: 12px; border-left: 4px solid #01a9ac; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.05)';">
                        <div class="d-flex align-items-center mb-2">
                            <i class="feather icon-map-pin text-c-blue me-2"></i>
                            <label class="text-muted small mb-0" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Alamat</label>
                        </div>
                        <div class="fw-bold" style="color: #1f2937; font-size: 1rem; line-height: 1.6;">
                            @if($currentSchool->alamat_baris_1)
                                {{ $currentSchool->alamat_baris_1 }}
                                @if($currentSchool->alamat_baris_2)
                                    <br>{{ $currentSchool->alamat_baris_2 }}
                                @endif
                            @else
                                {{ $currentSchool->alamat ?? 'Belum diisi' }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Row: Statistik Pembayaran & Traffic Pembayaran -->
    <div class="row mb-4">
        <!-- Statistik Pembayaran (Line Chart) -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px; height: 100%;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size: 0.9rem;">💰 Statistik Pembayaran</h6>
                            <h3 class="fw-bold text-primary mb-0">Rp {{ number_format($totalPaymentsThisYear ?? 0, 0, ',', '.') }}</h3>
                            <small class="text-muted">Juli - Juni {{ now()->year }}</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-pill" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('payment.index') }}"><i class="fas fa-list me-2"></i>Lihat Semua</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i>Export Data</a></li>
                            </ul>
                        </div>
                    </div>
                    <div style="height: 250px;">
                        <canvas id="paymentsLineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Rank Pembayaran User -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px; height: 100%;">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center" style="padding: 1.25rem 1.5rem;">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);">
                            <i class="feather icon-award text-white"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Top Rank Pembayaran</h6>
                            <small class="text-muted" style="font-size: 0.75rem;">Berdasarkan Jumlah Transaksi</small>
                        </div>
                    </div>
                    <span class="badge bg-c-green text-white">{{ $selectedPeriod ? $selectedPeriod->period_start . '/' . $selectedPeriod->period_end : date('Y') . '/' . (date('Y') + 1) }}</span>
                </div>
                <div class="card-body p-4">
                    <div style="height: 220px; overflow-y: auto;">
                        @forelse($topPaymentUsers ?? [] as $index => $user)
                        <div class="rank-item mb-3 p-3" style="background: linear-gradient(135deg, {{ $index < 3 ? '#f0fdf4' : '#f9fafb' }} 0%, {{ $index < 3 ? '#dcfce7' : '#ffffff' }} 100%); border-radius: 12px; border-left: 4px solid {{ $index == 0 ? '#10b981' : ($index == 1 ? '#3b82f6' : ($index == 2 ? '#8b5cf6' : '#6b7280')) }}; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateX(5px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='none';">
                            <div class="d-flex align-items-center">
                                <div class="rank-number me-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background: linear-gradient(135deg, {{ $index == 0 ? '#10b981' : ($index == 1 ? '#3b82f6' : ($index == 2 ? '#8b5cf6' : '#6b7280')) }} 0%, {{ $index == 0 ? '#059669' : ($index == 1 ? '#2563eb' : ($index == 2 ? '#7c3aed' : '#4b5563')) }} 100%); box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                        @if($index === 0)
                                            <i class="feather icon-award text-white" style="font-size: 1.2rem;"></i>
                                        @elseif($index === 1)
                                            <i class="feather icon-award text-white" style="font-size: 1.2rem;"></i>
                                        @elseif($index === 2)
                                            <i class="feather icon-award text-white" style="font-size: 1.2rem;"></i>
                                        @else
                                            <span class="text-white fw-bold" style="font-size: 1.1rem;">{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold mb-1" style="color: #1f2937; font-size: 0.95rem;">{{ $user['name'] }}</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-dark" style="font-size: 0.7rem; padding: 0.25rem 0.5rem;">{{ $user['class'] ?? 'N/A' }}</span>
                                        <span class="text-muted" style="font-size: 0.8rem;">
                                            <i class="feather icon-shopping-cart me-1"></i>{{ $user['transaction_count'] }} transaksi
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-5">
                            <div class="mb-3">
                                <i class="feather icon-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            </div>
                            <p class="mb-0" style="font-size: 0.9rem;">Belum ada data transaksi</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row: Persentase Pembayaran & Cards -->
    <div class="row mb-4">
        <!-- Pembayaran Bulan Ini -->
        <div class="col-md-3 mb-3">
            <div class="card widget-card-1" style="position: relative; background: white;">
                <div class="card-block">
                    <div class="card1-icon bg-c-blue" style="background: linear-gradient(135deg, #01a9ac 0%, #0ac282 100%);">
                        <i class="feather icon-pie-chart"></i>
                    </div>
                    <h6 class="mb-0" style="color: #919aa3; font-size: 0.85rem;">Pembayaran Bulan Ini</h6>
                    <h4 class="mt-2 mb-2" style="color: #01a9ac; font-weight: 600;">Rp {{ number_format($monthPayments ?? 0, 0, ',', '.') }}</h4>
                    <div class="d-flex align-items-center mt-3">
                        <span style="color: #919aa3; font-size: 0.75rem;">
                            {{ number_format($monthPaymentsCount ?? 0) }} transaksi
                        </span>
                        @php
                            $monthGrowth = $monthPaymentsGrowth ?? 0;
                            $isMonthGrowthPositive = $monthGrowth >= 0;
                        @endphp
                        <span class="badge ms-auto" style="background-color: {{ $isMonthGrowthPositive ? '#10b981' : '#ef4444' }}; font-size: 0.75rem; padding: 0.35rem 0.65rem;">
                            <i class="fas fa-arrow-{{ $isMonthGrowthPositive ? 'up' : 'down' }}"></i> {{ abs($monthGrowth) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Persentase Lunas -->
        <div class="col-md-3 mb-3">
            <div class="card widget-card-1" style="position: relative;">
                <div class="card-block">
                    <div class="card1-icon bg-c-blue" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="feather icon-target"></i>
                    </div>
                    <h6 class="mb-0" style="color: #919aa3; font-size: 0.85rem;">Target Prosentase Pembayaran Bulan Ini</h6>
                    <h4 class="mt-2 mb-3" style="color: #4facfe; font-weight: 600;">{{ number_format($paymentCompletionPercent ?? 0, 1) }}%</h4>
                    <div class="progress" style="height: 8px; border-radius: 10px; background-color: #e9ecef;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ $paymentCompletionPercent ?? 0 }}%; 
                                    border-radius: 10px;
                                    background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Countdown Berlangganan -->
        <div class="col-md-3 mb-3">
            <div class="card widget-card-1" style="position: relative;">
                <div class="card-block">
                    <div class="card1-icon bg-c-yellow" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="feather icon-clock"></i>
                    </div>
                    <h6 class="mb-0" style="color: #919aa3; font-size: 0.85rem;">Sisa Waktu Berlangganan</h6>
                    @if(isset($subscriptionDaysLeft) && $subscriptionDaysLeft > 0)
                        @if($subscriptionDaysLeft >= 30)
                            <h4 class="mt-2 mb-3" style="color: #0ac282; font-weight: 600;">{{ floor($subscriptionDaysLeft / 30) }} Bulan</h4>
                            <div class="progress" style="height: 8px; border-radius: 10px; background-color: #e9ecef;">
                                <div class="progress-bar bg-c-green" role="progressbar" 
                                     style="width: 100%; border-radius: 10px;"></div>
                            </div>
                        @elseif($subscriptionDaysLeft >= 7)
                            <h4 class="mt-2 mb-3" style="color: #fe9365; font-weight: 600;">{{ number_format($subscriptionDaysLeft, 0, '', '') }} Hari</h4>
                            <div class="progress" style="height: 8px; border-radius: 10px; background-color: #e9ecef;">
                                <div class="progress-bar bg-c-yellow" role="progressbar" 
                                     style="width: {{ floor(($subscriptionDaysLeft / 30) * 100) }}%; border-radius: 10px;"></div>
                            </div>
                        @else
                            <h4 class="mt-2 mb-3" style="color: #fe5d70; font-weight: 600;">{{ number_format($subscriptionDaysLeft, 0, '', '') }} Hari</h4>
                            <div class="progress" style="height: 8px; border-radius: 10px; background-color: #e9ecef;">
                                <div class="progress-bar bg-c-pink" role="progressbar" 
                                     style="width: {{ floor(($subscriptionDaysLeft / 30) * 100) }}%; border-radius: 10px;"></div>
                            </div>
                        @endif
                    @else
                        <h4 class="mt-2 mb-3" style="color: #fe5d70; font-weight: 600;">Expired</h4>
                        <div class="progress" style="height: 8px; border-radius: 10px; background-color: #e9ecef;">
                            <div class="progress-bar bg-c-pink" role="progressbar" style="width: 0%;"></div>
                        </div>
                    @endif
                    <div class="mt-2">
                        <small style="color: #919aa3;">Berakhir: {{ $subscriptionExpiresAt ?? '-' }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kalender -->
        <div class="col-md-3 mb-3">
            <div class="card widget-card-1" style="position: relative;">
                <div class="card-block">
                    <div class="card1-icon bg-c-blue" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                        <i class="feather icon-calendar"></i>
                    </div>
                    <h6 class="mb-0" style="color: #919aa3; font-size: 0.85rem;">Kalender Hari Ini</h6>
                    @php
                        $currentDate = \Carbon\Carbon::now();
                        $dayName = $currentDate->locale('id')->isoFormat('dddd');
                        $dateNumber = $currentDate->format('d');
                        $monthYear = $currentDate->locale('id')->isoFormat('MMMM YYYY');
                    @endphp
                    <div class="text-center mb-3 mt-3">
                        <div class="fw-bold mb-1" style="color: #8b5cf6; font-size: 3rem; line-height: 1;">{{ $dateNumber }}</div>
                        <div style="color: #919aa3; font-size: 0.9rem;">{{ $monthYear }}</div>
                        <div class="badge bg-c-blue mt-2" style="color: white; font-size: 0.75rem;">
                            {{ $dayName }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Third Row: Grafik Jumlah Siswa/Kelas & Penerimaan/Pengeluaran -->
    <div class="row mb-4">
        <!-- Grafik Siswa Per Kelas (Doughnut) -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center" style="padding: 1.25rem 1.5rem;">
                    <h6 class="mb-0 fw-bold">📚 Distribusi Siswa Per Kelas</h6>
                    <span class="badge bg-light text-dark">Total: {{ $totalStudents ?? 0 }}</span>
                </div>
                <div class="card-body p-4">
                    <div style="height: 300px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="studentsPerClassChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Penerimaan/Pengeluaran (Bar) -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center" style="padding: 1.25rem 1.5rem;">
                    <h6 class="mb-0 fw-bold">💸 Penerimaan vs Pengeluaran</h6>
                    <form method="GET" action="" class="d-flex align-items-center" style="gap:8px">
                        <select name="period_id" class="form-control select-primary" onchange="this.form.submit()" style="min-width: 150px;">
                            @foreach(($periods ?? []) as $p)
                                <option value="{{ $p->period_id }}" {{ ($selectedPeriodId ?? '') == $p->period_id ? 'selected' : '' }}>
                                    {{ $p->period_start }}/{{ $p->period_end }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="card-body p-4">
                    <div style="height: 300px;">
                        <canvas id="incomeExpenseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fourth Row: Persentase Pembayaran Per Kelas -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0" style="padding: 1.25rem 1.5rem;">
                    <h6 class="mb-0 fw-bold">📈 Persentase Pembayaran Per Kelas</h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Kelas</th>
                                    <th width="15%">Jumlah Siswa</th>
                                    <th width="15%">Lunas</th>
                                    <th width="15%">Belum Lunas</th>
                                    <th width="25%">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentProgressByClass ?? [] as $index => $class)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $class['class_name'] }}</strong></td>
                                    <td>{{ $class['total_students'] }} siswa</td>
                                    <td><span class="badge bg-success">{{ $class['paid_students'] }}</span></td>
                                    <td><span class="badge bg-danger">{{ $class['unpaid_students'] }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 28px; border-radius: 14px; background-color: #e9ecef; position: relative; overflow: visible;">
                                                <div class="progress-bar" 
                                                     style="width: {{ max($class['percentage'], 5) }}%; 
                                                            min-width: {{ $class['percentage'] > 0 ? '30px' : '0' }};
                                                            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
                                                            border-radius: 14px;
                                                            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
                                                            display: flex;
                                                            align-items: center;
                                                            justify-content: center;
                                                            position: relative;" 
                                                     role="progressbar">
                                                    @if($class['percentage'] > 0)
                                                    <span class="small fw-bold text-white" style="text-shadow: 0 1px 2px rgba(0,0,0,0.3); font-size: 0.75rem; white-space: nowrap;">{{ number_format($class['percentage'], 1) }}%</span>
                                                    @endif
                                                </div>
                                                @if($class['percentage'] == 0)
                                                <span class="position-absolute" style="left: 50%; transform: translateX(-50%); color: #6b7280; font-size: 0.75rem; font-weight: 600;">{{ number_format($class['percentage'], 1) }}%</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada data</td>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Statistik Pembayaran (Line Chart)
    if (document.getElementById('paymentsLineChart')) {
        const monthLabels = {!! json_encode($monthLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']) !!};
        const paymentsData = {!! json_encode(array_values($paymentsMonthly ?? [0,0,0,0,0,0,0,0,0,0,0,0])) !!};
        
        new Chart(document.getElementById('paymentsLineChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Pembayaran',
                    data: paymentsData,
                    borderColor: '#5b6ee1',
                    backgroundColor: 'rgba(91, 110, 225, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: '#5b6ee1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#000',
                        bodyColor: '#666',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9ca3af' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' },
                        ticks: {
                            color: '#9ca3af',
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Top Rank Pembayaran User - No chart needed, using HTML list

    // 3. Distribusi Siswa Per Kelas (Doughnut)
    if (document.getElementById('studentsPerClassChart')) {
        const classLabels = {!! json_encode($classLabels ?? []) !!};
        const classData = {!! json_encode($classData ?? []) !!};
        
        new Chart(document.getElementById('studentsPerClassChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: classLabels,
                datasets: [{
                    data: classData,
                    backgroundColor: [
                        '#667eea', '#764ba2', '#f093fb', '#f5576c',
                        '#4facfe', '#00f2fe', '#43e97b', '#38f9d7',
                        '#fa709a', '#fee140', '#30cfd0', '#330867'
                    ],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15,
                            font: { size: 12 },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#000',
                        bodyColor: '#666',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' siswa';
                            }
                        }
                    }
                }
            }
        });
    }

    // 4. Penerimaan vs Pengeluaran (Bar)
    if (document.getElementById('incomeExpenseChart')) {
        const receiptsData = {!! json_encode(array_values($receiptsMonthly ?? [0,0,0,0,0,0,0,0,0,0,0,0])) !!};
        const expensesData = {!! json_encode(array_values($expensesMonthly ?? [0,0,0,0,0,0,0,0,0,0,0,0])) !!};
        
        new Chart(document.getElementById('incomeExpenseChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [
                    {
                        label: 'Penerimaan',
                        data: receiptsData,
                        backgroundColor: '#10b981',
                        borderRadius: 8,
                        maxBarThickness: 25
                    },
                    {
                        label: 'Pengeluaran',
                        data: expensesData,
                        backgroundColor: '#ef4444',
                        borderRadius: 8,
                        maxBarThickness: 25
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#000',
                        bodyColor: '#666',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9ca3af' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' },
                        ticks: {
                            color: '#9ca3af',
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<style>
.icon-box {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.progress-bar {
    transition: width 0.6s ease;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

/* Responsive */
@media (max-width: 768px) {
    .card-body h2 {
        font-size: 1.5rem !important;
    }
    
    .icon-box {
        width: 40px;
        height: 40px;
    }
}
</style>
@endsection

