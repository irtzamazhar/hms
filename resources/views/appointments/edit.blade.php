@extends('layouts.hms')
@section('title','Edit Appointment')
@section('breadcrumb')
    <a href="{{ route('appointments.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Appointments</a>
    <span class="mx-1">/</span>
    <a href="{{ route('appointments.show', $appointment) }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">{{ $appointment->appointment_number }}</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Edit</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Edit Appointment</h1>
    <a href="{{ route('appointments.show', $appointment) }}" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-200">← Back</a>
</div>

<form method="POST" action="{{ route('appointments.update', $appointment) }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Doctor</label>
            <select name="doctor_id" class="field">
                @foreach($doctors as $d)
                <option value="{{ $d->id }}" @selected($appointment->doctor_id == $d->id)>Dr. {{ $d->user?->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Department</label>
            <select name="department_id" class="field">
                <option value="">—</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected($appointment->department_id == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="appointment_datetime" type="datetime-local" label="Date & Time" required
                      :value="$appointment->appointment_datetime->format('Y-m-d\TH:i')" />
        <x-form.input name="duration_minutes" type="number" label="Duration (min)" :value="$appointment->duration_minutes ?? 30" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-form.select name="type" label="Type" required>
            @foreach(['new'=>'New Patient','follow_up'=>'Follow Up','consultation'=>'Consultation','emergency'=>'Emergency'] as $v => $l)
            <option value="{{ $v }}" @selected($appointment->type === $v)>{{ $l }}</option>
            @endforeach
        </x-form.select>
        <x-form.select name="status" label="Status" required>
            @foreach(['scheduled','confirmed','completed','cancelled','no_show'] as $s)
            <option value="{{ $s }}" @selected($appointment->status === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </x-form.select>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="fee" type="number" step="0.01" label="Fee (₨)" :value="$appointment->fee" />
        <x-form.select name="payment_status" label="Payment Status">
            <option value="pending" @selected($appointment->payment_status === 'pending')>Pending</option>
            <option value="paid" @selected($appointment->payment_status === 'paid')>Paid</option>
        </x-form.select>
    </div>

    <x-form.textarea name="reason" label="Reason" :value="$appointment->reason" rows="2" />
    <x-form.textarea name="notes" label="Notes" :value="$appointment->notes" rows="2" />

    <div class="flex justify-end gap-3 pt-2">
        <a href="{{ route('appointments.show', $appointment) }}" class="btn-cancel">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Update Appointment</button>
    </div>
</form>
</div>
@endsection
