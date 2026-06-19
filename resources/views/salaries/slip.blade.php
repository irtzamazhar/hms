<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Salary Slip — {{ $salaryPayment->month_name }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1e293b; }
    .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 12px; }
    .header h1 { font-size: 18px; color: #1e40af; }
    .header p { font-size: 11px; color: #64748b; }
    .slip-title { text-align: center; font-size: 14px; font-weight: bold; background: #1e40af; color: #fff; padding: 6px; margin-bottom: 14px; }
    .emp-box { border: 1px solid #e2e8f0; padding: 12px; border-radius: 4px; margin-bottom: 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .emp-box dt { font-size: 10px; color: #94a3b8; text-transform: uppercase; }
    .emp-box dd { font-weight: bold; color: #1e293b; margin-top: 1px; }
    .salary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
    .salary-section { border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden; }
    .section-header { background: #f8fafc; font-weight: bold; font-size: 11px; padding: 6px 10px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #e2e8f0; }
    .section-row { display: flex; justify-content: space-between; padding: 5px 10px; font-size: 11px; border-bottom: 1px solid #f8fafc; }
    .section-total { display: flex; justify-content: space-between; padding: 6px 10px; font-weight: bold; font-size: 12px; background: #f8fafc; }
    .net-box { border: 2px solid #1e40af; border-radius: 6px; padding: 12px 16px; text-align: center; margin-top: 12px; }
    .net-box .label { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
    .net-box .amount { font-size: 22px; font-weight: bold; color: #1e40af; margin-top: 4px; }
    .footer { margin-top: 24px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; text-align: center; font-size: 10px; color: #94a3b8; }
    .footer .sig-line { border-top: 1px solid #cbd5e1; padding-top: 4px; margin-top: 20px; }
    .badge-paid { background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 99px; font-size: 10px; }
    @page { margin: 15mm; }
    @media print { body { -webkit-print-color-adjust: exact; } }
</style>
</head>
<body>
<div class="header">
    <h1>{{ $setting->hospital_name ?? 'Hospital Management System' }}</h1>
    <p>{{ $setting->address ?? '' }} | {{ $setting->phone ?? '' }}</p>
</div>

<div class="slip-title">SALARY SLIP — {{ strtoupper($salaryPayment->month_name) }}</div>

<div class="emp-box">
    <dl>
        <dt>Employee Name</dt>
        <dd>{{ $salaryPayment->user?->name }}</dd>
    </dl>
    <dl>
        <dt>Employee ID</dt>
        <dd>{{ $salaryPayment->user?->employee_id }}</dd>
    </dl>
    <dl>
        <dt>Designation</dt>
        <dd>{{ ucfirst($salaryPayment->user?->user_type) }}</dd>
    </dl>
    <dl>
        <dt>Payment Status</dt>
        <dd><span class="badge-paid">{{ ucfirst($salaryPayment->status) }}</span></dd>
    </dl>
    @if($salaryPayment->payment_date)
    <dl>
        <dt>Payment Date</dt>
        <dd>{{ $salaryPayment->payment_date->format('d M Y') }}</dd>
    </dl>
    <dl>
        <dt>Payment Method</dt>
        <dd>{{ ucfirst(str_replace('_',' ',$salaryPayment->payment_method ?? '')) }}</dd>
    </dl>
    @endif
</div>

<div class="salary-grid">
    {{-- Earnings --}}
    <div class="salary-section">
        <div class="section-header">Earnings</div>
        <div class="section-row"><span>Basic Salary</span><span>₨ {{ number_format($salaryPayment->basic_salary, 2) }}</span></div>
        <div class="section-row"><span>Allowances</span><span>₨ {{ number_format($salaryPayment->total_allowances, 2) }}</span></div>
        @if($salaryPayment->bonus > 0)
        <div class="section-row"><span>Bonus</span><span>₨ {{ number_format($salaryPayment->bonus, 2) }}</span></div>
        @endif
        @if($salaryPayment->overtime > 0)
        <div class="section-row"><span>Overtime</span><span>₨ {{ number_format($salaryPayment->overtime, 2) }}</span></div>
        @endif
        <div class="section-total">
            <span>Gross Salary</span>
            <span>₨ {{ number_format($salaryPayment->basic_salary + $salaryPayment->total_allowances + $salaryPayment->bonus + $salaryPayment->overtime, 2) }}</span>
        </div>
    </div>

    {{-- Deductions --}}
    <div class="salary-section">
        <div class="section-header">Deductions</div>
        @if($salaryPayment->salaryStructure)
        <div class="section-row"><span>Income Tax</span><span>₨ {{ number_format($salaryPayment->salaryStructure->income_tax_deduction, 2) }}</span></div>
        <div class="section-row"><span>Provident Fund</span><span>₨ {{ number_format($salaryPayment->salaryStructure->provident_fund_deduction, 2) }}</span></div>
        <div class="section-row"><span>Other</span><span>₨ {{ number_format($salaryPayment->salaryStructure->other_deductions, 2) }}</span></div>
        @endif
        <div class="section-total">
            <span>Total Deductions</span>
            <span>₨ {{ number_format($salaryPayment->total_deductions, 2) }}</span>
        </div>
    </div>
</div>

<div class="net-box">
    <div class="label">Net Salary Payable</div>
    <div class="amount">₨ {{ number_format($salaryPayment->net_salary, 2) }}</div>
</div>

<div class="footer">
    <div><div class="sig-line">Employee Signature</div></div>
    <div><div class="sig-line">Accounts Officer</div></div>
    <div><div class="sig-line">HR Manager</div></div>
</div>
</body>
</html>
