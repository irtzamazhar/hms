@extends('layouts.hms')
@section('title','Medicine History — '.$patient->name)
@section('breadcrumb')
    <a href="{{ route('patients.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Patients</a> <span class="mx-1">/</span>
    <a href="{{ route('patients.show', $patient) }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">{{ $patient->name }}</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Medicine History</span>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-4">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Medicine History</h1>
            <p class="text-sm text-slate-400">{{ $patient->name }} · {{ $patient->mr_number }}</p>
        </div>
        <a href="{{ route('patients.show', $patient) }}" class="btn-cancel">Back to Patient</a>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 px-5 py-4">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Total Spent</p>
            <p class="text-2xl font-bold text-primary-600 mt-1">₨ {{ number_format($totalSpent, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 px-5 py-4">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Invoices</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ number_format($totalInvoices) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 px-5 py-4">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Medicines Dispensed</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1">{{ number_format($totalQuantity) }} <span class="text-sm font-normal text-slate-400">units</span></p>
        </div>
    </div>

    {{-- Date filter --}}
    <form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="field">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="field">
            </div>
            <div class="flex items-end gap-2 md:col-span-2">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg">Filter</button>
                <a href="{{ route('patients.medicine-history', $patient) }}" class="btn-cancel">Reset</a>
            </div>
        </div>
    </form>

    {{-- History grouped by sale/date --}}
    @forelse($sales as $sale)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 flex flex-wrap justify-between items-center gap-2">
            <div>
                <p class="font-semibold text-sm text-slate-700 dark:text-white">{{ $sale->sale_date->format('d M Y') }}</p>
                <p class="text-xs text-slate-400">
                    <span class="font-mono text-primary-600 dark:text-primary-400">{{ $sale->sale_number }}</span>
                    · {{ ucfirst($sale->shift ?? '—') }} Shift
                    @if($sale->doctor) · Dr. {{ $sale->doctor->user->name }} @endif
                </p>
            </div>
            <a href="{{ route('pharmacy.sale.show', $sale) }}" class="text-xs text-primary-600 hover:underline">View Invoice →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs text-slate-500">Medicine</th>
                        <th class="px-4 py-2.5 text-center text-xs text-slate-500">Qty</th>
                        <th class="px-4 py-2.5 text-right text-xs text-slate-500">Unit Price</th>
                        <th class="px-4 py-2.5 text-right text-xs text-slate-500">Discount</th>
                        <th class="px-4 py-2.5 text-right text-xs text-slate-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($sale->items as $item)
                    <tr>
                        <td class="px-4 py-2.5">
                            <p class="font-medium text-slate-700 dark:text-white">{{ $item->medicine->name ?? 'Medicine #'.$item->medicine_id }}</p>
                            <p class="text-xs text-slate-400">{{ $item->medicine->generic_name ?? '' }}</p>
                        </td>
                        <td class="px-4 py-2.5 text-center text-slate-600 dark:text-slate-300">{{ $item->quantity }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-600 dark:text-slate-300">₨ {{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-500">{{ ($item->discount_amount ?? 0) > 0 ? '₨ '.number_format($item->discount_amount, 2) : '—' }}</td>
                        <td class="px-4 py-2.5 text-right font-medium text-slate-700 dark:text-white">₨ {{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-200 dark:border-slate-600">
                        <td colspan="4" class="px-4 py-2.5 text-right text-sm font-semibold text-slate-600 dark:text-slate-300">Net Total</td>
                        <td class="px-4 py-2.5 text-right font-bold text-primary-600">₨ {{ number_format($sale->net_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-12 text-center text-slate-400 text-sm">
        No medicine purchases found for this patient{{ (request('from') || request('to')) ? ' in the selected date range' : '' }}.
    </div>
    @endforelse

    @if($sales->hasPages())
    <div>{{ $sales->links() }}</div>
    @endif

</div>
@endsection
