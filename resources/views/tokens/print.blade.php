<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Token #{{ $token->token_number }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; width: 80mm; padding: 8mm; font-size: 12px; }
        .center { text-align: center; }
        .hospital { font-size: 14px; font-weight: bold; }
        .token-no { font-size: 72px; font-weight: 900; line-height: 1; color: #1d4ed8; margin: 8px 0; }
        .divider { border-top: 1px dashed #ccc; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; margin: 3px 0; }
        .label { color: #666; }
        .priority { color: #dc2626; font-weight: bold; font-size: 14px; }
        @media print { body { width: 80mm; } @page { margin: 0; size: 80mm auto; } }
    </style>
</head>
<body>
    <div class="center">
        <p class="hospital">{{ \App\Models\HospitalSetting::current()->hospital_name }}</p>
        <p class="label">OPD Token Slip</p>
    </div>
    <div class="divider"></div>

    @if($token->priority !== 'normal')
    <p class="center priority">⚠ {{ strtoupper($token->priority) }}</p>
    @endif

    <div class="center">
        <p class="label">Token No.</p>
        <p class="token-no">{{ $token->token_number }}</p>
        <p>{{ ucfirst($token->shift) }} Shift — {{ $token->token_date->format('d/m/Y') }}</p>
    </div>

    <div class="divider"></div>

    <div class="row"><span class="label">Patient:</span><span>{{ $token->patient->name }}</span></div>
    <div class="row"><span class="label">MR No:</span><span>{{ $token->patient->mr_number }}</span></div>
    @if($token->doctor)
    <div class="row"><span class="label">Doctor:</span><span>Dr. {{ $token->doctor->user->name }}</span></div>
    @endif
    @if($token->department)
    <div class="row"><span class="label">Dept:</span><span>{{ $token->department->name }}</span></div>
    @endif
    <div class="row"><span class="label">Printed:</span><span>{{ now()->format('d/m/Y H:i') }}</span></div>

    <div class="divider"></div>
    <p class="center label">Please wait for your number to be called.</p>

    <script>window.onload = function(){ window.print(); }</script>
</body>
</html>
