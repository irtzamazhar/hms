@extends('layouts.hms')
@section('title','Edit Staff')
@section('breadcrumb')
    <a href="{{ route('staff.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Staff</a> <span class="mx-1">/</span>
    <a href="{{ route('staff.show',$staff) }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">{{ $staff->user->name }}</a> <span class="mx-1">/</span>
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
            <label class="field-label">Status</label>
            <select name="status" class="field">
                @foreach(['active'=>'Active','inactive'=>'Inactive','on_leave'=>'On Leave','terminated'=>'Terminated'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('status',$staff->status)===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <x-form.input name="designation" label="Designation *" :value="old('designation',$staff->designation)" required />
        <div>
            <label class="field-label">Department</label>
            <select name="department_id" class="field">
                <option value="">— None —</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" @selected(old('department_id',$staff->department_id)==$d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Basic Salary (₨)</label>
            <input type="number" name="basic_salary" value="{{ old('basic_salary',$staff->basic_salary) }}" min="0" step="0.01"
                   class="field">
        </div>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Update</button>
    <a href="{{ route('staff.show',$staff) }}" class="btn-cancel">Cancel</a>
</div>

</form>
</div>
@endsection
