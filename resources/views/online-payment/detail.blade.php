<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3">Informasi Pembayaran</h6>
        <table class="table table-borderless">
            <tr>
                <td width="140"><strong>Nomor Pembayaran:</strong></td>
                <td>{{ $transfer->reference }}</td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>
                    <span class="badge {{ \App\Helpers\TransferStatusHelper::getTransferStatusBadge($transfer->status) }}">
                        {{ \App\Helpers\TransferStatusHelper::getTransferStatusText($transfer->status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Metode Pembayaran:</strong></td>
                <td>Transfer Bank Manual</td>
            </tr>

            @if($transfer->confirm_photo)
                <tr>
                    <td><strong>Bukti Transfer:</strong></td>
                    <td>
                        @php
                            $imagePath = 'storage/' . $transfer->confirm_photo;
                            $fullPath = public_path($imagePath);
                            $imageExists = file_exists($fullPath);
                            
                            // Check for double extension issue (e.g., .jpg.jpg)
                            if (!$imageExists && $transfer->confirm_photo) {
                                $pathInfo = pathinfo($transfer->confirm_photo);
                                $doubleExtensionPath = 'storage/' . $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.' . $pathInfo['extension'] . '.' . $pathInfo['extension'];
                                $doubleExtensionFullPath = public_path($doubleExtensionPath);
                                if (file_exists($doubleExtensionFullPath)) {
                                    $imagePath = $doubleExtensionPath;
                                    $fullPath = $doubleExtensionFullPath;
                                    $imageExists = true;
                                }
                            }
                        @endphp
                        
                        @if($imageExists)
                            <button type="button" class="btn btn-sm btn-outline-primary proof-image-btn" 
                                    data-image-url="{{ asset($imagePath) }}"
                                    onclick="viewProofImage('{{ asset($imagePath) }}', {{ $transfer->transfer_id }})">
                                <i class="fas fa-eye me-1"></i>Lihat Bukti
                            </button>
                            <a href="{{ asset($imagePath) }}" 
                               target="_blank" class="btn btn-sm btn-outline-secondary ms-1">
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                        @else
                            <span class="text-muted">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                File bukti transfer tidak ditemukan
                            </span>
                        @endif
                    </td>
                </tr>
            @endif
            @if($transfer->detail)
                <tr>
                    <td><strong>Catatan:</strong></td>
                    <td>{{ $transfer->detail }}</td>
                </tr>
            @endif

            <tr>
                <td><strong>Tanggal Dibuat:</strong></td>
                <td>{{ \Carbon\Carbon::parse($transfer->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
            @if($transfer->verif_date)
                <tr>
                    <td><strong>Tanggal Verifikasi:</strong></td>
                    <td>{{ \Carbon\Carbon::parse($transfer->verif_date)->format('d/m/Y H:i') }}</td>
                </tr>
            @endif
        </table>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary mb-3">Informasi Siswa</h6>
        <table class="table table-borderless">
            <tr>
                <td width="140"><strong>NIS:</strong></td>
                <td>{{ $transfer->student_nis }}</td>
            </tr>
            <tr>
                <td><strong>Nama:</strong></td>
                <td>{{ $transfer->student_full_name }}</td>
            </tr>
            <tr>
                <td><strong>Kelas:</strong></td>
                <td>{{ $transfer->class_name ?? 'Kelas tidak ditemukan' }}</td>
            </tr>
        </table>
    </div>
</div>

@if($transferDetails->count() > 0)
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="text-primary mb-3">Detail Tagihan</h6>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Jenis</th>
                            <th>Nama Tagihan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transferDetails as $detail)
                            <tr>
                                <td>
                                    @if($detail->payment_type == 1)
                                        <span class="badge bg-primary">Bulanan</span>
                                    @elseif($detail->payment_type == 2)
                                        <span class="badge bg-info">Bebas</span>
                                    @elseif($detail->payment_type == 3 && $detail->is_tabungan == 1)
                                        <span class="badge bg-success">Tabungan</span>
                                    @else
                                        <span class="badge bg-secondary">Lainnya</span>
                                    @endif
                                </td>
                                <td>
                                    @if($detail->payment_type == 1)
                                        {{ $detail->pos_name ?? 'N/A' }} - {{ $detail->month_name ?? 'N/A' }}
                                    @elseif($detail->payment_type == 3 && $detail->is_tabungan == 1)
                                        {{ $detail->desc ?? 'Setor Tabungan' }}
                                    @else
                                        {{ $detail->pos_name ?? $detail->desc ?? 'N/A' }}
                                    @endif
                                </td>
                                <td>
                                    {{-- Gunakan helper untuk semua jenis pembayaran --}}
                                    <span class="badge {{ \App\Helpers\TransferStatusHelper::getDetailStatusBadge($detail->payment_type, $detail->is_tabungan ?? 0, $transfer->status) }}">
                                        {{ \App\Helpers\TransferStatusHelper::getDetailStatusText($detail->payment_type, $detail->is_tabungan ?? 0, $transfer->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<div class="row mt-4">
    <div class="col-12 text-center">
        @if($transfer->status == 1)
            <button type="button" class="btn btn-success me-2" onclick="downloadReceipt({{ $transfer->transfer_id }})" style="color: white;">
                <i class="fas fa-download me-2" style="color: white;"></i>Cetak Kuitansi
            </button>
        @endif
        
        @if($transfer->status == 0)
            <button type="button" class="btn btn-success me-2" data-action="approve" data-payment-id="{{ $transfer->transfer_id }}" style="color: white;">
                <i class="fas fa-check me-2" style="color: white;"></i>Verifikasi
            </button>
            <button type="button" class="btn btn-danger me-2" data-action="reject" data-payment-id="{{ $transfer->transfer_id }}" style="color: white;">
                <i class="fas fa-times me-2" style="color: white;"></i>Tolak
            </button>
        @elseif($transfer->status == 2)
            <span class="badge bg-danger">Pembayaran Ditolak</span>
        @elseif($transfer->status == 3)
            <span class="badge bg-secondary">Pembayaran Dibatalkan</span>
        @elseif($transfer->status == 4)
            <span class="badge bg-dark">Pembayaran Expired</span>
        @endif
        

    </div>
</div> 