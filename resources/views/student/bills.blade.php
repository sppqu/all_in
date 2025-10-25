@extends('layouts.student')

@section('title', 'Tagihan Siswa')

@section('content')
<style>
    /* Custom green theme for radio buttons */
    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
    
    .form-check-input:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }
    
    .form-check-input:checked:focus {
        background-color: #198754;
        border-color: #198754;
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }
    
    /* Smaller font sizes for bill titles */
    .card h5.fw-bold {
        font-size: 0.9rem !important;
    }
    
    .card h5.fw-bold small {
        font-size: 0.8rem !important;
    }
    
    /* Smaller font sizes for modal titles and labels */
    .modal-title.fw-bold {
        font-size: 0.9rem !important;
    }
    
    .modal .form-label.fw-bold {
        font-size: 0.8rem !important;
    }
    
    .modal .form-label {
        font-size: 0.8rem !important;
    }
</style>

<div class="container-fluid">
    <!-- Bill Type Selection Card -->
    <div class="card mb-4" style="border-radius: 15px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Pilih Tipe Tagihan</h5>
                <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            <hr class="mb-4">
            <div class="d-flex gap-4">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="billType" id="bulanan" value="bulanan" {{ $defaultType == 'bulanan' ? 'checked' : '' }} onchange="switchBillType('bulanan')">
                    <label class="form-check-label text-muted" for="bulanan">
                        Bulanan
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="billType" id="bebas" value="bebas" {{ $defaultType == 'bebas' ? 'checked' : '' }} onchange="switchBillType('bebas')">
                    <label class="form-check-label text-muted" for="bebas">
                        Non Bulanan
                    </label>
                </div>
        </div>
        </div>
    </div>

    <!-- Bills List Card -->
    <div class="card mb-5" style="border-radius: 15px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-4" id="billTitle">
                @if($defaultType == 'bulanan')
                    @if($selectedMonth)
                        <small class="text-muted">Hingga {{ $months[$selectedMonth] ?? 'Bulan ' . $selectedMonth }} {{ $selectedYear }}</small>
                    @endif
                @else
                    <small class="text-muted"></small>
                @endif
            </h5>
            
            <!-- Pending Payment Notice -->
            @php
                $pendingCount = 0;
                if($defaultType == 'bulanan') {
                    $pendingCount = DB::table('transfer as t')
                        ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
                        ->join('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
                        ->where('t.student_id', session('student_id'))
                        ->where('t.status', 0)
                        ->where('td.payment_type', 1)
                        ->count();
                } else {
                    $pendingCount = DB::table('transfer as t')
                        ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
                        ->join('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
                        ->where('t.student_id', session('student_id'))
                        ->where('t.status', 0)
                        ->where('td.payment_type', 2)
                        ->count();
                }
            @endphp
            
            @if($pendingCount > 0)
                <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock me-2"></i>
                        <div>
                            <strong>Pembayaran Menunggu Verifikasi!</strong><br>
                            <small>Anda memiliki {{ $pendingCount }} pembayaran yang sedang menunggu verifikasi admin. 
                            Item tersebut tidak dapat dipilih lagi sampai verifikasi selesai.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <!-- Bulanan Bills -->
            <div id="bulananBills" class="bill-section" style="display: {{ $defaultType == 'bulanan' ? 'block' : 'none' }};">
                @if($bulananBills->count() > 0)
                    @foreach($bulananBills as $bill)
                        <div class="bill-item d-flex justify-content-between align-items-start py-3" style="border-bottom: 1px solid #e9ecef;" data-bill-type="bulanan" data-bill-id="{{ $bill->bulan_id }}">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold bill-name" style="font-size: 0.75rem;">{{ $bill->pos_name }} - {{ $bill->period_start ?? '2025' }}/{{ $bill->period_end ?? '2026' }}</h6>
                                    <small class="text-muted bill-month">
                                        @php
                                            $months = [
                                                1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober',
                                                5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
                                                9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
                                            ];
                                            $monthName = $months[$bill->month_month_id] ?? 'Bulan ' . $bill->month_month_id;
                                        @endphp
                                        {{ $monthName }} 2025
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success bill-amount">Rp {{ number_format($bill->bulan_bill, 0, ',', '.') }}</div>
                                <small class="text-muted d-block mb-2">Belum dibayar</small>
                                <button class="btn btn-success btn-sm select-btn">
                                    <i class="fas fa-check me-1"></i>Pilih
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">Tidak ada data tagihan</p>
                    </div>
                @endif
            </div>

            <!-- Bebas Bills -->
            <div id="bebasBills" class="bill-section" style="display: {{ $defaultType == 'bebas' ? 'block' : 'none' }};">

                
                @if($bebasBills->count() > 0)
                    @foreach($bebasBills as $bill)
                        <div class="bill-item d-flex justify-content-between align-items-start py-3" style="border-bottom: 1px solid #e9ecef;" 
                             data-bill-type="bebas" data-bill-id="{{ $bill->bebas_id }}"
                             data-total-bill="{{ $bill->bebas_bill }}" 
                             data-paid-amount="{{ $bill->bebas_total_pay ?? 0 }}">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold bill-name" style="font-size: 0.75rem;">{{ $bill->pos_name }} - {{ $bill->period_start ?? '2025' }}/{{ $bill->period_end ?? '2026' }}</h6>
                                    <small class="text-muted bill-month">
                                        {{ $bill->bebas_desc ?? '' }}
                                    </small>
                                    @if($bill->bebas_total_pay > 0)
                                        <small class="text-success d-block">
                                            <i class="fas fa-info-circle me-1"></i>Pembayaran bertahap
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                @php
                                    $totalBill = $bill->bebas_bill;
                                    $totalPaid = $bill->bebas_total_pay ?? 0;
                                    $remainingAmount = $totalBill - $totalPaid;
                                @endphp
                                <div class="fw-bold text-success bill-amount">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</div>
                                @if($totalPaid > 0)
                                    <small class="text-muted d-block">Total: Rp {{ number_format($totalBill, 0, ',', '.') }}</small>
                                    <small class="text-success d-block">Dibayar: Rp {{ number_format($totalPaid, 0, ',', '.') }}</small>
                                @else
                                    <small class="text-muted d-block mb-2">Belum dibayar</small>
                                @endif
                                <button class="btn btn-success btn-sm select-btn">
                                    <i class="fas fa-check me-1"></i>Pilih
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">Semua tagihan non bulanan sudah lunas!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="filterModalLabel">
                        <i class="fas fa-filter me-2"></i>Filter Biaya Bulanan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="filterForm" method="GET" action="{{ route('student.bills') }}">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hingga</label>
                                </div>
                        
                        <div class="mb-3">
                            <label for="month" class="form-label">Bulan</label>
                            <select class="form-select" id="month" name="month" required>
                                <option value="1" {{ $selectedMonth == 1 ? 'selected' : '' }}>Juli</option>
                                <option value="2" {{ $selectedMonth == 2 ? 'selected' : '' }}>Agustus</option>
                                <option value="3" {{ $selectedMonth == 3 ? 'selected' : '' }}>September</option>
                                <option value="4" {{ $selectedMonth == 4 ? 'selected' : '' }}>Oktober</option>
                                <option value="5" {{ $selectedMonth == 5 ? 'selected' : '' }}>November</option>
                                <option value="6" {{ $selectedMonth == 6 ? 'selected' : '' }}>Desember</option>
                                <option value="7" {{ $selectedMonth == 7 ? 'selected' : '' }}>Januari</option>
                                <option value="8" {{ $selectedMonth == 8 ? 'selected' : '' }}>Februari</option>
                                <option value="9" {{ $selectedMonth == 9 ? 'selected' : '' }}>Maret</option>
                                <option value="10" {{ $selectedMonth == 10 ? 'selected' : '' }}>April</option>
                                <option value="11" {{ $selectedMonth == 11 ? 'selected' : '' }}>Mei</option>
                                <option value="12" {{ $selectedMonth == 12 ? 'selected' : '' }}>Juni</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="year" class="form-label">Tahun</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" onclick="changeYear(-1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <input type="number" class="form-control text-center" id="year" name="year" value="{{ $selectedYear }}" min="2020" max="2030" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="changeYear(1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                                </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100" style="border-radius: 10px;">
                            <i class="fas fa-check me-2"></i>Terapkan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Amount Modal -->
<div class="modal fade" id="paymentAmountModal" tabindex="-1" aria-labelledby="paymentAmountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; border: none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="paymentAmountModalLabel">
                    <span id="modalBillTitle">K-PENUNJANG KBM TP 2025/2026</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <p class="text-muted mb-2">Tagihan: <span id="modalBillAmount" class="fw-bold">Rp 0</span></p>
                </div>
                <div class="mb-3">
                    <label for="paymentAmount" class="form-label fw-bold">Ingin Dibayar Rp :</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="paymentAmount" placeholder="Masukkan nominal pembayaran" style="border-color: #198754;">
                        <div class="input-group-append">
                            <button class="btn btn-outline-success" type="button" onclick="setFullAmount()">Lunas <span id="fullAmountText">Rp 0</span></button>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button class="btn btn-outline-success btn-sm" type="button" onclick="setMinimumAmount()">Minimum Rp 10.000</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-success" data-bs-dismiss="modal">BATAL</button>
                <button type="button" class="btn btn-success" onclick="confirmAmount()">OKE</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .form-check-input {
        width: 20px;
        height: 20px;
        border: 2px solid #dee2e6;
        border-radius: 50%;
        cursor: pointer;
    }
    
    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
    
    .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
    }
    
    .form-check-label {
        cursor: pointer;
        font-weight: 500;
    }
    
    .bill-item {
        transition: all 0.3s ease;
    }
    
    .bill-item:hover {
        background-color: #f8f9fa;
        border-radius: 10px;
        margin: 0 -10px;
        padding-left: 20px !important;
        padding-right: 20px !important;
    }
    
    .bill-item:last-child {
        border-bottom: none !important;
    }
    
    .fixed-bottom {
        z-index: 1030;
    }
    
    /* Mobile optimizations */
    @media (max-width: 768px) {
        .card-body {
            padding: 20px 15px;
    }
    
        .bill-item {
            padding: 15px 0 !important;
        }
        
        .fixed-bottom {
            padding: 12px 15px !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let currentBillType = '{{ $defaultType }}';

    function switchBillType(type) {
        currentBillType = type;
        
        // Hide all bill sections
        document.querySelectorAll('.bill-section').forEach(section => {
            section.style.display = 'none';
        });
        
        // Show selected bill section
        document.getElementById(type + 'Bills').style.display = 'block';
        
        // Update title
        if (type === 'bulanan') {
            document.getElementById('billTitle').textContent = '';
        } else {
            document.getElementById('billTitle').textContent = '';
        }
    }

    function changeYear(increment) {
        const yearInput = document.getElementById('year');
        const currentYear = parseInt(yearInput.value);
        const newYear = currentYear + increment;
        
        // Validate year range
        if (newYear >= 2020 && newYear <= 2030) {
            yearInput.value = newYear;
        }
    }

    function selectBill(type, billId) {
        console.log('selectBill called:', type, billId); // Debug log
        console.log('selectBill function exists:', typeof selectBill); // Debug log
        
        if (type === 'bebas') {
            // For non-bulanan, show payment amount modal
            showPaymentAmountModal(type, billId);
        } else {
            // For bulanan, add directly to cart
            addToCart(type, billId);
        }
    }
    
    function showPaymentAmountModal(type, billId) {
        // Get bill details
        const billItem = document.querySelector(`[data-bill-type="${type}"][data-bill-id="${billId}"]`);
        
        if (!billItem) {
            console.log('Bill item not found!');
            return;
        }

        const billNameElement = billItem.querySelector('.bill-name');
        const billAmountElement = billItem.querySelector('.bill-amount');
        
        if (!billNameElement || !billAmountElement) {
            console.error('Required elements not found!');
            return;
        }

        const billName = billNameElement.textContent;
        const billAmount = billAmountElement.textContent;
        
        // Set modal content
        document.getElementById('modalBillTitle').textContent = billName;
        document.getElementById('modalBillAmount').textContent = billAmount;
        document.getElementById('fullAmountText').textContent = billAmount;
        
        // Set minimum amount in input
        document.getElementById('paymentAmount').value = 10000;
        
        // Store current bill info for confirmation
        window.currentBillInfo = { type, billId, billName, billAmount };
        
        // Open modal
        const modal = new bootstrap.Modal(document.getElementById('paymentAmountModal'));
        modal.show();
    }

    function addToCart(type, billId) {
        console.log('addToCart called:', type, billId); // Debug log
        console.log('addToCart function exists:', typeof addToCart); // Debug log
        
        // Get bill details
        const billItem = document.querySelector(`[data-bill-type="${type}"][data-bill-id="${billId}"]`);
        console.log('billItem found:', billItem); // Debug log
        
        if (!billItem) {
            console.log('Bill item not found!'); // Debug log
            return;
        }

        const billNameElement = billItem.querySelector('.bill-name');
        const billAmountElement = billItem.querySelector('.bill-amount');
        const billMonthElement = billItem.querySelector('.bill-month');
        
        console.log('Elements found:', { billNameElement, billAmountElement, billMonthElement }); // Debug log
        
        if (!billNameElement || !billAmountElement || !billMonthElement) {
            console.error('Required elements not found!');
            return;
        }

        const billName = billNameElement.textContent;
        const billAmount = billAmountElement.textContent;
        const billMonth = billMonthElement.textContent;
        
        console.log('Bill details:', { billName, billAmount, billMonth }); // Debug log

        // Create cart item object
        const cartItem = {
            type: type,
            id: billId,
            name: billName,
            amount: billAmount,
            month: billMonth
        };
        
        // For bebas bills, add additional information for remaining amount
        if (type === 'bebas') {
            // Get the remaining amount from the bill amount (which shows remaining)
            const remainingAmount = parseInt(billAmount.replace(/[^\d]/g, ''));
            
            // Try to get total bill and paid amount from data attributes or calculate
            const totalBillElement = billItem.querySelector('[data-total-bill]');
            const paidAmountElement = billItem.querySelector('[data-paid-amount]');
            
            console.log('Adding bebas bill to cart:', { 
                billAmount, 
                remainingAmount, 
                totalBillElement: !!totalBillElement, 
                paidAmountElement: !!paidAmountElement 
            });
            
            if (totalBillElement && paidAmountElement) {
                const totalBill = parseInt(totalBillElement.getAttribute('data-total-bill'));
                const paidAmount = parseInt(paidAmountElement.getAttribute('data-paid-amount'));
                
                cartItem.totalBill = totalBill;
                cartItem.paidAmount = paidAmount;
                cartItem.remainingAmount = remainingAmount;
                
                console.log('Bebas bill details:', { totalBill, paidAmount, remainingAmount });
            } else {
                // If data attributes not available, use the amount as remaining
                cartItem.remainingAmount = remainingAmount;
                console.log('Using amount as remaining for bebas bill:', remainingAmount);
            }
        }

        // Get existing cart from localStorage
        let cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
        console.log('Current cart:', cart); // Debug log
        
        // Check if item already exists in cart
        const existingItem = cart.find(item => item.type === type && item.id == billId);
        if (existingItem) {
            console.log('Item already in cart!'); // Debug log
            // Show alert that item is already in cart
            showAlert('Item sudah ada di keranjang!', 'warning');
            return;
        }

        // Add item to cart
        cart.push(cartItem);
        localStorage.setItem('studentCart', JSON.stringify(cart));
        console.log('Item added to cart:', cart); // Debug log

        // Update cart badge
        updateCartBadge();
        
        // Show success message
        showAlert('Item berhasil ditambahkan ke keranjang!', 'success');
        
        // Change button to show "Sudah Dipilih"
        const button = billItem.querySelector('.select-btn');
        button.innerHTML = '<i class="fas fa-check me-1"></i>Sudah Dipilih';
        button.classList.remove('btn-success', 'btn-info');
        button.classList.add('btn-secondary');
        button.disabled = true;
    }

    // Use global updateCartBadge function from layout

    function showAlert(message, type) {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 8px;';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 3000);
    }

    // Initialize cart badge on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing cart...'); // Debug log
        updateCartBadge();
        
        // Add click event listeners to all select buttons
        const selectButtons = document.querySelectorAll('.select-btn');
        console.log('Found select buttons:', selectButtons.length); // Debug log
        
        selectButtons.forEach((button, index) => {
            console.log(`Adding event listener to button ${index}:`, button); // Debug log
            
            button.addEventListener('click', function(e) {
                console.log('Button clicked!'); // Debug log
                e.preventDefault();
                e.stopPropagation();
                
                try {
                    const billItem = this.closest('.bill-item');
                    console.log('Bill item found:', billItem); // Debug log
                    
                    if (!billItem) {
                        console.error('Bill item not found');
                        return;
                    }
                    
                    const type = billItem.getAttribute('data-bill-type');
                    const billId = billItem.getAttribute('data-bill-id');
                    
                    console.log('Button clicked:', type, billId); // Debug log
                    
                    if (type && billId) {
                        console.log('Calling selectBill function...'); // Debug log
                        selectBill(type, billId);
                    } else {
                        console.error('Type or billId not found:', { type, billId });
                    }
                } catch (error) {
                    console.error('Error in button click handler:', error);
                }
            });
            
            console.log(`Event listener added to button ${index}`); // Debug log
        });
        
        // Mark already selected items
        const cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
        cart.forEach(item => {
            const billItem = document.querySelector(`[data-bill-type="${item.type}"][data-bill-id="${item.id}"]`);
            if (billItem) {
                const button = billItem.querySelector('.select-btn');
                if (button) {
                    button.innerHTML = '<i class="fas fa-check me-1"></i>Sudah Dipilih';
                    button.classList.remove('btn-success', 'btn-info');
                    button.classList.add('btn-secondary');
                    button.disabled = true;
                }
            }
        });
    });
    
    function setFullAmount() {
        const fullAmount = document.getElementById('fullAmountText').textContent;
        const numericAmount = parseInt(fullAmount.replace(/[^\d]/g, ''));
        document.getElementById('paymentAmount').value = numericAmount;
    }
    
    function setMinimumAmount() {
        document.getElementById('paymentAmount').value = 10000;
    }
    
    function confirmAmount() {
        const amount = parseInt(document.getElementById('paymentAmount').value);
        
        if (!amount || amount < 10000) {
            showAlert('Minimal pembayaran Rp 10.000!', 'warning');
            return;
        }
        
        if (window.currentBillInfo) {
            // Create cart item with custom amount
            const cartItem = {
                type: window.currentBillInfo.type,
                id: window.currentBillInfo.billId,
                name: window.currentBillInfo.billName,
                amount: `Rp ${amount.toLocaleString('id-ID')}`,
                month: 'Non Bulanan'
            };
            
            // Get existing cart from localStorage
            let cart = JSON.parse(localStorage.getItem('studentCart') || '[]');
            
            // Check if item already exists in cart
            const existingItem = cart.find(item => item.type === window.currentBillInfo.type && item.id == window.currentBillInfo.billId);
            if (existingItem) {
                showAlert('Item sudah ada di keranjang!', 'warning');
                return;
            }
            
            // Add item to cart
            cart.push(cartItem);
            localStorage.setItem('studentCart', JSON.stringify(cart));
            
            // Update cart badge
            updateCartBadge();
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentAmountModal'));
            modal.hide();
            
            // Show success message
            showAlert('Tagihan berhasil ditambahkan ke keranjang!', 'success');
            
            // Update button to show "Sudah Dipilih"
            const billItem = document.querySelector(`[data-bill-type="${window.currentBillInfo.type}"][data-bill-id="${window.currentBillInfo.billId}"]`);
            if (billItem) {
                const button = billItem.querySelector('.select-btn');
                if (button) {
                    button.innerHTML = '<i class="fas fa-check me-1"></i>Sudah Dipilih';
                    button.classList.remove('btn-success', 'btn-info');
                    button.classList.add('btn-secondary');
                    button.disabled = true;
                }
            }
            
            // Clear current bill info
            window.currentBillInfo = null;
        }
    }
</script>
@endpush 