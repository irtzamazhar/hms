@extends('layouts.hms')
@section('title','Suppliers')
@section('breadcrumb')
    <span class="text-slate-400">Pharmacy</span> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Suppliers</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Suppliers</h1>
    @can('manage purchases')
    <a href="{{ route('suppliers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Supplier
    </a>
    @endcan
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or company..."
               class="field">
        <select name="status" class="field">
            <option value="">All Status</option>
            <option value="active" @selected(request('status')==='active')>Active</option>
            <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg">Search</button>
        <a href="{{ route('suppliers.index') }}" class="px-3 py-2 border text-slate-600 dark:text-slate-300">Clear</a>
    </div>
</form>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Supplier</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Contact</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">City</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Purchases</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($suppliers as $s)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800 dark:text-white">{{ $s->name }}</p>
                    @if($s->company)<p class="text-xs text-slate-400">{{ $s->company }}</p>@endif
                </td>
                <td class="px-4 py-3">
                    <p class="text-slate-600 dark:text-slate-300">{{ $s->phone }}</p>
                    @if($s->email)<p class="text-xs text-slate-400">{{ $s->email }}</p>@endif
                </td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $s->city ?? '—' }}</td>
                <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-white">{{ $s->purchases_count }}</td>
                <td class="px-4 py-3"><x-badge color="{{ $s->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($s->status) }}</x-badge></td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1.5 justify-end">
                        <a href="{{ route('suppliers.show', $s) }}" title="View"
                           class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        @can('manage purchases')
                        <a href="{{ route('suppliers.edit', $s) }}" title="Edit"
                           class="p-1.5 rounded-lg text-slate-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No suppliers found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($suppliers->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $suppliers->links() }}</div>
    @endif
</div>
@endsection
