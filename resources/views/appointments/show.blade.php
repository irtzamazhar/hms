@extends('layouts.hms')
@section('title','Appointment — '.$appointment->appointment_number)
@section('breadcrumb')
    <a href="{{ route('appointments.index') }}" class="text-slate-400 hover:text-slate-600">Appointments</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">{{ $appointment->appointment_number }}</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">{{ $appointment->appointment_number }}</h1>
    <div class="flex gap-2">
        @can('create appointments')
        <a href="{{ route('appointments.edit', $appointment) }}" class="px-4 py-2 border text-slate-600 dark:text-slate-300 hover:bg-slate-50">Edit</a>
        @endcan
        @if($appointment->status === 'confirmed')
        <a href="{{ route('opd.create') }}?patient_id={{ $appointment->patient_id }}&doctor_id={{ $appointment->doctor?->id }}"
           class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg">Start OPD Visit</a>
        @endif
    </div>
</div>

<div class="grid md:grid-cols-3 gap-4">
    <div class="md:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Appointment Details</h2>
        <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
            <div><dt class="text-slate-400">Patient</dt><dd class="font-medium text-slate-700 dark:text-white">{{ $appointment->patient?->name }} ({{ $appointment->patient?->mr_number }})</dd></div>
            <div><dt class="text-slate-400">Doctor</dt><dd class="font-medium text-slate-700 dark:text-white">Dr. {{ $appointment->doctor?->user?->name }}</dd></div>
            <div><dt class="text-slate-400">Department</dt><dd class="font-medium text-slate-700 dark:text-white">{{ $appointment->department?->name ?? '—' }}</dd></div>
            <div><dt class="text-slate-400">Type</dt><dd><x-badge color="blue">{{ ucfirst(str_replace('_',' ',$appointment->type)) }}</x-badge></dd></div>
            <div><dt class="text-slate-400">Date & Time</dt><dd class="font-medium text-slate-700 dark:text-white">{{ $appointment->appointment_datetime->format('d M Y, h:i A') }}</dd></div>
            <div><dt class="text-slate-400">Duration</dt><dd class="font-medium text-slate-700 dark:text-white">{{ $appointment->duration_minutes ?? 30 }} minutes</dd></div>
            <div><dt class="text-slate-400">Fee</dt><dd class="font-medium text-slate-700 dark:text-white">{{ $appointment->fee ? '₨ '.number_format($appointment->fee, 0) : '—' }}</dd></div>
            <div><dt class="text-slate-400">Payment</dt><dd><x-badge color="{{ $appointment->payment_status === 'paid' ? 'green' : 'amber' }}">{{ ucfirst($appointment->payment_status) }}</x-badge></dd></div>
        </dl>
        @if($appointment->reason)
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700">
            <dt class="text-xs text-slate-400 mb-1">Reason</dt>
            <dd class="text-sm text-slate-600 dark:text-slate-300">{{ $appointment->reason }}</dd>
        </div>
        @endif
        @if($appointment->notes)
        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
            <dt class="text-xs text-slate-400 mb-1">Notes</dt>
            <dd class="text-sm text-slate-600 dark:text-slate-300">{{ $appointment->notes }}</dd>
        </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Status</h2>
            <div class="mb-3"><x-badge color="{{ ['scheduled'=>'blue','confirmed'=>'green','completed'=>'slate','cancelled'=>'red','no_show'=>'amber'][$appointment->status] ?? 'slate' }}" class="text-base">{{ ucfirst(str_replace('_',' ',$appointment->status)) }}</x-badge></div>
            @if(!in_array($appointment->status, ['completed','cancelled']))
            @can('create appointments')
            <form method="POST" action="{{ route('appointments.status', $appointment) }}">
                @csrf @method('PATCH')
                <select name="status" class="field mb-2">
                    @foreach(['scheduled','confirmed','completed','cancelled','no_show'] as $s)
                    <option value="{{ $s }}" @selected($appointment->status === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg">Update Status</button>
            </form>
            @endcan
            @endif
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
            <p class="text-xs text-slate-400">Booked By</p>
            <p class="font-medium text-slate-700 dark:text-white mt-1">{{ $appointment->createdBy?->name }}</p>
            <p class="text-xs text-slate-400 mt-2">Created</p>
            <p class="font-medium text-slate-700 dark:text-white mt-0.5">{{ $appointment->created_at->format('d M Y H:i') }}</p>
        </div>
    </div>
</div>
@endsection
