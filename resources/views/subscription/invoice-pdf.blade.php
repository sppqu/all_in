<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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
            border-bottom: 2px solid #007bff;
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
            color: #007bff;
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
            font-size: 14px;
        }
        .total-row {
            padding: 8px 0;
            font-size: 14px;
        }
        .total-row.grand-total {
            font-weight: bold;
            font-size: 18px;
            border-top: 2px solid #007bff;
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
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
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
            <h2 style="color: #007bff; margin: 0;">INVOICE</h2>
            <p style="margin: 5px 0;"><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
            <p style="margin: 5px 0;"><strong>Date:</strong> {{ $invoice->created_at->format('d/m/Y') }}</p>
            <p style="margin: 5px 0;"><strong>Due Date:</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
            <p style="margin: 5px 0;">
                <strong>Status:</strong> 
                <span class="status-badge status-{{ $invoice->payment_status }}">
                    {{ strtoupper($invoice->payment_status) }}
                </span>
            </p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="customer-info">
        <h3>Bill To:</h3>
        <p style="margin: 5px 0;">{{ $school['name'] }}</p>
        <p style="margin: 5px 0;">{{ $school['address'] }}</p>
        <p style="margin: 5px 0;">{{ $school['phone'] }}</p>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Duration</th>
                <th>Activation Date</th>
                <th>Expired Date</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->plan_name }} - SPPQU Subscription</td>
                <td>{{ $invoice->billing_details['plan_duration'] ?? '30 hari' }}</td>
                <td>{{ $invoice->subscription->activated_at ? $invoice->subscription->activated_at->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $invoice->subscription->expires_at ? $invoice->subscription->expires_at->format('d/m/Y H:i') : '-' }}</td>
                <td style="text-align: right;">{{ $invoice->formatted_amount }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span style="float: left;">Subtotal:</span>
            <span style="float: right;">{{ $invoice->formatted_amount }}</span>
        </div>
        <div class="total-row">
            <span style="float: left;">Tax (0%):</span>
            <span style="float: right;">Rp 0</span>
        </div>
        <div class="total-row grand-total">
            <span style="float: left;">Total:</span>
            <span style="float: right;">{{ $invoice->formatted_amount }}</span>
        </div>
        <div class="clear"></div>
    </div>

    @if($invoice->isPaid())
        <div style="margin-top: 30px; padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;">
            <h4 style="margin: 0; color: #155724;">Payment Information</h4>
            <p style="margin: 5px 0;"><strong>Paid Date:</strong> {{ $invoice->paid_at->format('d/m/Y H:i') }}</p>
            @if($invoice->midtrans_transaction_id)
                <p style="margin: 5px 0;"><strong>Transaction ID:</strong> {{ $invoice->midtrans_transaction_id }}</p>
            @endif
            @if($invoice->payment_method)
                <p style="margin: 5px 0;"><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}</p>
            @endif
        </div>
    @endif

    <div class="footer">
        <p>Thank you for choosing SPPQU!</p>
        <p>This is a computer generated invoice. No signature required.</p>
        <p>For any questions, please contact us at {{ $company['email'] }}</p>
    </div>
</body>
</html>
