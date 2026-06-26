<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt — {{ $sale->sale_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; width: 80mm; padding: 8mm; font-size: 11px; color: #1e293b; }
        .center { text-align: center; }
        .hospital { font-size: 13px; font-weight: 900; }
        .divider { border-top: 1px dashed #94a3b8; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; margin: 2px 0; }
        .label { color: #64748b; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 9px; color: #64748b; text-transform: uppercase; padding: 2px 0; border-bottom: 1px solid #e2e8f0; }
        th:last-child { text-align: right; }
        td { padding: 3px 0; }
        td:last-child { text-align: right; }
        .total-row td { font-weight: bold; border-top: 1px solid #1e293b; padding-top: 4px; margin-top: 4px; }
        .big-total { font-size: 14px; }
        @media print { body { width: 80mm; } @page { margin: 0; size: 80mm auto; } }
    </style>
</head>
<body>
    <div class="center">
        <p class="hospital">{{ $setting->hospital_name }}</p>
        <p class="label">Pharmacy Receipt</p>
        <p>{{ $setting->phone }}</p>
    </div>
    <div class="divider"></div>

    <div class="row"><span class="label">Receipt:</span><span>{{ $sale->sale_number }}</span></div>
    <div class="row"><span class="label">Date:</span><span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span class="label">Patient:</span><span>{{ $sale->patient?->name ?? 'Walk-in' }}</span></div>
    <div class="row"><span class="label">Shift:</span><span>{{ ucfirst($sale->shift) }}</span></div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr><th>Item</th><th>Qty</th><th>Rate</th><th>Amt</th></tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ Str::limit($item->medicine->name ?? $item->medicine_name, 16) }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 0) }}</td>
                <td>{{ number_format($item->total_price, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            @if(($sale->discount_amount ?? 0) > 0)
            <tr><td colspan="3">Discount</td><td>-{{ number_format($sale->discount_amount, 0) }}</td></tr>
            @endif
            <tr class="total-row"><td colspan="3" class="big-total">TOTAL</td><td class="big-total">Rs {{ number_format($sale->net_amount, 0) }}</td></tr>
        </tfoot>
    </table>

    <div class="divider"></div>
    <p class="center label">Payment: {{ ucfirst(str_replace('_',' ',$sale->payment_method ?? 'cash')) }}</p>
    <p class="center" style="margin-top:8px;font-size:10px;">Thank you for your visit!</p>
    <script>window.onload = function(){ window.print(); }</script>
</body>
</html>
