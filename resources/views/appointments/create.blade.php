@extends('layouts.hms')
@section('title','Book Appointment')
@section('breadcrumb')
    <a href="{{ route('appointments.index') }}" class="text-slate-400 hover:text-slate-600">Appointments</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Book</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Book Appointment</h1>
    <a href="{{ route('appointments.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
</div>

<form method="POST" action="{{ route('appointments.store') }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    @csrf

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Patient <span class="text-red-500">*</span></label>
            <select name="patient_id" required class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="">— Select Patient —</option>
                @foreach($patients as $p)
                <option value="{{ $p->id }}" @selected(request('patient_id') == $p->id)>{{ $p->name }} ({{ $p->mr_number }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Doctor <span class="text-red-500">*</span></label>
            <select name="doctor_id" required class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="">— Select Doctor —</option>
                @foreach($doctors as $d)
                <option value="{{ $d->id }}" @selected(request('doctor_id') == $d->id)>Dr. {{ $d->user?->name }} — {{ $d->specialization }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-form.select name="department_id" label="Department">
            <option value="">— Select —</option>
            @foreach($departments as $dept)
            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
            @endforeach
        </x-form.select>
        <x-form.select name="type" label="Appointment Type" required>
            @foreach(['new'=>'New Patient','follow_up'=>'Follow Up','consultation'=>'Consultation','emergency'=>'Emergency'] as $v => $l)
            <option value="{{ $v }}" @selected(old('type') === $v)>{{ $l }}</option>
            @endforeach
        </x-form.select>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="appointment_datetime" type="datetime-local" label="Date & Time" required
                      :value="now()->addHour()->format('Y-m-d\TH:i')" />
        <x-form.input name="duration_minutes" type="number" label="Duration (minutes)" :value="30" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="fee" type="number" step="0.01" label="Consultation Fee (₨)" />
    </div>

    <x-form.textarea name="reason" label="Reason for Visit" rows="2" />

    <div class="flex justify-end gap-3 pt-2">
        <a href="{{ route('appointments.index') }}" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm rounded-lg">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Book Appointment</button>
    </div>
</form>
</div>
@endsection
