<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lab Report — {{ $booking->booking_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; background: white; }
        .container { padding: 20px; max-width: 700px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #1d4ed8; padding-bottom: 12px; }
        .hospital-name { font-size: 18px; font-weight: 900; color: #1d4ed8; }
        .sub { color: #64748b; font-size: 10px; margin-top: 2px; }
        .badge { display: inline-block; background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .patient-box { display: flex; gap: 48px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; }
        .pgroup .label { font-size: 9px; color: #94a3b8; text-transform: uppercase; font-weight: bold; }
        .pgroup .value { font-size: 12px; font-weight: 600; margin-top: 1px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead tr { background: #1d4ed8; color: white; }
        th { padding: 8px 12px; text-align: left; font-size: 10px; font-weight: 600; }
        td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
        .flag { color: #dc2626; font-weight: bold; }
        .ok { color: #16a34a; }
        .footer { text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 16px; }
        @media print { @page { margin: 15mm; } }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <div class="hospital-name">{{ $setting->hospital_name }}</div>
            <div class="sub">{{ $setting->address }}</div>
            <div class="sub">{{ $setting->phone }} | Laboratory Department</div>
        </div>
        <div style="text-align:right">
            <p style="font-size:10px;color:#64748b;text-transform:uppercase;font-weight:bold;">Laboratory Report</p>
            <p style="font-size:14px;font-weight:900;color:#1d4ed8;margin-top:2px;">{{ $booking->booking_number }}</p>
            <div class="badge">COMPLETED</div>
        </div>
    </div>

    <div class="patient-box">
        <div class="pgroup"><div class="label">Patient</div><div class="value">{{ $booking->patient->name }}</div><div class="sub" style="color:#64748b">MR: {{ $booking->patient->mr_number }}</div></div>
        <div class="pgroup"><div class="label">Age / Gender</div><div class="value">{{ $booking->patient->age ?? '—' }} yrs / {{ ucfirst($booking->patient->gender) }}</div></div>
        <div class="pgroup"><div class="label">Sample Collected</div><div class="value">{{ $booking->sample_collected_at?->format('d M Y H:i') ?? $booking->created_at->format('d M Y') }}</div></div>
        <div class="pgroup"><div class="label">Report Date</div><div class="value">{{ now()->format('d M Y') }}</div></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Test Name</th>
                <th>Result</th>
                <th>Normal Range</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($booking->items as $item)
            <tr>
                <td><strong>{{ $item->labTest->name }}</strong><br><span style="font-size:9px;color:#94a3b8;">{{ $item->labTest->code ?? '' }}</span></td>
                <td><strong {{ $item->report?->is_abnormal ? 'class="flag"' : '' }}>{{ $item->report?->result_value ?? '—' }}</strong></td>
                <td style="color:#64748b">{{ $item->report?->normal_range ?? '—' }}</td>
                <td>
                    @if($item->report?->is_abnormal ?? false)
                        <span class="flag">Abnormal</span>
                    @else
                        <span class="ok">Normal</span>
                    @endif
                </td>
            </tr>
            @if($item->report?->remarks)
            <tr style="background:#fefce8">
                <td colspan="4" style="color:#92400e;font-style:italic;font-size:10px;">Remark: {{ $item->report->remarks }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ $setting->hospital_name }} · Laboratory Dept · {{ now()->format('d M Y H:i') }}<br>
        This report is computer generated and is valid without signature. For queries: {{ $setting->phone }}
    </div>
</div>
</body>
</html>
