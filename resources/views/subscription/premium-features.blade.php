@extends('layouts.coreui')

@section('title', 'Fitur Premium - SPPQU')

@section('active_menu', 'menu.billing')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('manage.admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manage.subscription.index') }}">Berlangganan</a></li>
                        <li class="breadcrumb-item active">Fitur Premium</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-crown me-2 text-warning"></i>
                    Fitur Premium SPPQU
                </h4>
            </div>
        </div>
    </div>

    <!-- Subscription Status -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-info"></i>
                        Status Berlangganan Anda
                    </h5>
                </div>
                <div class="card-body">
                    @if(hasActiveSubscription())
                        <div class="alert alert-success">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="mb-1">Berlangganan Aktif</h6>
                                    <p class="mb-0">Anda memiliki akses penuh ke semua fitur premium SPPQU.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <h6 class="mb-1">Berlangganan Tidak Aktif</h6>
                                    <p class="mb-0">Berlangganan sekarang untuk mengakses semua fitur premium.</p>
                                </div>
                                <div class="ms-auto">
                                    <a href="{{ route('manage.subscription.plans') }}" class="btn btn-warning">
                                        <i class="fas fa-crown me-2"></i>Berlangganan Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-star me-2 text-warning"></i>
                        Daftar Fitur Premium
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Akuntansi -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-calculator text-primary" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5 class="card-title">Sistem Akuntansi</h5>
                                    <p class="card-text text-muted">
                                        Manajemen kas, penerimaan, pengeluaran, transfer kas, dan arus kas yang lengkap.
                                    </p>
                                    <div class="mt-3">
                                        @if(function_exists('canAccessPremium') && canAccessPremium('menu.akuntansi'))
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-lock me-1"></i>Premium
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Laporan -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-chart-line text-success" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5 class="card-title">Laporan Lengkap</h5>
                                    <p class="card-text text-muted">
                                        Laporan per pos, per kelas, rekapitulasi, analisis target, dan tunggakan siswa.
                                    </p>
                                    <div class="mt-3">
                                        @if(function_exists('canAccessPremium') && canAccessPremium('menu.laporan'))
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-lock me-1"></i>Premium
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kirim Tagihan -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-paper-plane text-info" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5 class="card-title">Kirim Tagihan WhatsApp</h5>
                                    <p class="card-text text-muted">
                                        Kirim tagihan otomatis ke siswa dan wali melalui WhatsApp secara massal.
                                    </p>
                                    <div class="mt-3">
                                        @if(function_exists('canAccessPremium') && canAccessPremium('menu.kirim_tagihan'))
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-lock me-1"></i>Premium
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Manajemen User -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-user-cog text-warning" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5 class="card-title">Manajemen Pengguna</h5>
                                    <p class="card-text text-muted">
                                        Kelola pengguna, role, dan hak akses menu dengan sistem permission yang fleksibel.
                                    </p>
                                    <div class="mt-3">
                                        @if(function_exists('canAccessPremium') && canAccessPremium('menu.users'))
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-lock me-1"></i>Premium
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- General Setting -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-cogs text-secondary" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5 class="card-title">Pengaturan Umum</h5>
                                    <p class="card-text text-muted">
                                        Konfigurasi sistem, pengaturan sekolah, dan parameter aplikasi yang lengkap.
                                    </p>
                                    <div class="mt-3">
                                        @if(function_exists('canAccessPremium') && canAccessPremium('menu.general_setting'))
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-lock me-1"></i>Premium
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                                                 <!-- Pembayaran -->
                         <div class="col-md-6 col-lg-4 mb-4">
                             <div class="card h-100 border-0 shadow-sm">
                                 <div class="card-body text-center">
                                     <div class="mb-3">
                                         <i class="fas fa-money-bill text-success" style="font-size: 2.5rem;"></i>
                                     </div>
                                     <h5 class="card-title">Sistem Pembayaran</h5>
                                     <p class="card-text text-muted">
                                         Pembayaran tunai dan online dengan berbagai metode pembayaran yang fleksibel.
                                     </p>
                                     <div class="mt-3">
                                         @if(function_exists('canAccessPremium') && canAccessPremium('menu.pembayaran'))
                                             <span class="badge bg-success">
                                                 <i class="fas fa-check me-1"></i>Aktif
                                             </span>
                                         @else
                                             <span class="badge bg-secondary">
                                                 <i class="fas fa-lock me-1"></i>Premium
                                             </span>
                                         @endif
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <!-- Export/Import -->
                         <div class="col-md-6 col-lg-4 mb-4">
                             <div class="card h-100 border-0 shadow-sm">
                                 <div class="card-body text-center">
                                     <div class="mb-3">
                                         <i class="fas fa-file-export text-danger" style="font-size: 2.5rem;"></i>
                                     </div>
                                     <h5 class="card-title">Export & Import Data</h5>
                                     <p class="card-text text-muted">
                                         Export data ke Excel/PDF dan import data dari file Excel dengan validasi.
                                     </p>
                                     <div class="mt-3">
                                         @if(hasActiveSubscription())
                                             <span class="badge bg-success">
                                                 <i class="fas fa-check me-1"></i>Aktif
                                             </span>
                                         @else
                                             <span class="badge bg-secondary">
                                                 <i class="fas fa-lock me-1"></i>Premium
                                             </span>
                                         @endif
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <!-- Payment Gateway Add-on -->
                         <div class="col-md-6 col-lg-4 mb-4">
                             <div class="card h-100 border-0 shadow-sm">
                                 <div class="card-body text-center">
                                     <div class="mb-3">
                                         <i class="fas fa-credit-card text-primary" style="font-size: 2.5rem;"></i>
                                     </div>
                                     <h5 class="card-title">Payment Gateway</h5>
                                     <p class="card-text text-muted">
                                         Integrasi payment gateway untuk pembayaran online dengan berbagai metode pembayaran.
                                     </p>
                                     <div class="mt-3">
                                         @php
                                             $hasPaymentGateway = \App\Models\UserAddon::where('user_id', auth()->id())
                                                 ->whereHas('addon', function($query) {
                                                     $query->where('slug', 'payment-gateway');
                                                 })
                                                 ->where('status', 'active')
                                                 ->exists();
                                         @endphp
                                         @if($hasPaymentGateway)
                                             <span class="badge bg-success">
                                                 <i class="fas fa-check me-1"></i>Aktif
                                             </span>
                                         @else
                                             <span class="badge bg-warning">
                                                 <i class="fas fa-plus me-1"></i>Add-on
                                             </span>
                                         @endif
                                     </div>
                                     <div class="mt-2">
                                         @if(!$hasPaymentGateway)
                                                                                           <a href="{{ route('manage.addons.show', 'payment-gateway') }}" class="btn btn-sm btn-outline-primary">
                                                 <i class="fas fa-shopping-cart me-1"></i>Beli Add-on
                                             </a>
                                         @endif
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <!-- WhatsApp Gateway Add-on -->
                         <div class="col-md-6 col-lg-4 mb-4">
                             <div class="card h-100 border-0 shadow-sm">
                                 <div class="card-body text-center">
                                     <div class="mb-3">
                                         <i class="fab fa-whatsapp text-success" style="font-size: 2.5rem;"></i>
                                     </div>
                                     <h5 class="card-title">WhatsApp Gateway</h5>
                                     <p class="card-text text-muted">
                                         Integrasi WhatsApp Gateway untuk notifikasi otomatis dan komunikasi.
                                     </p>
                                     <div class="mt-3">
                                         @php
                                             $hasWhatsAppGateway = \App\Models\UserAddon::where('user_id', auth()->id())
                                                 ->whereHas('addon', function($query) {
                                                     $query->where('slug', 'whatsapp-gateway');
                                                 })
                                                 ->where('status', 'active')
                                                 ->exists();
                                         @endphp
                                         @if($hasWhatsAppGateway)
                                             <span class="badge bg-success">
                                                 <i class="fas fa-check me-1"></i>Aktif
                                             </span>
                                         @else
                                             <span class="badge bg-warning">
                                                 <i class="fas fa-plus me-1"></i>Add-on
                                             </span>
                                         @endif
                                     </div>
                                     <div class="mt-2">
                                         @if(!$hasWhatsAppGateway)
                                             <a href="{{ route('manage.addons.show', 'whatsapp-gateway') }}" class="btn btn-sm btn-outline-primary">
                                                 <i class="fas fa-shopping-cart me-1"></i>Beli Add-on
                                             </a>
                                         @endif
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <!-- Analisis Target Add-on -->
                         <div class="col-md-6 col-lg-4 mb-4">
                             <div class="card h-100 border-0 shadow-sm">
                                 <div class="card-body text-center">
                                     <div class="mb-3">
                                         <i class="fas fa-chart-line text-info" style="font-size: 2.5rem;"></i>
                                     </div>
                                     <h5 class="card-title">Analisis Target</h5>
                                     <p class="card-text text-muted">
                                         Dashboard analisis target capaian untuk monitoring pembayaran SPP.
                                     </p>
                                     <div class="mt-3">
                                         @php
                                             $hasAnalisisTarget = \App\Models\UserAddon::where('user_id', auth()->id())
                                                 ->whereHas('addon', function($query) {
                                                     $query->where('slug', 'analisis-target');
                                                 })
                                                 ->where('status', 'active')
                                                 ->exists();
                                         @endphp
                                         @if($hasAnalisisTarget)
                                             <span class="badge bg-success">
                                                 <i class="fas fa-check me-1"></i>Aktif
                                             </span>
                                         @else
                                             <span class="badge bg-warning">
                                                 <i class="fas fa-plus me-1"></i>Add-on
                                             </span>
                                         @endif
                                     </div>
                                     <div class="mt-2">
                                         @if(!$hasAnalisisTarget)
                                             <a href="{{ route('manage.addons.show', 'analisis-target') }}" class="btn btn-sm btn-outline-primary">
                                                 <i class="fas fa-shopping-cart me-1"></i>Beli Add-on
                                             </a>
                                         @endif
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <!-- SPMB Add-on -->
                         <div class="col-md-6 col-lg-4 mb-4">
                             <div class="card h-100 border-0 shadow-sm">
                                 <div class="card-body text-center">
                                     <div class="mb-3">
                                         <i class="fas fa-user-graduate text-primary" style="font-size: 2.5rem;"></i>
                                     </div>
                                     <h5 class="card-title">SPMB</h5>
                                     <p class="card-text text-muted">
                                         Sistem Penerimaan Mahasiswa Baru dengan pendaftaran online dan seleksi.
                                     </p>
                                     <div class="mt-3">
                                         @php
                                             $hasSPMB = \App\Models\UserAddon::where('user_id', auth()->id())
                                                 ->whereHas('addon', function($query) {
                                                     $query->where('slug', 'spmb');
                                                 })
                                                 ->where('status', 'active')
                                                 ->exists();
                                         @endphp
                                         <span class="badge bg-secondary">
                                             <i class="fas fa-clock me-1"></i>Segera
                                         </span>
                                     </div>
                                                                           <div class="mt-2">
                                          <button class="btn btn-sm btn-outline-secondary" disabled>
                                              <i class="fas fa-clock me-1"></i>Segera
                                          </button>
                                      </div>
                                 </div>
                             </div>
                         </div>

                         <!-- Pencatatan Skor Poin Siswa Add-on -->
                         <div class="col-md-6 col-lg-4 mb-4">
                             <div class="card h-100 border-0 shadow-sm">
                                 <div class="card-body text-center">
                                     <div class="mb-3">
                                         <i class="fas fa-star text-warning" style="font-size: 2.5rem;"></i>
                                     </div>
                                     <h5 class="card-title">Skor Poin Siswa</h5>
                                     <p class="card-text text-muted">
                                         Sistem pencatatan dan monitoring skor poin siswa untuk evaluasi.
                                     </p>
                                     <div class="mt-3">
                                         @php
                                             $hasSkorPoin = \App\Models\UserAddon::where('user_id', auth()->id())
                                                 ->whereHas('addon', function($query) {
                                                     $query->where('slug', 'bk');
                                                 })
                                                 ->where('status', 'active')
                                                 ->exists();
                                         @endphp
                                         <span class="badge bg-secondary">
                                             <i class="fas fa-clock me-1"></i>Segera
                                         </span>
                                     </div>
                                                                           <div class="mt-2">
                                          <button class="btn btn-sm btn-outline-secondary" disabled>
                                              <i class="fas fa-clock me-1"></i>Segera
                                          </button>
                                      </div>
                                 </div>
                             </div>
                         </div>

                        <!-- E-Jurnal Harian 7KAIH Add-on -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-book" style="font-size: 2.5rem; color: #6f42c1;"></i>
                                    </div>
                                    <h5 class="card-title">E-Jurnal Harian 7KAIH</h5>
                                    <p class="card-text text-muted">
                                        Sistem jurnal harian 7 Kebiasaan Anak Indonesia Hebat dengan monitoring guru.
                                    </p>
                                    <div class="mt-3">
                                        @php
                                            $hasEJurnal = \App\Models\UserAddon::where('user_id', auth()->id())
                                                ->whereHas('addon', function($query) {
                                                    $query->where('slug', 'ejurnal-7kaih');
                                                })
                                                ->where('status', 'active')
                                                ->exists();
                                        @endphp
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-clock me-1"></i>Segera
                                        </span>
                                    </div>
                                                                      <div class="mt-2">
                                         <button class="btn btn-sm btn-outline-secondary" disabled>
                                             <i class="fas fa-clock me-1"></i>Segera
                                         </button>
                                     </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inventaris Add-on -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-boxes text-info" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5 class="card-title">Sistem Inventaris</h5>
                                    <p class="card-text text-muted">
                                        Manajemen inventaris sekolah untuk tracking aset dan pemeliharaan.
                                    </p>
                                    <div class="mt-3">
                                        @php
                                            $hasInventaris = \App\Models\UserAddon::where('user_id', auth()->id())
                                                ->whereHas('addon', function($query) {
                                                    $query->where('slug', 'inventaris');
                                                })
                                                 ->where('status', 'active')
                                                 ->exists();
                                         @endphp
                                         <span class="badge bg-secondary">
                                             <i class="fas fa-clock me-1"></i>Segera
                                         </span>
                                     </div>
                                                                           <div class="mt-2">
                                          <button class="btn btn-sm btn-outline-secondary" disabled>
                                              <i class="fas fa-clock me-1"></i>Segera
                                          </button>
                                      </div>
                                 </div>
                             </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
