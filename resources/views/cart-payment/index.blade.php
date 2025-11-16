@extends('layouts.adminty')

@section('title', 'Cart Payment Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Cart Payment Management</h4>
                    <p class="card-subtitle">Kelola pembayaran cart dan integrasikan dengan sistem tagihan</p>
                </div>
                <div class="card-body">
                    <!-- Student Selection -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="student_id" class="form-label">Pilih Siswa</label>
                            <select class="form-select" id="student_id" name="student_id">
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->student_id }}">{{ $student->student_full_name }} - {{ $student->class->class_name ?? 'N/A' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-primary" id="loadCartItems">
                                    <i class="fas fa-shopping-cart"></i> Load Cart Items
                                </button>
                                <button type="button" class="btn btn-warning" id="updateExistingTransfers">
                                    <i class="fas fa-sync"></i> Update Existing Transfers
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Items Display -->
                    <div id="cartItemsContainer" style="display: none;">
                        <h5>Cart Items</h5>
                        <div class="table-responsive">
                            <table class="table table-striped" id="cartItemsTable">
                                <thead>
                                    <tr>
                                        <th>Jenis Tagihan</th>
                                        <th>Nama POS</th>
                                        <th>Bulan</th>
                                        <th>Jumlah</th>
                                        <th>Deskripsi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="cartItemsBody">
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_method" class="form-label">Metode Pembayaran</label>
                                    <select class="form-select" id="payment_method" name="payment_method">
                                        <option value="midtrans">Midtrans</option>
                                        <option value="tripay">Tripay</option>
                                        <option value="payment_gateway">Payment Gateway</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gateway_transaction_id" class="form-label">Gateway Transaction ID (Opsional)</label>
                                    <input type="text" class="form-control" id="gateway_transaction_id" name="gateway_transaction_id" placeholder="ID transaksi dari gateway">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-success" id="processPayment">
                                    <i class="fas fa-credit-card"></i> Proses Pembayaran
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Results Display -->
                    <div id="resultsContainer" style="display: none;">
                        <h5>Hasil Proses</h5>
                        <div id="resultsContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memproses...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let cartItems = [];
    
    // Load cart items for selected student
    $('#loadCartItems').click(function() {
        const studentId = $('#student_id').val();
        
        if (!studentId) {
            Swal.fire('Error', 'Silakan pilih siswa terlebih dahulu', 'error');
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: `/cart-payment/items/${studentId}`,
            method: 'GET',
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    cartItems = response.data;
                    displayCartItems(cartItems);
                    $('#cartItemsContainer').show();
                } else {
                    Swal.fire('Error', response.message || 'Gagal memuat cart items', 'error');
                }
            },
            error: function(xhr) {
                hideLoading();
                Swal.fire('Error', 'Gagal memuat cart items', 'error');
            }
        });
    });
    
    // Display cart items in table
    function displayCartItems(items) {
        const tbody = $('#cartItemsBody');
        tbody.empty();
        
        if (items.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center">Tidak ada tagihan yang belum dibayar</td></tr>');
            return;
        }
        
        items.forEach(function(item, index) {
            const row = `
                <tr>
                    <td>
                        <span class="badge ${item.bill_type === 'bulanan' ? 'bg-primary' : 'bg-success'}">
                            ${item.bill_type === 'bulanan' ? 'Bulanan' : 'Bebas'}
                        </span>
                    </td>
                    <td>${item.pos_name}</td>
                    <td>${item.month_name || '-'}</td>
                    <td>Rp ${Number(item.amount).toLocaleString('id-ID')}</td>
                    <td>${item.description}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeCartItem(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    // Remove item from cart
    window.removeCartItem = function(index) {
        cartItems.splice(index, 1);
        displayCartItems(cartItems);
        
        if (cartItems.length === 0) {
            $('#cartItemsContainer').hide();
        }
    };
    
    // Process payment
    $('#processPayment').click(function() {
        if (cartItems.length === 0) {
            Swal.fire('Error', 'Tidak ada item dalam cart', 'error');
            return;
        }
        
        const paymentMethod = $('#payment_method').val();
        const gatewayTransactionId = $('#gateway_transaction_id').val();
        
        if (!paymentMethod) {
            Swal.fire('Error', 'Silakan pilih metode pembayaran', 'error');
            return;
        }
        
        showLoading();
        
        const requestData = {
            student_id: $('#student_id').val(),
            payment_method: paymentMethod,
            cart_items: cartItems,
            gateway_transaction_id: gatewayTransactionId,
            total_amount: cartItems.reduce((sum, item) => sum + parseFloat(item.amount), 0)
        };
        
        $.ajax({
            url: '/cart-payment/process',
            method: 'POST',
            data: requestData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    displayResults(response.data);
                    Swal.fire('Success', 'Pembayaran berhasil diproses', 'success');
                    
                    // Clear cart
                    cartItems = [];
                    $('#cartItemsContainer').hide();
                    $('#resultsContainer').show();
                } else {
                    Swal.fire('Error', response.message || 'Gagal memproses pembayaran', 'error');
                }
            },
            error: function(xhr) {
                hideLoading();
                Swal.fire('Error', 'Gagal memproses pembayaran', 'error');
            }
        });
    });
    
    // Update existing transfers
    $('#updateExistingTransfers').click(function() {
        Swal.fire({
            title: 'Update Existing Transfers?',
            text: 'Ini akan mengupdate transaksi transfer yang sudah ada dengan informasi bill yang sesuai. Lanjutkan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Update',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                
                $.ajax({
                    url: '/cart-payment/update-existing',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        hideLoading();
                        
                        if (response.success) {
                            Swal.fire('Success', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message || 'Gagal mengupdate existing transfers', 'error');
                        }
                    },
                    error: function(xhr) {
                        hideLoading();
                        Swal.fire('Error', 'Gagal mengupdate existing transfers', 'error');
                    }
                });
            }
        });
    });
    
    // Display results
    function displayResults(results) {
        const container = $('#resultsContent');
        let html = '<div class="table-responsive"><table class="table table-striped">';
        html += '<thead><tr><th>Item</th><th>Status</th><th>Message</th></tr></thead><tbody>';
        
        results.forEach(function(result, index) {
            html += `
                <tr>
                    <td>Item ${index + 1}</td>
                    <td>
                        <span class="badge ${result.success ? 'bg-success' : 'bg-danger'}">
                            ${result.success ? 'Success' : 'Failed'}
                        </span>
                    </td>
                    <td>${result.message}</td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        container.html(html);
        $('#resultsContainer').show();
    }
    
    // Show/hide loading
    function showLoading() {
        $('#loadingModal').modal('show');
    }
    
    function hideLoading() {
        $('#loadingModal').modal('hide');
    }
});
</script>
@endpush
