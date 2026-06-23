@extends('layouts.hms')
@section('title',$medicine->name)
@section('breadcrumb')
    <a href="{{ route('medicines.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Medicines</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $medicine->name }}</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex justify-between items-start flex-wrap gap-3">
        <div>
            <h1 class="text-lg font-bold text-slate-800 dark:text-white">{{ $medicine->name }}</h1>
            <p class="text-sm text-slate-400">{{ $medicine->generic_name }} · {{ ucfirst($medicine->dosage_form) }} {{ $medicine->strength }}</p>
        </div>
        <div class="flex gap-2">
            <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $medicine->stock_quantity <= $medicine->minimum_stock ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                Stock: {{ $medicine->stock_quantity }} {{ $medicine->unit }}
            </span>
            @can('manage medicines')
            <a href="{{ route('medicines.edit',$medicine) }}" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">Edit</a>
            @endcan
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
            <div class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Details</div>
            @foreach([
                ['Category', $medicine->category->name ?? '—'],
                ['Dosage Form', ucfirst($medicine->dosage_form)],
                ['Strength', $medicine->strength ?? '—'],
                ['Unit', $medicine->unit ?? '—'],
                ['Barcode', $medicine->barcode ?? '—'],
                ['Supplier', $medicine->supplier->name ?? '—'],
                ['Rx Required', $medicine->is_prescription_required ? 'Yes' : 'No'],
                ['Status', ucfirst($medicine->status)],
            ] as [$l,$v])
            <div class="px-5 py-2.5 flex justify-between">
                <span class="text-sm text-slate-400">{{ $l }}</span>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $v }}</span>
            </div>
            @endforeach
        </div>

        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Pricing</div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-3">
                        <p class="text-xs text-slate-400">Purchase Price</p>
                        <p class="text-lg font-bold text-slate-700 dark:text-white">₨ {{ number_format($medicine->purchase_price, 2) }}</p>
                    </div>
                    <div class="bg-primary-50 dark:bg-primary-900/20 rounded-lg p-3">
                        <p class="text-xs text-slate-400">Selling Price</p>
                        <p class="text-lg font-bold text-primary-600">₨ {{ number_format($medicine->selling_price, 2) }}</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-3">
                        <p class="text-xs text-slate-400">Margin</p>
                        <p class="text-lg font-bold text-green-600">{{ $medicine->purchase_price > 0 ? number_format((($medicine->selling_price - $medicine->purchase_price) / $medicine->purchase_price) * 100, 1) : 0 }}%</p>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3">
                        <p class="text-xs text-slate-400">Min. Stock</p>
                        <p class="text-lg font-bold text-amber-600">{{ $medicine->minimum_stock }}</p>
                    </div>
                </div>
            </div>

            {{-- Stock Adjustment --}}
            @can('manage medicines')
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Stock Adjustment</div>
                <form method="POST" action="{{ route('medicines.stock.adjust',$medicine) }}">
                    @csrf
                    <div class="flex gap-2">
                        <select name="type" class="field">
                            <option value="in">Add Stock</option>
                            <option value="out">Remove Stock</option>
                        </select>
                        <input type="number" name="quantity" placeholder="Qty" min="1" required
                               class="field">
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg">Apply</button>
                    </div>
                    <input type="text" name="reason" placeholder="Reason (optional)" class="field mt-2">
                </form>
            </div>
            @endcan
        </div>
    </div>

    {{-- Stock movements --}}
    @if($medicine->stockMovements && $medicine->stockMovements->count())
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 font-semibold text-sm text-slate-700 dark:text-white">Recent Stock Movements</div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs text-slate-500">Type</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-500">Qty</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-500">Reason</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-500">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($medicine->stockMovements->take(10) as $m)
                <tr>
                    <td class="px-4 py-2"><span class="text-xs font-semibold {{ $m->type === 'in' ? 'text-green-600' : 'text-red-500' }}">{{ strtoupper($m->type) }}</span></td>
                    <td class="px-4 py-2">{{ $m->quantity }}</td>
                    <td class="px-4 py-2 text-slate-500">{{ $m->reason ?? '—' }}</td>
                    <td class="px-4 py-2 text-xs text-slate-400">{{ $m->created_at->format('d M Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
@endsection
