<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Report — {{ date('F Y', mktime(0,0,0,$report->month,1,$report->year)) }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; }
        .container { padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #1d4ed8; padding-bottom: 12px; margin-bottom: 16px; }
        .hospital { font-size: 18px; font-weight: 900; color: #1d4ed8; }
        .sub { color: #64748b; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th { background: #f1f5f9; padding: 7px 12px; text-align: left; font-size: 9px; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0; }
        th:last-child, td:last-child { text-align: right; }
        td { padding: 7px 12px; border-bottom: 1px solid #f1f5f9; }
        .section-title { font-size: 12px; font-weight: 700; color: #1e293b; margin: 12px 0 6px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; }
        .total-box { display: flex; justify-content: space-between; align-items: center; background: #f0fdf4; border: 2px solid #86efac; border-radius: 6px; padding: 12px 16px; margin-top: 12px; }
        .footer { text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 16px; }
        @media print { @page { margin: 15mm; } }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="hospital">{{ $setting->hospital_name }}</div>
        <div class="sub">{{ $setting->address }} | {{ $setting->phone }}</div>
        <div style="font-size:14px;font-weight:700;margin:6px 0 2px;">Monthly Closing Report</div>
        <div class="sub">{{ date('F Y', mktime(0,0,0,$report->month,1,$report->year)) }}</div>
    </div>

    <div class="section-title">Revenue Breakdown</div>
    <table>
        <thead><tr><th>Department</th><th>Records</th><th>Revenue</th></tr></thead>
        <tbody>
            <tr><td>OPD</td><td>{{ $report->total_opd_patients ?? 0 }}</td><td>Rs {{ number_format($report->opd_revenue ?? 0, 0) }}</td></tr>
            <tr><td>IPD</td><td>{{ $report->total_ipd_admissions ?? 0 }}</td><td>Rs {{ number_format($report->ipd_revenue ?? 0, 0) }}</td></tr>
            <tr><td>Pharmacy</td><td>—</td><td>Rs {{ number_format($report->pharmacy_revenue ?? 0, 0) }}</td></tr>
            <tr><td>Laboratory</td><td>—</td><td>Rs {{ number_format($report->lab_revenue ?? 0, 0) }}</td></tr>
            <tr style="font-weight:bold;background:#f8fafc;"><td>Total Revenue</td><td></td><td>Rs {{ number_format($report->total_revenue ?? 0, 0) }}</td></tr>
        </tbody>
    </table>

    <div class="section-title">Expense Breakdown</div>
    <table>
        <thead><tr><th>Category</th><th>Amount</th></tr></thead>
        <tbody>
            @foreach($expenseByCategory ?? [] as $cat)
            <tr><td>{{ $cat->name ?? '—' }}</td><td>Rs {{ number_format($cat->total ?? 0, 0) }}</td></tr>
            @endforeach
            <tr><td>Salaries</td><td>Rs {{ number_format($report->total_salaries ?? 0, 0) }}</td></tr>
            <tr style="font-weight:bold;background:#f8fafc;"><td>Total Expenses</td><td>Rs {{ number_format($report->total_expenses ?? 0, 0) }}</td></tr>
        </tbody>
    </table>

    <div class="total-box">
        <span style="font-size:14px;font-weight:700;">Net Profit / (Loss)</span>
        <span style="font-size:22px;font-weight:900;color:{{ ($report->net_profit ?? 0) >= 0 ? '#16a34a' : '#dc2626' }};">Rs {{ number_format($report->net_profit ?? 0, 0) }}</span>
    </div>

    <div class="footer">
        Generated: {{ now()->format('d M Y H:i') }} | {{ $setting->hospital_name }} HMS
        @if($report->closed_at) | CLOSED @endif
    </div>
</div>
</body>
</html>
