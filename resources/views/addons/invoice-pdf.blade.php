<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoiceNumber }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #6f42c1;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .company-logo {
            max-width: 120px;
            max-height: 60px;
            margin-bottom: 10px;
        }
        .company-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        .company-title {
            margin: 0;
            color: #6f42c1;
        }
        .invoice-info {
            float: right;
            width: 40%;
            text-align: right;
        }
        .clear {
            clear: both;
        }
        .customer-info {
            margin-bottom: 30px;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .invoice-table th,
        .invoice-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .invoice-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-section {
            float: right;
            width: 300px;
        }
        .total-row {
            padding: 5px 0;
        }
        .total-row.grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #6f42c1;
            padding-top: 10px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .addon-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            background-color: #6f42c1;
            color: white;
            font-size: 10px;
            font-weight: bold;
        }
        .lifetime-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            background: linear-gradient(135deg, #198754, #20c997);
            color: white;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <div class="company-header">
                <img src="{{ public_path('images/logo.png') }}" alt="SPPQU Logo" class="company-logo">
                <h1 class="company-title">{{ $company['name'] }}</h1>
            </div>
            <p style="margin: 5px 0;">{{ $company['address'] }}</p>
            <p style="margin: 5px 0;">Phone: {{ $company['phone'] }}</p>
            <p style="margin: 5px 0;">Email: {{ $company['email'] }}</p>
        </div>
        <div class="invoice-info">
            <h2 style="color: #6f42c1; margin: 0;">INVOICE</h2>
            <h3 style="margin: 5px 0; color: #6f42c1;">ADD-ON PREMIUM</h3>
            <p style="margin: 5px 0;"><strong>Invoice #:</strong> {{ $invoiceNumber }}</p>
            <p style="margin: 5px 0;"><strong>Date:</strong> {{ $invoiceDate }}</p>
            <p style="margin: 5px 0;">
                <strong>Status:</strong> 
                <span class="status-badge status-{{ $userAddon->status }}">
                    {{ strtoupper($userAddon->status) }}
                </span>
            </p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="customer-info">
        <h3>Bill To:</h3>
        <p style="margin: 5px 0;"><strong>{{ $school['name'] }}</strong></p>
        <p style="margin: 5px 0;">{{ $school['address'] }}</p>
        <p style="margin: 5px 0;">Phone: {{ $school['phone'] }}</p>
        <p style="margin: 5px 0; margin-top: 10px;"><strong>Admin:</strong> {{ $user->name }}</p>
        <p style="margin: 5px 0;">Email: {{ $user->email }}</p>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Type</th>
                <th>Purchase Date</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $addon->name }}</strong>
                    <br>
                    <span class="addon-badge">🧩 Premium Add-on</span>
                    <br>
                    <small style="color: #666;">{{ $addon->description }}</small>
                </td>
                <td>
                    @if($addon->type === 'one_time')
                        <span class="lifetime-badge">♾️ LIFETIME</span>
                    @else
                        <span class="status-badge status-active">🔄 Recurring</span>
                    @endif
                </td>
                <td>{{ $invoiceDate }}</td>
                <td style="text-align: right;">
                    <strong>Rp {{ number_format($userAddon->amount_paid, 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span style="float: left;">Subtotal:</span>
            <span style="float: right;">Rp {{ number_format($userAddon->amount_paid, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span style="float: left;">Tax (0%):</span>
            <span style="float: right;">Rp 0</span>
        </div>
        <div class="total-row grand-total">
            <span style="float: left;">Total:</span>
            <span style="float: right;">Rp {{ number_format($userAddon->amount_paid, 0, ',', '.') }}</span>
        </div>
        <div class="clear"></div>
    </div>

    @if($userAddon->status == 'active')
        <div style="margin-top: 30px; padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;">
            <h4 style="margin: 0; color: #155724;">✅ Payment Confirmed</h4>
            <p style="margin: 5px 0;"><strong>Purchase Date:</strong> {{ $invoiceDate }}</p>
            @if($userAddon->transaction_id)
                <p style="margin: 5px 0;"><strong>Transaction ID:</strong> {{ $userAddon->transaction_id }}</p>
            @endif
            @if($userAddon->payment_reference)
                <p style="margin: 5px 0;"><strong>Payment Reference:</strong> {{ $userAddon->payment_reference }}</p>
            @endif
            @if($userAddon->payment_method)
                <p style="margin: 5px 0;"><strong>Payment Method:</strong> {{ strtoupper($userAddon->payment_method) }}</p>
            @endif
            @if($addon->type === 'one_time')
                <p style="margin: 10px 0 5px 0; padding: 10px; background-color: #fff; border-left: 3px solid #198754;">
                    <strong>🎉 Lifetime Access!</strong><br>
                    <small>Add-on ini dapat digunakan selamanya tanpa perpanjangan.</small>
                </p>
            @endif
        </div>
    @endif

    <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
        <h4 style="margin: 0 0 10px 0;">ℹ️ Add-on Features:</h4>
        @if($addon->features)
            @php
                $features = is_string($addon->features) ? json_decode($addon->features, true) : $addon->features;
            @endphp
            <ul style="margin: 5px 0; padding-left: 20px;">
                @foreach($features as $feature)
                    <li style="margin: 5px 0;">{{ $feature }}</li>
                @endforeach
            </ul>
        @else
            <p style="margin: 5px 0; color: #666;">Semua fitur {{ $addon->name }} tersedia.</p>
        @endif
    </div>

    <div class="footer">
        <p>Thank you for purchasing SPPQU Premium Add-on!</p>
        <p>This is a computer generated invoice. No signature required.</p>
        <p>For support or questions, please contact us at {{ $company['email'] }} or {{ $company['phone'] }}</p>
    </div>
</body>
</html>

