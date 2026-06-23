@extends('layouts.hms')
@section('title','Add Staff Member')
@section('breadcrumb')
    <a href="{{ route('staff.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Staff</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Add Staff</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<form method="POST" action="{{ route('staff.store') }}" class="space-y-4">
@csrf

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Account Information</h2>
    <p class="text-xs text-slate-400 mb-4">Default password: <code class="field bg-slate-100 px-1.5 py-0.5">Staff@123</code></p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="name" label="Full Name *" :value="old('name')" required />
        <x-form.input name="email" label="Email *" type="email" :value="old('email')" required />
        <x-form.input name="phone" label="Phone" :value="old('phone')" />
        <x-form.input name="employee_id" label="Employee ID" :value="old('employee_id')" />
        <div>
            <label class="field-label">Role *</label>
            <select name="user_type" required class="field">
                @foreach(['nurse'=>'Nurse','receptionist'=>'Receptionist','lab_technician'=>'Lab Technician','pharmacist'=>'Pharmacist','accountant'=>'Accountant'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('user_type')===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <x-form.input name="joining_date" label="Joining Date" type="date" :value="old('joining_date', today()->toDateString())" />
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Position Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="designation" label="Designation / Title *" :value="old('designation')" required />
        <div>
            <label class="field-label">Department</label>
            <select name="department_id" class="field">
                <option value="">— None —</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" @selected(old('department_id')==$d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Basic Salary (₨)</label>
            <input type="number" name="basic_salary" value="{{ old('basic_salary') }}" min="0" step="0.01"
                   class="field">
        </div>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Add Staff Member</button>
    <a href="{{ route('staff.index') }}" class="btn-cancel">Cancel</a>
</div>

</form>
</div>
@endsection
