@extends('layouts.student')

@section('title', 'Profile Siswa')

@section('content')
<div class="container-fluid">
    <!-- Profile Card -->
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg profile-card" style="border-radius: 20px; margin: 0 10px;">
                <div class="card-body text-center p-5">
                    <!-- Profile Avatar -->
                    <div class="profile-avatar mb-4">
                        <div class="avatar-circle">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>

                    <!-- Student Name -->
                    <h3 class="student-name mb-3">{{ $student->student_full_name }}</h3>

                    <!-- Student Info -->
                    <div class="student-info mb-4">
                        <div class="info-item">
                            <span class="info-label">NIS:</span>
                            <span class="info-value">{{ $student->student_nis }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Kelas:</span>
                            <span class="info-value">{{ $student->class ? $student->class->class_name : '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">No.HP/WA:</span>
                            <span class="info-value">{{ $student->student_parent_phone ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="status-badge">
                        <span class="badge bg-success bg-opacity-10 text-success px-4 py-2">
                            <i class="fas fa-check-circle me-2"></i>Aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Card -->
    <div class="row justify-content-center mt-4">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm password-card" style="border-radius: 15px; margin: 0 10px;">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-key me-2 text-primary"></i>Ubah Password
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('student.update-password') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-bold">Password Saat Ini</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 @error('current_password') is-invalid @enderror" 
                                       id="current_password" name="current_password" placeholder="Masukkan password saat ini" required>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-bold">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-key text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 @error('new_password') is-invalid @enderror" 
                                       id="new_password" name="new_password" placeholder="Masukkan password baru" required>
                            </div>
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="new_password_confirmation" class="form-label fw-bold">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-check text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0" 
                                       id="new_password_confirmation" name="new_password_confirmation" placeholder="Konfirmasi password baru" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold" style="border-radius: 12px;">
                            <i class="fas fa-save me-2"></i>Ubah Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Profile Card Styling */
.profile-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 1px solid #e9ecef;
    width: 100%;
    max-width: 100%;
}

.profile-avatar {
    margin-bottom: 2rem;
}

.avatar-circle {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #198754, #20c997);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: 0 8px 25px rgba(25, 135, 84, 0.3);
}

.avatar-circle i {
    font-size: 2rem;
    color: white;
}

.student-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #198754;
    margin-bottom: 1.5rem;
}

.student-info {
    text-align: center;
}

.info-item {
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: rgba(25, 135, 84, 0.05);
    border-radius: 10px;
    border-left: 4px solid #198754;
}

.info-label {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.9rem;
    display: block;
    margin-bottom: 0.25rem;
}

.info-value {
    font-weight: 700;
    color: #212529;
    font-size: 1rem;
}

.status-badge {
    margin-top: 2rem;
}

.status-badge .badge {
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 20px;
    padding: 0.75rem 1.5rem;
}

/* Password Card Styling */
.password-card {
    background: white;
    border: 1px solid #e9ecef;
    width: 100%;
    max-width: 100%;
}

.password-card .card-header {
    padding: 1.5rem 1.5rem 0;
}

.password-card .card-header h5 {
    color: #198754;
    font-size: 1.1rem;
}

.input-group-text {
    border: 1px solid #dee2e6;
    background-color: #f8f9fa;
}

.form-control {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
}

.form-control:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

.form-label {
    color: #495057;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #198754, #20c997);
    border: none;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(25, 135, 84, 0.3);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .container-fluid {
        padding: 12px;
    }
    
    .avatar-circle {
        width: 70px;
        height: 70px;
    }
    
    .avatar-circle i {
        font-size: 1.75rem;
    }
    
    .student-name {
        font-size: 1.3rem;
    }
    
    .info-value {
        font-size: 0.9rem;
    }
    
    .status-badge .badge {
        font-size: 0.8rem;
        padding: 0.6rem 1.2rem;
    }
    
    .password-card .card-header {
        padding: 1rem 1rem 0;
    }
    
    .password-card .card-body {
        padding: 1rem;
    }
    
    .form-control {
        font-size: 0.85rem;
        padding: 0.6rem 0.8rem;
    }
    
    .btn-primary {
        font-size: 0.9rem;
        padding: 0.75rem 1rem;
    }
}

/* Animation */
.profile-card {
    animation: slideInUp 0.6s ease-out;
}

.password-card {
    animation: slideInUp 0.6s ease-out 0.2s both;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endsection 