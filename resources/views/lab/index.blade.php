@extends('layouts.hms')
@section('title','Laboratory Bookings')
@section('breadcrumb')
    <span class="text-slate-400">Lab</span> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Bookings</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">Lab Bookings</h1>
        <p class="text-sm text-slate-400">{{ $bookings->total() }} total</p>
    </div>
    @can('create lab')
    <a href="{{ route('lab.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Booking
    </a>
    @endcan
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <input type="date" name="date" value="{{ request('date', today()->toDateString()) }}"
               class="field">
        <select name="status" class="field">
            <option value="">All Statuses</option>
            @foreach(['pending','processing','completed','cancelled'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Patient name…"
               class="field">
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 text-white text-sm rounded-lg">Filter</button>
            <a href="{{ route('lab.index') }}" class="btn-cancel">Reset</a>
        </div>
    </div>
</form>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Booking #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Patient</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Tests</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Amount</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($bookings as $b)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-4 py-3 font-mono text-xs text-primary-600 dark:text-primary-400 font-medium">{{ $b->booking_number }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-700 dark:text-white">{{ $b->patient->name }}</p>
                        <p class="text-xs text-slate-400">{{ $b->patient->mr_number }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-500">{{ $b->items->count() }} test(s)</td>
                    <td class="px-4 py-3 font-semibold text-slate-700 dark:text-white">₨ {{ number_format($b->net_amount, 0) }}</td>
                    <td class="px-4 py-3">
                        <x-badge color="{{ ['completed'=>'green','processing'=>'blue','pending'=>'amber','cancelled'=>'red'][$b->status] ?? 'slate' }}">
                            {{ ucfirst($b->status) }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-400">{{ $b->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('lab.show',$b) }}" class="p-1.5 rounded text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400 text-sm">No lab bookings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bookings->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection
