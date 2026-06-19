@extends('layouts.hms')
@section('title','Create Shift')
@section('breadcrumb')
    <a href="{{ route('shifts.index') }}" class="text-slate-400 hover:text-slate-600">Shifts</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Create</span>
@endsection

@section('content')
<div class="max-w-lg mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Create Shift</h1>
    <a href="{{ route('shifts.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
</div>

<form method="POST" action="{{ route('shifts.store') }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    @csrf

    <x-form.input name="name" label="Shift Name" placeholder="e.g. Morning Shift" required />

    <x-form.select name="type" label="Type" required>
        @foreach(['morning','evening','night','custom'] as $t)
        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
        @endforeach
    </x-form.select>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="start_time" type="time" label="Start Time" :value="'08:00'" required />
        <x-form.input name="end_time" type="time" label="End Time" :value="'14:00'" required />
    </div>

    <x-form.select name="status" label="Status" required>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </x-form.select>

    <div class="flex justify-end gap-3 pt-2">
        <a href="{{ route('shifts.index') }}" class="btn-cancel">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Create Shift</button>
    </div>
</form>
</div>
@endsection
