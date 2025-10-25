@extends('layouts.coreui')

@section('head')
<title>General Setting</title>
<style>
.tab-pane-setting {
    display: none;
}
#tab-profile.tab-pane-setting {
    display: block;
}
[data-tab] {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
}
[data-tab].active {
    background-color: rgb(14, 109, 42) !important;
    color: white !important;
    border-color: rgb(33, 160, 86) !important;
    z-index: 1;
}
[data-tab]:not(.active) {
    background-color: white;
    color: #6c757d;
    border-color: #dee2e6;
}
[data-tab]:not(.active):hover {
    background-color: #f8f9fa;
    color: #495057;
}
/* Ensure tab content is properly displayed */
.tab-pane-setting[style*="display: block"] {
    display: block !important;
}
.tab-pane-setting[style*="display: none"] {
    display: none !important;
}
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card my-3">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">General Setting</h4>
                </div>
                <div class="card-body">
                    <!-- Alert Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Terjadi kesalahan:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="mb-4">
                        <div class="btn-group w-100" role="group" aria-label="General Setting Tabs" id="settingTabGroup">
                            <button type="button" class="btn btn-outline-success active" data-tab="profile">Profile Sekolah</button>
                            <button type="button" class="btn btn-outline-success" data-tab="rekening">Rekening Bank</button>
                            <button type="button" class="btn btn-outline-success {{ !$hasPaymentGatewayAddon ? 'disabled' : '' }}" data-tab="gateway" {{ !$hasPaymentGatewayAddon ? 'disabled' : '' }}>
                                Payment Gateway
                                @if(!$hasPaymentGatewayAddon)
                                    <i class="fas fa-lock ms-1"></i>
                                @endif
                            </button>
                            <button type="button" class="btn btn-outline-success {{ !$hasWhatsAppGatewayAddon ? 'disabled' : '' }}" data-tab="wa" {{ !$hasWhatsAppGatewayAddon ? 'disabled' : '' }}>
                                WhatsApp Gateway
                                @if(!$hasWhatsAppGatewayAddon)
                                    <i class="fas fa-lock ms-1"></i>
                                @endif
                            </button>
                        </div>
                    </div>

                    <!-- Profile Sekolah Tab -->
                    <div id="tab-profile" class="tab-pane-setting">
                        <div class="row">
                            <div class="col-lg-6">
                                <h5 class="mb-3">Profile Sekolah</h5>
                                <table class="table table-bordered">
                                    <tr><th width="200">Jenjang</th><td>{{ $profile->jenjang ?? '-' }}</td></tr>
                                    <tr><th>Nama Sekolah</th><td>{{ $profile->nama_sekolah ?? '-' }}</td></tr>
                                    <tr><th>Alamat</th><td>{{ $profile->alamat ?? '-' }}</td></tr>
                                    <tr><th>No. Telp</th><td>{{ $profile->no_telp ?? '-' }}</td></tr>
                                    <tr><th>Logo Sekolah</th><td>
                                        @if(!empty($profile->logo_sekolah) && $profile->logo_sekolah !== 'Logo')
                                            <img src="{{ asset('storage/'.$profile->logo_sekolah) }}" alt="Logo" height="60" id="logoPreview">
                                        @else
                                            <span class="text-muted">Belum diupload</span>
                                        @endif
                                    </td></tr>
                                </table>
                            </div>
                            <div class="col-lg-6">
                                <h5 class="mb-3">Edit Profile Sekolah</h5>
                                <form method="POST" action="{{ route('manage.general.setting.update') }}" enctype="multipart/form-data" id="profileForm">
                                    @csrf
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Jenjang</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="jenjang" value="{{ old('jenjang', $profile->jenjang ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Nama Sekolah</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="nama_sekolah" value="{{ old('nama_sekolah', $profile->nama_sekolah ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Alamat</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control" name="alamat" rows="2">{{ old('alamat', $profile->alamat ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">No. Telp</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="no_telp" value="{{ old('no_telp', $profile->no_telp ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Logo Sekolah</label>
                                        <div class="col-sm-9">
                                            <input type="file" class="form-control" name="logo_sekolah" id="logoInput">
                                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah logo.</small>
                                            <div class="mt-2">
                                                <img id="logoPreview" src="" alt="Preview" style="max-height: 100px; display: none;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-9 offset-sm-3">
                                            <button type="submit" class="btn btn-success text-white">Simpan Profile</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Rekening Bank Tab -->
                    <div id="tab-rekening" class="tab-pane-setting">
                        <div class="row">
                            <div class="col-lg-6">
                                <h5 class="mb-3">Info Rekening Bank Sekolah</h5>
                                <table class="table table-bordered">
                                    <tr><th width="200">No. Rekening</th><td>{{ $gateway->norek_bank ?? '-' }}</td></tr>
                                    <tr><th>Nama Bank</th><td>{{ $gateway->nama_bank ?? '-' }}</td></tr>
                                    <tr><th>Nama Rekening</th><td>{{ $gateway->nama_rekening ?? '-' }}</td></tr>
                                </table>
                            </div>
                            <div class="col-lg-6">
                                <h5 class="mb-3">Edit Rekening Bank</h5>
                                <form method="POST" action="{{ route('manage.general.setting.rekening') }}">
                                    @csrf
                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label">No. Rekening</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="norek_bank" value="{{ old('norek_bank', $gateway->norek_bank ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label">Nama Bank</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="nama_bank" value="{{ old('nama_bank', $gateway->nama_bank ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label">Nama Rekening</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="nama_rekening" value="{{ old('nama_rekening', $gateway->nama_rekening ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-8 offset-sm-4">
                                            <button type="submit" class="btn btn-success text-white">Simpan Rekening</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Gateway Tab -->
                    <div id="tab-gateway" class="tab-pane-setting">
                        @if(!$hasPaymentGatewayAddon)
                            <div class="alert alert-warning" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h5 class="alert-heading mb-2">Payment Gateway Add-on Belum Dimiliki</h5>
                                        <p class="mb-3">Untuk mengakses pengaturan Payment Gateway, Anda perlu membeli add-on Payment Gateway terlebih dahulu.</p>
                                        <a href="{{ route('manage.addons.show', 'payment-gateway') }}" class="btn btn-primary">
                                            <i class="fas fa-shopping-cart me-2"></i>Beli Payment Gateway Add-on
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                        <div class="row">
                            <div class="col-lg-6">
                                <h5 class="mb-3">Midtrans Payment Gateway Settings</h5>
                                <table class="table table-bordered">
                                    <tr><th width="200">Mode</th><td>{{ ucfirst($gateway->midtrans_mode ?? 'sandbox') }}</td></tr>
                                    <tr><th>Status</th><td>{{ $gateway->midtrans_is_active ? 'Aktif' : 'Tidak Aktif' }}</td></tr>
                                    <tr><th>Server Key</th><td>
                                        @if($gateway->midtrans_mode === 'production')
                                            {{ $gateway->midtrans_server_key_production ? '***' . substr($gateway->midtrans_server_key_production, -4) : '-' }}
                                        @else
                                            {{ $gateway->midtrans_server_key_sandbox ? '***' . substr($gateway->midtrans_server_key_sandbox, -4) : '-' }}
                                        @endif
                                    </td></tr>
                                    <tr><th>Client Key</th><td>
                                        @if($gateway->midtrans_mode === 'production')
                                            {{ $gateway->midtrans_client_key_production ? '***' . substr($gateway->midtrans_client_key_production, -4) : '-' }}
                                        @else
                                            {{ $gateway->midtrans_client_key_sandbox ? '***' . substr($gateway->midtrans_client_key_sandbox, -4) : '-' }}
                                        @endif
                                    </td></tr>
                                    <tr><th>Merchant ID</th><td>
                                        @if($gateway->midtrans_mode === 'production')
                                            {{ $gateway->midtrans_merchant_id_production ? '***' . substr($gateway->midtrans_merchant_id_production, -4) : '-' }}
                                        @else
                                            {{ $gateway->midtrans_merchant_id_sandbox ? '***' . substr($gateway->midtrans_merchant_id_sandbox, -4) : '-' }}
                                        @endif
                                    </td></tr>
                                </table>
                            </div>
                            <div class="col-lg-6">
                                <h5 class="mb-3">Edit Payment Gateway</h5>
                                <form method="POST" action="{{ route('manage.general.setting.gateway') }}">
                                    @csrf
                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label">Status</label>
                                        <div class="col-sm-8">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="midtrans_is_active" value="1" id="midtransActive" {{ ($gateway->midtrans_is_active ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="midtransActive">
                                                    Aktifkan Midtrans Payment Gateway
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Midtrans Settings -->
                                    <div class="card mb-3">
                                        <div class="card-header">Midtrans Settings</div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label">Mode</label>
                                                <div class="col-sm-8">
                                                    <select class="form-select" name="midtrans_mode" id="midtransMode">
                                                        <option value="sandbox" {{ ($gateway->midtrans_mode ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                                        <option value="production" {{ ($gateway->midtrans_mode ?? 'sandbox') == 'production' ? 'selected' : '' }}>Production</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <!-- Sandbox Settings -->
                                            <div id="sandboxSettings" class="midtrans-settings">
                                                <h6 class="mb-3">Sandbox Settings</h6>
                                                <div class="row mb-3">
                                                    <label class="col-sm-4 col-form-label">Server Key</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" name="midtrans_server_key_sandbox" value="{{ old('midtrans_server_key_sandbox', $gateway->midtrans_server_key_sandbox ?? '') }}">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <label class="col-sm-4 col-form-label">Client Key</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" name="midtrans_client_key_sandbox" value="{{ old('midtrans_client_key_sandbox', $gateway->midtrans_client_key_sandbox ?? '') }}">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <label class="col-sm-4 col-form-label">Merchant ID</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" name="midtrans_merchant_id_sandbox" value="{{ old('midtrans_merchant_id_sandbox', $gateway->midtrans_merchant_id_sandbox ?? '') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Production Settings -->
                                            <div id="productionSettings" class="midtrans-settings" style="display: none;">
                                                <h6 class="mb-3">Production Settings</h6>
                                                <div class="row mb-3">
                                                    <label class="col-sm-4 col-form-label">Server Key</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" name="midtrans_server_key_production" value="{{ old('midtrans_server_key_production', $gateway->midtrans_server_key_production ?? '') }}">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <label class="col-sm-4 col-form-label">Client Key</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" name="midtrans_client_key_production" value="{{ old('midtrans_client_key_production', $gateway->midtrans_client_key_production ?? '') }}">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <label class="col-sm-4 col-form-label">Merchant ID</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" name="midtrans_merchant_id_production" value="{{ old('midtrans_merchant_id_production', $gateway->midtrans_merchant_id_production ?? '') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-8 offset-sm-4">
                                            <button type="submit" class="btn btn-success text-white">Simpan Payment Gateway</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- WhatsApp Gateway Tab -->
                    <div id="tab-wa" class="tab-pane-setting">
                        @if(!$hasWhatsAppGatewayAddon)
                            <div class="alert alert-warning" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h5 class="alert-heading mb-2">WhatsApp Gateway Add-on Belum Dimiliki</h5>
                                        <p class="mb-3">Untuk mengakses pengaturan WhatsApp Gateway, Anda perlu membeli add-on WhatsApp Gateway terlebih dahulu.</p>
                                        <a href="{{ route('manage.addons.show', 'whatsapp-gateway') }}" class="btn btn-primary">
                                            <i class="fas fa-shopping-cart me-2"></i>Beli WhatsApp Gateway Add-on
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                        <div class="row">
                            <div class="col-lg-6">
                                <h5 class="mb-3">WhatsApp Gateway Settings</h5>
                                <table class="table table-bordered">
                                    <tr><th width="200">Status Notifikasi</th><td>{{ $gateway->enable_wa_notification ? 'Aktif' : 'Tidak Aktif' }}</td></tr>
                                    <tr><th>URL Gateway</th><td>{{ $gateway->url_wagateway ?? '-' }}</td></tr>
                                    <tr><th>API Key</th><td>{{ $gateway->apikey_wagateway ? '***' . substr($gateway->apikey_wagateway, -4) : '-' }}</td></tr>
                                    <tr><th>Sender</th><td>{{ $gateway->sender_wagateway ?? '-' }}</td></tr>
                                    <tr><th>No. WhatsApp</th><td>{{ $gateway->wa_gateway ?? '-' }}</td></tr>
                                </table>
                            </div>
                            <div class="col-lg-6">
                                <h5 class="mb-3">Edit WhatsApp Gateway</h5>
                                <form method="POST" action="{{ route('manage.general.setting.gateway') }}">
                                    @csrf
                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label">Status Notifikasi</label>
                                        <div class="col-sm-8">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="enable_wa_notification" value="1" id="waNotificationActive" {{ ($gateway->enable_wa_notification ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="waNotificationActive">
                                                    Aktifkan Notifikasi WhatsApp
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label">URL Gateway</label>
                                        <div class="col-sm-8">
                                            <input type="url" class="form-control" name="url_wagateway" value="{{ old('url_wagateway', $gateway->url_wagateway ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label">API Key</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="apikey_wagateway" value="{{ old('apikey_wagateway', $gateway->apikey_wagateway ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label">Sender</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="sender_wagateway" value="{{ old('sender_wagateway', $gateway->sender_wagateway ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-4 col-form-label">No. WhatsApp</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="wa_gateway" value="{{ old('wa_gateway', $gateway->wa_gateway ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-8 offset-sm-4">
                                            <button type="submit" class="btn btn-success text-white">Simpan WhatsApp Gateway</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
.btn.disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}

.btn.disabled:hover {
    opacity: 0.6;
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('General Setting script loaded');
    
    // Tab switching
    const tabButtons = document.querySelectorAll('[data-tab]');
    const tabPanes = document.querySelectorAll('.tab-pane-setting');
    
    console.log('Found tab buttons:', tabButtons.length);
    console.log('Found tab panes:', tabPanes.length);
    
    // Log all tab buttons and panes for debugging
    tabButtons.forEach(function(btn, index) {
        console.log(`Tab button ${index}:`, btn.getAttribute('data-tab'), btn);
    });
    
    tabPanes.forEach(function(pane, index) {
        console.log(`Tab pane ${index}:`, pane.id, pane);
    });
    
    tabButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.getAttribute('data-tab');
            console.log('Tab clicked:', tabName);
            
            // Check if Payment Gateway tab is disabled
            if (tabName === 'gateway' && this.classList.contains('disabled')) {
                e.preventDefault();
                alert('Payment Gateway Add-on belum dimiliki. Silakan beli add-on terlebih dahulu.');
                return;
            }
            
            // Check if WhatsApp Gateway tab is disabled
            if (tabName === 'wa' && this.classList.contains('disabled')) {
                e.preventDefault();
                alert('WhatsApp Gateway Add-on belum dimiliki. Silakan beli add-on terlebih dahulu.');
                return;
            }
            
            // Remove active class from all buttons
            tabButtons.forEach(function(btn) {
                btn.classList.remove('active');
                console.log('Removed active from:', btn.getAttribute('data-tab'));
            });
            
            // Add active class to clicked button
            this.classList.add('active');
            console.log('Added active to:', tabName);
            
            // Hide all tab panes
            tabPanes.forEach(function(pane) {
                pane.style.display = 'none';
                console.log('Hiding tab pane:', pane.id);
            });
            
            // Show selected tab pane
            const selectedPane = document.getElementById('tab-' + tabName);
            if (selectedPane) {
                selectedPane.style.display = 'block';
                console.log('Showing tab pane:', selectedPane.id);
                
                // Force reflow to ensure display change takes effect
                selectedPane.offsetHeight;
            } else {
                console.error('Tab pane not found:', 'tab-' + tabName);
            }
        });
    });
    
    // Logo preview
    const logoInput = document.getElementById('logoInput');
    if (logoInput) {
        logoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('logoPreview');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Midtrans mode switching
    const modeSelect = document.getElementById('midtransMode');
    const sandboxSettings = document.getElementById('sandboxSettings');
    const productionSettings = document.getElementById('productionSettings');
    
    if (modeSelect) {
        function toggleSettings() {
            const selectedMode = modeSelect.value;
            if (selectedMode === 'sandbox') {
                sandboxSettings.style.display = 'block';
                productionSettings.style.display = 'none';
            } else {
                sandboxSettings.style.display = 'none';
                productionSettings.style.display = 'block';
            }
        }
        
        modeSelect.addEventListener('change', toggleSettings);
        toggleSettings(); // Initial call
    }
    
    // Initialize default tab display
    function initDefaultTab() {
        console.log('Initializing default tab display');
        
        // Hide all tabs first
        tabPanes.forEach(function(tab) {
            tab.style.display = 'none';
            console.log('Hiding tab:', tab.id);
        });
        
        // Show profile tab by default
        const profileTab = document.getElementById('tab-profile');
        if (profileTab) {
            profileTab.style.display = 'block';
            console.log('Profile tab displayed');
        }
        
        // Ensure profile button is active
        const profileButton = document.querySelector('[data-tab="profile"]');
        if (profileButton) {
            profileButton.classList.add('active');
            console.log('Profile button activated');
        }
        
        // Log final state
        tabPanes.forEach(function(tab) {
            console.log('Tab', tab.id, 'display:', tab.style.display);
        });
    }
    
    // Initialize default tab
    initDefaultTab();
    
    // Test tab switching functionality
    console.log('Testing tab switching functionality...');
    
    // Add click event to test tab switching
    setTimeout(function() {
        console.log('Testing tab switching after initialization...');
        const rekeningButton = document.querySelector('[data-tab="rekening"]');
        if (rekeningButton) {
            console.log('Found rekening button, testing click...');
            // Don't actually click, just log for debugging
            console.log('Rekening button ready for click');
        } else {
            console.error('Rekening button not found!');
        }
    }, 500);
    

    
    // Form submission debugging
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            console.log('Form submitted');
            console.log('Form data:', new FormData(this));
            
            // Check if logo file is selected
            const logoInput = document.getElementById('logoInput');
            if (logoInput && logoInput.files.length > 0) {
                const file = logoInput.files[0];
                console.log('Logo file selected:', {
                    name: file.name,
                    size: file.size,
                    type: file.type
                });
            } else {
                console.log('No logo file selected');
            }
        });
    }
});
</script>
@endsection 