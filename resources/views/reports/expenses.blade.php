@extends('layouts.hms')
@section('title','Expense Report')
@section('breadcrumb')
    <a href="{{ route('reports.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Reports</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Expense Report</span>
@endsection

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Expense Report</h1>
    <a href="{{ route('reports.expenses.export', request()->query()) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export Excel
    </a>
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <input type="date" name="from" value="{{ request('from', today()->startOfMonth()->toDateString()) }}"
               class="field">
        <input type="date" name="to" value="{{ request('to', today()->toDateString()) }}"
               class="field">
        <select name="category_id" class="field">
            <option value="">All Categories</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 text-white text-sm rounded-lg">Generate</button>
            <a href="{{ route('reports.expenses') }}" class="btn-cancel">Reset</a>
        </div>
    </div>
</form>

<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Total Expenses</p>
        <p class="text-2xl font-bold text-red-500 mt-1">₨ {{ number_format($totalAmount ?? 0, 0) }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Approved</p>
        <p class="text-2xl font-bold text-green-600 mt-1">₨ {{ number_format($approvedAmount ?? 0, 0) }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Pending</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">₨ {{ number_format($pendingAmount ?? 0, 0) }}</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
            <tr>
                <th class="px-4 py-2.5 text-left text-xs text-slate-500">Title</th>
                <th class="px-4 py-2.5 text-left text-xs text-slate-500">Category</th>
                <th class="px-4 py-2.5 text-right text-xs text-slate-500">Amount</th>
                <th class="px-4 py-2.5 text-left text-xs text-slate-500">Date</th>
                <th class="px-4 py-2.5 text-left text-xs text-slate-500">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($expenses as $e)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                <td class="px-4 py-2.5 text-slate-700 dark:text-white">{{ $e->title }}</td>
                <td class="px-4 py-2.5 text-slate-500">{{ $e->category->name ?? '—' }}</td>
                <td class="px-4 py-2.5 text-right font-semibold text-red-500">₨ {{ number_format($e->amount, 0) }}</td>
                <td class="px-4 py-2.5 text-xs text-slate-400">{{ $e->expense_date->format('d M Y') }}</td>
                <td class="px-4 py-2.5"><x-badge color="{{ ['approved'=>'green','pending'=>'amber','rejected'=>'red'][$e->status] ?? 'slate' }}">{{ ucfirst($e->status) }}</x-badge></td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400 text-sm">No expenses for selected period.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
