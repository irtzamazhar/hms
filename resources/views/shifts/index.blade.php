@extends('layouts.hms')
@section('title','Shifts')
@section('breadcrumb')
    <span class="font-medium text-slate-700 dark:text-slate-200">Shifts</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Shift Management</h1>
    <div class="flex gap-2">
        <a href="{{ route('shifts.assignments') }}" class="px-4 py-2 border text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Assignments</a>
        <a href="{{ route('shifts.close.form') }}" class="px-4 py-2 border text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Close Shift</a>
        @can('manage settings')
        <a href="{{ route('shifts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Shift
        </a>
        @endcan
    </div>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($shifts as $shift)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <div class="flex justify-between items-start mb-3">
            <div>
                <p class="font-bold text-slate-800 dark:text-white">{{ $shift->name }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ ucfirst($shift->type) }} shift</p>
            </div>
            <x-badge color="{{ $shift->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($shift->status) }}</x-badge>
        </div>
        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 mb-3">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} — {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
        </div>
        <div class="flex justify-between items-center text-xs text-slate-400">
            <span>{{ $shift->assignments_count }} assignments</span>
            @can('manage settings')
            <a href="{{ route('shifts.edit', $shift) }}" class="text-primary-600 hover:underline">Edit</a>
            @endcan
        </div>
    </div>
    @empty
    <div class="md:col-span-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-12 text-center">
        <p class="text-slate-400">No shifts created yet.</p>
        <a href="{{ route('shifts.create') }}" class="mt-3 inline-block px-4 py-2 bg-primary-600 text-white text-sm rounded-lg">Create First Shift</a>
    </div>
    @endforelse
</div>
@endsection
