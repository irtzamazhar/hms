@extends('layouts.hms')
@section('title','OPD Visits')
@section('breadcrumb')
    <span class="text-slate-400">OPD</span> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Visits</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">OPD Visits</h1>
        <p class="text-sm text-slate-400">{{ $visits->total() }} total visits</p>
    </div>
    @can('create opd')
    <a href="{{ route('opd.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Visit
    </a>
    @endcan
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <input type="date" name="date" value="{{ request('date', today()->toDateString()) }}"
               class="field">
        <select name="shift" class="field">
            <option value="">All Shifts</option>
            @foreach(['morning','evening','night'] as $s)
                <option value="{{ $s }}" @selected(request('shift')===$s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="doctor_id" class="field">
            <option value="">All Doctors</option>
            @foreach($doctors as $d)
                <option value="{{ $d->id }}" @selected(request('doctor_id')==$d->id)>Dr. {{ $d->user->name }}</option>
            @endforeach
        </select>
        <select name="status" class="field">
            <option value="">All Statuses</option>
            @foreach(['waiting','in_progress','completed','cancelled'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 text-white text-sm rounded-lg">Filter</button>
            <a href="{{ route('opd.index') }}" class="btn-cancel">Reset</a>
        </div>
    </div>
</form>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Visit #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Patient</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Doctor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Shift</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Fee</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Payment</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($visits as $v)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-4 py-3 font-mono text-xs text-primary-600 dark:text-primary-400 font-medium">{{ $v->visit_number }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-700 dark:text-white">{{ $v->patient->name }}</p>
                        <p class="text-xs text-slate-400">{{ $v->patient->mr_number }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">Dr. {{ $v->doctor->user->name }}</td>
                    <td class="px-4 py-3">
                        <x-badge color="{{ ['morning'=>'amber','evening'=>'blue','night'=>'purple'][$v->shift] ?? 'slate' }}">
                            {{ ucfirst($v->shift) }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">₨ {{ number_format($v->net_amount,0) }}</td>
                    <td class="px-4 py-3">
                        <x-badge color="{{ ['paid'=>'green','pending'=>'amber','partial'=>'blue','waived'=>'slate'][$v->payment_status] ?? 'slate' }}">
                            {{ ucfirst($v->payment_status) }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-3">
                        <x-badge color="{{ ['completed'=>'green','in_progress'=>'blue','waiting'=>'amber','cancelled'=>'red'][$v->status] ?? 'slate' }}">
                            {{ ucfirst(str_replace('_',' ',$v->status)) }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-400">{{ $v->visit_date->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1">
                            <a href="{{ route('opd.show',$v) }}" class="p-1.5 rounded text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-white" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('opd.invoice',$v) }}" class="p-1.5 rounded text-slate-400 hover:bg-green-50 dark:hover:bg-green-900/30 hover:text-green-600" title="Invoice">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-12 text-center text-slate-400 text-sm">No OPD visits found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($visits->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $visits->links() }}</div>
    @endif
</div>
@endsection
