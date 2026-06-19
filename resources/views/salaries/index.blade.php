@extends('layouts.hms')
@section('title','Salary Payments')
@section('breadcrumb')
    <span class="font-medium text-slate-700 dark:text-slate-200">Salaries</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Salary Management</h1>
    <div class="flex gap-2">
        <a href="{{ route('salaries.structure') }}" class="px-4 py-2 border text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Salary Structure</a>
        <a href="{{ route('salaries.export', request()->query()) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Excel
        </a>
        @can('manage salaries')
        <a href="{{ route('salaries.generate') }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg">Generate Salary</a>
        @endcan
    </div>
</div>

{{-- Summary --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Month Total</p>
        <p class="text-xl font-bold text-blue-600 mt-1">₨ {{ number_format($summary['total'], 0) }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Paid Amount</p>
        <p class="text-xl font-bold text-green-600 mt-1">₨ {{ number_format($summary['paid'], 0) }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Pending Payments</p>
        <p class="text-xl font-bold text-amber-600 mt-1">{{ $summary['pending'] }}</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <select name="month" class="field">
            <option value="">All Months</option>
            @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" @selected(request('month') == $m)>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
            @endfor
        </select>
        <select name="year" class="field">
            <option value="">All Years</option>
            @for($y = now()->year; $y >= now()->year - 3; $y--)
            <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>
            @endfor
        </select>
        <select name="status" class="field">
            <option value="">All Status</option>
            @foreach(['generated','paid','cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search employee..."
                   class="field">
            <button type="submit" class="px-3 py-2 bg-primary-600 text-white text-sm rounded-lg">Go</button>
        </div>
    </div>
</form>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Employee</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Period</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Basic</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Allowances</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Deductions</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Net Salary</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($payments as $p)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800 dark:text-white">{{ $p->user?->name }}</p>
                    <p class="text-xs text-slate-400">{{ $p->user?->employee_id }}</p>
                </td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $p->month_name }}</td>
                <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-200">₨ {{ number_format($p->basic_salary, 0) }}</td>
                <td class="px-4 py-3 text-right text-green-600">₨ {{ number_format($p->total_allowances, 0) }}</td>
                <td class="px-4 py-3 text-right text-red-500">₨ {{ number_format($p->total_deductions, 0) }}</td>
                <td class="px-4 py-3 text-right font-bold text-slate-800 dark:text-white">₨ {{ number_format($p->net_salary, 0) }}</td>
                <td class="px-4 py-3">
                    <x-badge color="{{ ['generated'=>'amber','paid'=>'green','cancelled'=>'red'][$p->status] ?? 'slate' }}">{{ ucfirst($p->status) }}</x-badge>
                </td>
                <td class="px-4 py-3 flex gap-2 justify-end">
                    <a href="{{ route('salaries.slip', $p) }}" target="_blank" class="text-xs text-green-600 hover:underline">Slip</a>
                    @if($p->status === 'generated')
                    @can('manage salaries')
                    <button type="button" onclick="document.getElementById('pay-{{ $p->id }}').classList.toggle('hidden')"
                            class="text-xs text-primary-600 hover:underline">Pay</button>
                    @endcan
                    @endif
                </td>
            </tr>
            @if($p->status === 'generated')
            <tr id="pay-{{ $p->id }}" class="hidden bg-blue-50 dark:bg-blue-900/20">
                <td colspan="8" class="px-4 py-3">
                    <form method="POST" action="{{ route('salaries.pay', $p) }}" class="flex gap-3 items-end flex-wrap">
                        @csrf @method('PATCH')
                        <div>
                            <label class="text-xs text-slate-500">Payment Method</label>
                            <select name="payment_method" class="field">
                                @foreach(['cash','bank_transfer','cheque'] as $m)
                                <option value="{{ $m }}">{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="text-xs text-slate-500">Bonus ₨</label><input type="number" name="bonus" value="0" step="0.01" class="field w-28"></div>
                        <div><label class="text-xs text-slate-500">Overtime ₨</label><input type="number" name="overtime" value="0" step="0.01" class="field w-28"></div>
                        <div><label class="text-xs text-slate-500">Ref #</label><input type="text" name="transaction_reference" class="field w-32"></div>
                        <button type="submit" class="px-4 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs rounded-lg font-medium">Mark Paid</button>
                    </form>
                </td>
            </tr>
            @endif
            @empty
            <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">No salary records found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($payments->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
