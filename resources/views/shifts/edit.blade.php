@extends('layouts.hms')
@section('title','Edit Shift')
@section('breadcrumb')
    <a href="{{ route('shifts.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Shifts</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Edit</span>
@endsection

@section('content')
<div class="max-w-lg mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Edit Shift — {{ $shift->name }}</h1>
    <a href="{{ route('shifts.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-200">← Back</a>
</div>

<form method="POST" action="{{ route('shifts.update', $shift) }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    @csrf
    @method('PUT')

    <x-form.input name="name" label="Shift Name" :value="$shift->name" required />

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="start_time" type="time" label="Start Time" :value="$shift->start_time" required />
        <x-form.input name="end_time" type="time" label="End Time" :value="$shift->end_time" required />
    </div>

    <x-form.select name="status" label="Status" required>
        <option value="active" @selected($shift->status === 'active')>Active</option>
        <option value="inactive" @selected($shift->status === 'inactive')>Inactive</option>
    </x-form.select>

    <div class="flex justify-between items-center pt-2">
        <form method="POST" action="{{ route('shifts.destroy', $shift) }}" onsubmit="return confirm('Delete shift?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-sm text-red-500 hover:text-red-700">Delete Shift</button>
        </form>
        <div class="flex gap-3">
            <a href="{{ route('shifts.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Update Shift</button>
        </div>
    </div>
</form>
</div>
@endsection
