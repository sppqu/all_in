<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug SPMB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h2>Debug SPMB</h2>
        
        <div class="row">
            <div class="col-md-6">
                <h4>Session Data</h4>
                <pre>{{ json_encode(session()->all(), JSON_PRETTY_PRINT) }}</pre>
            </div>
            
            <div class="col-md-6">
                <h4>Config Data</h4>
                <pre>{{ json_encode([
                    'tripay_api_key' => config('tripay.api_key'),
                    'tripay_private_key' => config('tripay.private_key'),
                    'tripay_merchant_code' => config('tripay.merchant_code'),
                    'tripay_base_url' => config('tripay.base_url'),
                    'tripay_sandbox' => config('tripay.is_sandbox'),
                ], JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <h4>Registration Data</h4>
                @if(session('spmb_registration_id'))
                    @php
                        $registration = \App\Models\SPMBRegistration::find(session('spmb_registration_id'));
                    @endphp
                    @if($registration)
                        <pre>{{ json_encode($registration->toArray(), JSON_PRETTY_PRINT) }}</pre>
                    @else
                        <p class="text-danger">Registration not found</p>
                    @endif
                @else
                    <p class="text-warning">No registration ID in session</p>
                @endif
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <a href="{{ route('spmb.dashboard') }}" class="btn btn-primary me-2">Back to Dashboard</a>
                @if(session('spmb_registration_id'))
                    @php
                        $registration = \App\Models\SPMBRegistration::find(session('spmb_registration_id'));
                    @endphp
                    @if($registration && $registration->step == 1)
                        <a href="{{ route('spmb.fix-step') }}" class="btn btn-warning me-2">Fix Step (1 → 2)</a>
                    @endif
                    @if($registration && $registration->step == 2)
                        <a href="{{ route('spmb.skip-step2') }}" class="btn btn-warning me-2">Skip Step 2 → 3</a>
                    @endif
                    @if($registration && $registration->step <= 2)
                        <a href="{{ route('spmb.force-skip-to-step3') }}" class="btn btn-danger">Force Skip to Step 3</a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</body>
</html>
