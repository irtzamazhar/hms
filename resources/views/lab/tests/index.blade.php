@extends('layouts.hms')
@section('title','Lab Tests')
@section('breadcrumb')
    <span class="text-slate-400">Laboratory</span> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Lab Tests</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Lab Tests</h1>
    <div class="flex gap-2">
        <a href="{{ route('lab.index') }}" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm rounded-lg hover:bg-slate-50">Bookings</a>
        @can('create lab')
        <a href="{{ route('lab.tests.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Test
        </a>
        @endcan
    </div>
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or code..."
               class="flex-1 text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
        <select name="category_id" class="text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="status" class="text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            <option value="">All Status</option>
            <option value="active" @selected(request('status')==='active')>Active</option>
            <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg">Filter</button>
        <a href="{{ route('lab.tests.index') }}" class="px-3 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm rounded-lg">Clear</a>
    </div>
</form>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Test</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Category</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Sample</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Cost</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">TAT</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($tests as $test)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800 dark:text-white">{{ $test->name }}</p>
                    <p class="text-xs text-slate-400 font-mono">{{ $test->code }}</p>
                </td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $test->category?->name }}</td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $test->sample_type ?? '—' }}</td>
                <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-white">₨ {{ number_format($test->cost, 0) }}</td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $test->turnaround_hours ? $test->turnaround_hours.'h' : '—' }}</td>
                <td class="px-4 py-3"><x-badge color="{{ $test->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($test->status) }}</x-badge></td>
                <td class="px-4 py-3 text-right">
                    @can('create lab')
                    <a href="{{ route('lab.tests.edit', $test) }}" class="text-xs text-primary-600 hover:underline">Edit</a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">No lab tests found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($tests->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $tests->links() }}</div>
    @endif
</div>
@endsection
