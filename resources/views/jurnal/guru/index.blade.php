@extends('layouts.adminty')

@push('styles')
<style>
    .info-card {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .info-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .info-card-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #ffffff;
        flex-shrink: 0;
    }

    .info-card-icon.primary {
        background: #01a9ac;
    }

    .info-card-icon.success {
        background: #28a745;
    }

    .info-card-icon.warning {
        background: #ffc107;
    }

    .info-card-icon.danger {
        background: #dc3545;
    }

    .info-card-icon.info {
        background: #17a2b8;
    }

    .info-card-title {
        font-size: 0.9rem;
        font-weight: 600;
        margin: 0;
        text-align: right;
        flex: 1;
        padding-left: 1rem;
    }

    .info-card-title.primary {
        color: #01a9ac;
    }

    .info-card-title.success {
        color: #28a745;
    }

    .info-card-title.warning {
        color: #ffc107;
    }

    .info-card-title.danger {
        color: #dc3545;
    }

    .info-card-title.info {
        color: #17a2b8;
    }

    .info-card-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1rem;
        line-height: 1.2;
    }

    .info-card-footer {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        margin-top: auto;
    }

    .info-card-footer-icon {
        font-size: 0.875rem;
    }

    .info-card-footer-text {
        color: #6c757d;
    }

    .info-card-footer.primary .info-card-footer-icon,
    .info-card-footer.primary .info-card-footer-text {
        color: #01a9ac;
    }

    .info-card-footer.success .info-card-footer-icon,
    .info-card-footer.success .info-card-footer-text {
        color: #28a745;
    }

    .info-card-footer.warning .info-card-footer-icon,
    .info-card-footer.warning .info-card-footer-text {
        color: #ffc107;
    }

    .info-card-footer.danger .info-card-footer-icon,
    .info-card-footer.danger .info-card-footer-text {
        color: #dc3545;
    }

    .info-card-footer.info .info-card-footer-icon,
    .info-card-footer.info .info-card-footer-text {
        color: #17a2b8;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                <i class="feather icon-book me-2"></i>Dashboard E-Jurnal 7KAIH
            </h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Monitoring & Verifikasi Jurnal Harian Siswa</p>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-card-icon primary">
                        <i class="feather icon-book-open"></i>
                    </div>
                    <div class="info-card-title primary">Total Jurnal</div>
                </div>
                <div class="info-card-value">{{ $stats['total_jurnal'] ?? 0 }}</div>
                <div class="info-card-footer primary">
                    <i class="feather icon-file-text info-card-footer-icon"></i>
                    <span class="info-card-footer-text">Semua jurnal</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-card-icon warning">
                        <i class="feather icon-clock"></i>
                    </div>
                    <div class="info-card-title warning">Pending Verifikasi</div>
                </div>
                <div class="info-card-value">{{ $stats['pending_verifikasi'] ?? 0 }}</div>
                <div class="info-card-footer warning">
                    <i class="feather icon-hourglass info-card-footer-icon"></i>
                    <span class="info-card-footer-text">Menunggu verifikasi</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-card-icon success">
                        <i class="feather icon-check-circle"></i>
                    </div>
                    <div class="info-card-title success">Terverifikasi</div>
                </div>
                <div class="info-card-value">{{ $stats['terverifikasi'] ?? 0 }}</div>
                <div class="info-card-footer success">
                    <i class="feather icon-check info-card-footer-icon"></i>
                    <span class="info-card-footer-text">Jurnal terverifikasi</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-card-icon info">
                        <i class="feather icon-edit"></i>
                    </div>
                    <div class="info-card-title info">Draft</div>
                </div>
                <div class="info-card-value">{{ $stats['draft'] ?? 0 }}</div>
                <div class="info-card-footer info">
                    <i class="feather icon-file info-card-footer-icon"></i>
                    <span class="info-card-footer-text">Jurnal draft</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



