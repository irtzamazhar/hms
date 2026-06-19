<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Purchase Order — {{ $purchase->purchase_number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1e293b; }
    .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 12px; margin-bottom: 16px; }
    .header h1 { font-size: 20px; color: #1e40af; }
    .header p { font-size: 11px; color: #64748b; }
    .po-title { background: #1e40af; color: #fff; text-align: center; padding: 6px; font-size: 14px; font-weight: bold; margin-bottom: 12px; }
    .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
    .meta-box { border: 1px solid #e2e8f0; padding: 10px; border-radius: 4px; }
    .meta-box dt { font-size: 10px; color: #94a3b8; text-transform: uppercase; }
    .meta-box dd { font-weight: bold; color: #1e293b; margin-top: 2px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    th { background: #1e40af; color: #fff; padding: 8px 10px; font-size: 10px; text-align: left; }
    td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; }
    tr:nth-child(even) td { background: #f8fafc; }
    .totals { margin-left: auto; width: 250px; }
    .total-row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #f1f5f9; }
    .total-final { font-size: 14px; font-weight: bold; color: #1e40af; border-top: 2px solid #1e40af; padding-top: 6px; margin-top: 4px; }
    .badge-paid { background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 99px; font-size: 10px; }
    .badge-pending { background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 99px; font-size: 10px; }
    .badge-partial { background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 99px; font-size: 10px; }
    @page { margin: 15mm; }
    @media print { body { -webkit-print-color-adjust: exact; } }
</style>
</head>
<body>
<div class="header">
    <h1>{{ $setting->hospital_name ?? 'Hospital Management System' }}</h1>
    <p>{{ $setting->address ?? '' }} | {{ $setting->phone ?? '' }}</p>
</div>

<div class="po-title">PURCHASE ORDER</div>

<div class="meta-grid">
    <div class="meta-box">
        <dl>
            <dt>Supplier</dt><dd>{{ $purchase->supplier?->name }}</dd>
            @if($purchase->supplier?->company)<dd style="font-weight:normal;font-size:11px">{{ $purchase->supplier->company }}</dd>@endif
            <dt style="margin-top:6px">Phone</dt><dd>{{ $purchase->supplier?->phone ?? '—' }}</dd>
        </dl>
    </div>
    <div class="meta-box">
        <dl>
            <dt>PO Number</dt><dd>{{ $purchase->purchase_number }}</dd>
            <dt style="margin-top:6px">Date</dt><dd>{{ $purchase->purchase_date->format('d M Y') }}</dd>
            @if($purchase->invoice_number)
            <dt style="margin-top:6px">Invoice #</dt><dd>{{ $purchase->invoice_number }}</dd>
            @endif
            <dt style="margin-top:6px">Payment</dt>
            <dd><span class="badge-{{ $purchase->payment_status }}">{{ ucfirst($purchase->payment_status) }}</span></dd>
        </dl>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Medicine</th>
            <th style="text-align:center">Qty</th>
            <th style="text-align:right">Unit Price</th>
            <th style="text-align:right">Sale Price</th>
            <th>Batch</th>
            <th>Expiry</th>
            <th style="text-align:right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchase->items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->medicine?->name }}</td>
            <td style="text-align:center">{{ $item->quantity }}</td>
            <td style="text-align:right">₨ {{ number_format($item->unit_price, 2) }}</td>
            <td style="text-align:right">₨ {{ number_format($item->sale_price, 2) }}</td>
            <td>{{ $item->batch_number ?? '—' }}</td>
            <td>{{ $item->expiry_date?->format('M Y') ?? '—' }}</td>
            <td style="text-align:right; font-weight:bold">₨ {{ number_format($item->total_price, 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="totals">
    <div class="total-row"><span>Subtotal</span><span>₨ {{ number_format($purchase->subtotal, 0) }}</span></div>
    @if($purchase->discount > 0)
    <div class="total-row"><span>Discount</span><span>− ₨ {{ number_format($purchase->discount, 0) }}</span></div>
    @endif
    @if($purchase->tax > 0)
    <div class="total-row"><span>Tax</span><span>+ ₨ {{ number_format($purchase->tax, 0) }}</span></div>
    @endif
    <div class="total-row total-final"><span>TOTAL</span><span>₨ {{ number_format($purchase->total_amount, 0) }}</span></div>
    <div class="total-row" style="color:#16a34a"><span>Paid</span><span>₨ {{ number_format($purchase->paid_amount, 0) }}</span></div>
    <div class="total-row" style="color:#dc2626"><span>Due</span><span>₨ {{ number_format($purchase->due_amount, 0) }}</span></div>
</div>

@if($purchase->notes)
<div style="margin-top:16px; padding:10px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; font-size:11px;">
    <strong>Notes:</strong> {{ $purchase->notes }}
</div>
@endif
</body>
</html>
