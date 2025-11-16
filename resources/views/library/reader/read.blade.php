@extends(session('is_student') ? 'layouts.student' : 'layouts.adminty')

@section('title', $book->judul . ' - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4" style="height: 100vh; display: flex; flex-direction: column;">
    <!-- Header -->
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ session('is_student') ? route('student.library') : route('library.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
            <div>
                <h5 class="mb-0 fw-bold">{{ $book->judul }}</h5>
                <small class="text-muted">{{ $book->pengarang }}</small>
            </div>
            <div>
                <a href="{{ session('is_student') ? route('student.library.download', $book->id) : route('library.download', $book->id) }}" class="btn btn-primary" target="_blank">
                    <i class="fas fa-download me-2"></i>Download PDF
                </a>
            </div>
        </div>
    </div>

    <!-- PDF Viewer -->
    <div class="flex-grow-1" style="min-height: 0;">
        <iframe 
            src="{{ $pdfUrl }}" 
            style="width: 100%; height: 100%; border: none; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);"
            title="PDF Viewer">
        </iframe>
    </div>
</div>

@push('styles')
<style>
body {
    overflow: hidden;
}

.pcoded-content, .main-body, .page-wrapper, .page-body {
    height: 100% !important;
}

.container-fluid {
    height: 100%;
    display: flex;
    flex-direction: column;
}
</style>
@endpush

@endsection

