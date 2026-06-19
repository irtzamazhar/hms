@extends('layouts.hms')
@section('title','Edit Staff')
@section('breadcrumb')
    <a href="{{ route('staff.index') }}" class="text-slate-400 hover:text-slate-600">Staff</a> <span class="mx-1">/</span>
    <a href="{{ route('staff.show',$staff) }}" class="text-slate-400 hover:text-slate-600">{{ $staff->user->name }}</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Edit</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<form method="POST" action="{{ route('staff.update',$staff) }}" class="space-y-4">
@csrf @method('PUT')

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">Edit Staff Member</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="name" label="Full Name" :value="old('name',$staff->user->name)" required />
        <x-form.input name="phone" label="Phone" :value="old('phone',$staff->user->phone)" />
        <x-form.input name="employee_id" label="Employee ID" :value="old('employee_id',$staff->user->employee_id)" />
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
            <select name="is_active" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                <option value="1" @selected(old('is_active', $staff->is_active ?? true))>Active</option>
                <option value="0" @selected(!old('is_active', $staff->is_active ?? true))>Inactive</option>
            </select>
        </div>
        <x-form.input name="position" label="Position" :value="old('position',$staff->position)" />
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Department</label>
            <select name="department_id" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                <option value="">— None —</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" @selected(old('department_id',$staff->department_id)==$d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Basic Salary (₨)</label>
            <input type="number" name="basic_salary" value="{{ old('basic_salary',$staff->basic_salary) }}" min="0" step="0.01"
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Update</button>
    <a href="{{ route('staff.show',$staff) }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm rounded-lg">Cancel</a>
</div>

</form>
</div>
@endsection
