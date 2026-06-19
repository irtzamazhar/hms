@extends('layouts.hms')
@section('title','Appointments')
@section('breadcrumb')
    <span class="font-medium text-slate-700 dark:text-slate-200">Appointments</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Appointments</h1>
    @can('create appointments')
    <a href="{{ route('appointments.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Book Appointment
    </a>
    @endcan
</div>

<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Today's Appointments</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $todayCount }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
        <p class="text-xs text-slate-400">Pending (Today)</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $pendingCount }}</p>
    </div>
</div>

<form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Patient name..."
               class="text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
        <input type="date" name="date" value="{{ request('date') }}"
               class="text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
        <select name="doctor_id" class="text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            <option value="">All Doctors</option>
            @foreach($doctors as $d)
            <option value="{{ $d->id }}" @selected(request('doctor_id') == $d->id)>Dr. {{ $d->user?->name }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <select name="status" class="flex-1 text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="">All Status</option>
                @foreach(['scheduled','confirmed','completed','cancelled','no_show'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-2 bg-primary-600 text-white text-sm rounded-lg">Go</button>
        </div>
    </div>
</form>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">APT #</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Patient</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Doctor</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Date & Time</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Type</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Fee</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($appointments as $apt)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $apt->appointment_number }}</td>
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800 dark:text-white">{{ $apt->patient?->name }}</p>
                    <p class="text-xs text-slate-400">{{ $apt->patient?->mr_number }}</p>
                </td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">Dr. {{ $apt->doctor?->user?->name }}</td>
                <td class="px-4 py-3">
                    <p class="text-slate-700 dark:text-slate-200">{{ $apt->appointment_datetime->format('d M Y') }}</p>
                    <p class="text-xs text-slate-400">{{ $apt->appointment_datetime->format('h:i A') }}</p>
                </td>
                <td class="px-4 py-3">
                    <x-badge color="{{ ['new'=>'blue','follow_up'=>'purple','consultation'=>'cyan','emergency'=>'red'][$apt->type] ?? 'slate' }}">{{ ucfirst(str_replace('_',' ',$apt->type)) }}</x-badge>
                </td>
                <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-white">{{ $apt->fee ? '₨ '.number_format($apt->fee,0) : '—' }}</td>
                <td class="px-4 py-3">
                    <x-badge color="{{ ['scheduled'=>'blue','confirmed'=>'green','completed'=>'slate','cancelled'=>'red','no_show'=>'amber'][$apt->status] ?? 'slate' }}">{{ ucfirst(str_replace('_',' ',$apt->status)) }}</x-badge>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('appointments.show', $apt) }}" class="text-xs text-primary-600 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">No appointments found.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($appointments->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">{{ $appointments->links() }}</div>
    @endif
</div>
@endsection
