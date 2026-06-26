<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Closing Report — {{ $report->report_date->format('d M Y') }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; }
        .container { padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #1d4ed8; padding-bottom: 12px; margin-bottom: 16px; }
        .hospital { font-size: 18px; font-weight: 900; color: #1d4ed8; }
        .sub { color: #64748b; font-size: 10px; }
        .report-title { font-size: 14px; font-weight: 700; margin: 6px 0 2px; }
        .grid2 { display: flex; gap: 16px; margin-bottom: 16px; }
        .card { flex: 1; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; }
        .card-label { font-size: 9px; color: #94a3b8; text-transform: uppercase; font-weight: bold; }
        .card-value { font-size: 20px; font-weight: 900; margin-top: 2px; }
        .card-sub { font-size: 11px; color: #16a34a; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th { background: #f1f5f9; padding: 6px 10px; text-align: left; font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
        td { padding: 6px 10px; border-bottom: 1px solid #f8fafc; }
        .total-section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; }
        .total-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; }
        .net { font-size: 16px; font-weight: 900; border-top: 2px solid #1d4ed8; padding-top: 6px; margin-top: 4px; }
        .footer { text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 16px; }
        @media print { @page { margin: 15mm; } }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="hospital">{{ $setting->hospital_name }}</div>
        <div class="sub">{{ $setting->address }} | {{ $setting->phone }}</div>
        <div class="report-title">Daily Closing Report</div>
        <div class="sub">{{ $report->report_date->format('d M Y') }}</div>
    </div>

    <div class="grid2">
        @foreach([
            ['OPD', $report->total_opd_patients ?? 0, 'Rs '.number_format($report->opd_revenue ?? 0, 0)],
            ['IPD', $report->total_ipd_admissions ?? 0, 'Rs '.number_format($report->ipd_revenue ?? 0, 0)],
            ['Pharmacy', null, 'Rs '.number_format($report->pharmacy_revenue ?? 0, 0)],
            ['Lab', null, 'Rs '.number_format($report->lab_revenue ?? 0, 0)],
        ] as [$dept, $count, $revenue])
        <div class="card">
            <div class="card-label">{{ $dept }}</div>
            <div class="card-value">{{ is_null($count) ? $revenue : $count }}</div>
            @if(!is_null($count))<div class="card-sub">{{ $revenue }}</div>@endif
        </div>
        @endforeach
    </div>

    <div class="total-section">
        <div class="total-row"><span>Total Revenue</span><span style="color:#16a34a;font-weight:600;">Rs {{ number_format($report->total_revenue ?? 0, 0) }}</span></div>
        <div class="total-row"><span>Total Expenses</span><span style="color:#dc2626;font-weight:600;">Rs {{ number_format($report->total_expenses ?? 0, 0) }}</span></div>
        <div class="total-row net"><span>Net Profit</span><span style="color:{{ ($report->net_profit ?? 0) >= 0 ? '#16a34a' : '#dc2626' }};">Rs {{ number_format($report->net_profit ?? 0, 0) }}</span></div>
    </div>

    <div class="footer">
        Prepared: {{ now()->format('d M Y H:i') }} | {{ $setting->hospital_name }} HMS
        @if($report->closed_at) | CLOSED @endif
    </div>
</div>
</body>
</html>
