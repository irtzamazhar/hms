<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>OPD Invoice — {{ $visit->visit_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1e293b; background: white; }
        .container { padding: 24px; max-width: 700px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 2px solid #1d4ed8; padding-bottom: 16px; }
        .hospital-name { font-size: 20px; font-weight: 900; color: #1d4ed8; }
        .hospital-meta { color: #64748b; font-size: 11px; margin-top: 4px; }
        .invoice-title { font-size: 11px; font-weight: bold; color: #64748b; text-align: right; text-transform: uppercase; letter-spacing: 1px; }
        .invoice-no { font-size: 16px; font-weight: 900; color: #1d4ed8; margin-top: 2px; }
        .meta-grid { display: flex; gap: 48px; margin-bottom: 20px; }
        .meta-group .label { font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; margin-bottom: 3px; }
        .meta-group .value { font-size: 13px; font-weight: 600; color: #1e293b; }
        .meta-group .sub { font-size: 11px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table thead tr { background: #f1f5f9; }
        th { padding: 8px 12px; text-align: left; font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; }
        th:last-child { text-align: right; }
        td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; }
        td:last-child { text-align: right; }
        .total-row td { font-weight: 700; font-size: 14px; border-top: 2px solid #1d4ed8; padding-top: 12px; }
        .payment-box { display: flex; justify-content: space-between; background: #f0fdf4; border: 1px solid #86efac; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px; }
        .payment-box.pending { background: #fffbeb; border-color: #fcd34d; }
        .payment-status { font-size: 16px; font-weight: 900; color: #16a34a; }
        .payment-status.pending { color: #d97706; }
        .rx-section { border-top: 1px solid #e2e8f0; padding-top: 16px; }
        .rx-title { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .rx-item { font-size: 11px; color: #475569; margin-bottom: 3px; }
        .footer { text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 20px; }
        @media print { @page { margin: 15mm; } }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <div class="hospital-name">{{ $setting->hospital_name }}</div>
            <div class="hospital-meta">{{ $setting->address }}</div>
            <div class="hospital-meta">{{ $setting->phone }} | {{ $setting->email }}</div>
        </div>
        <div>
            <div class="invoice-title">OPD Invoice</div>
            <div class="invoice-no">{{ $visit->visit_number }}</div>
            <div class="hospital-meta" style="text-align:right">{{ $visit->visit_date->format('d M Y') }}</div>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-group">
            <div class="label">Patient</div>
            <div class="value">{{ $visit->patient->name }}</div>
            <div class="sub">MR: {{ $visit->patient->mr_number }}</div>
            <div class="sub">{{ $visit->patient->phone ?? '' }}</div>
        </div>
        <div class="meta-group">
            <div class="label">Doctor</div>
            <div class="value">Dr. {{ $visit->doctor->user->name }}</div>
            <div class="sub">{{ $visit->doctor->specialization }}</div>
            <div class="sub">{{ ucfirst($visit->shift) }} Shift</div>
        </div>
        <div class="meta-group">
            <div class="label">Visit Type</div>
            <div class="value">{{ ucfirst(str_replace('_',' ',$visit->visit_type)) }}</div>
        </div>
    </div>

    @if($visit->diagnosis)
    <p style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 14px;font-size:12px;color:#334155;margin-bottom:20px;">
        <strong>Diagnosis:</strong> {{ $visit->diagnosis }}
    </p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Consultation Fee ({{ ucfirst(str_replace('_',' ',$visit->visit_type)) }})</td>
                <td>₨ {{ number_format($visit->consultation_fee, 2) }}</td>
            </tr>
            @if($visit->discount_amount > 0)
            <tr>
                <td style="color:#dc2626;">Discount</td>
                <td style="color:#dc2626;">— ₨ {{ number_format($visit->discount_amount, 2) }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td>Total Amount</td>
                <td>₨ {{ number_format($visit->net_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="payment-box {{ $visit->payment_status !== 'paid' ? 'pending' : '' }}">
        <div>
            <div style="font-size:10px;color:#64748b;">Payment Method</div>
            <div style="font-weight:600;">{{ ucfirst(str_replace('_',' ',$visit->payment_method)) }}</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:10px;color:#64748b;">Payment Status</div>
            <div class="payment-status {{ $visit->payment_status !== 'paid' ? 'pending' : '' }}">
                {{ strtoupper($visit->payment_status) }}
            </div>
        </div>
    </div>

    @if($visit->prescription && $visit->prescription->items->count())
    <div class="rx-section">
        <div class="rx-title">Prescription (Rx)</div>
        @foreach($visit->prescription->items as $item)
        <div class="rx-item">• {{ $item->medicine_name }} — {{ $item->dosage }}, {{ $item->frequency }}, {{ $item->duration }}</div>
        @endforeach
        @if($visit->prescription->notes)
        <div class="rx-item" style="margin-top:6px;"><em>Notes: {{ $visit->prescription->notes }}</em></div>
        @endif
    </div>
    @endif

    <div class="footer">
        {{ $setting->hospital_name }} · {{ now()->format('d M Y H:i') }}<br>
        This is a computer generated receipt.
    </div>
</div>
<script>window.onload = function(){ window.print(); }</script>
</body>
</html>
