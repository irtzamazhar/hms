@extends('layouts.hms')
@section('title','Expenses')
@section('breadcrumb')
    <span class="font-medium text-slate-700 dark:text-slate-200">Expenses</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Expenses</h1>
    @can('create expenses')
    <a href="{{ route('expenses.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Expense
    </a>
    @endcan
</div>

{{-- Summary --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    @foreach([
        ['Today', '₨ '.number_format($summary['today'],0), 'blue'],
        ['This Month', '₨ '.number_format($summary['month'],0), 'green'],
        ['Pending Approval', $summary['pending'].' expenses', 'amber'],
    ] as [$l,$v,$c])
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">{{ $l }}</p>
        <p class="text-xl font-bold text-{{ $c }}-600 dark:text-{{ $c }}-400 mt-1">{{ $v }}</p>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <input type="date" name="date" value="{{ request('date') }}"
               class="field">
        <select name="category_id" class="field">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="status" class="field">
            <option value="">All Status</option>
            @foreach(['pending','approved','rejected'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 text-white text-sm rounded-lg">Filter</button>
            <a href="{{ route('expenses.index') }}" class="btn-cancel">Reset</a>
        </div>
    </div>
</form>

{{-- Table --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Date</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Title</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Category</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Module</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Amount</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">By</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($expenses as $e)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $e->expense_date->format('d M Y') }}</td>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-white">{{ $e->title }}</td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $e->category?->name ?? '—' }}</td>
                <td class="px-4 py-3"><x-badge color="blue">{{ ucfirst($e->module) }}</x-badge></td>
                <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-white">₨ {{ number_format($e->amount, 0) }}</td>
                <td class="px-4 py-3">
                    <x-badge color="{{ ['pending'=>'amber','approved'=>'green','rejected'=>'red'][$e->status] ?? 'slate' }}">{{ ucfirst($e->status) }}</x-badge>
                </td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $e->createdBy?->name }}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center gap-1.5 justify-end">
                        <a href="{{ route('expenses.show', $e) }}" title="View"
                           class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">No expenses found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($expenses->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $expenses->links() }}</div>
    @endif
</div>
@endsection
