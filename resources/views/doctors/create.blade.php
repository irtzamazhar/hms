@extends('layouts.hms')
@section('title','Add Doctor')
@section('breadcrumb')
    <a href="{{ route('doctors.index') }}" class="text-slate-400 hover:text-slate-600">Doctors</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Add Doctor</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<form method="POST" action="{{ route('doctors.store') }}" class="space-y-4">
@csrf

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Account Information</h2>
    <p class="text-xs text-slate-400 mb-4">A user account will be created with the default password: <code class="bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded">Doctor@123</code></p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="name" label="Full Name *" :value="old('name')" required />
        <x-form.input name="email" label="Email *" type="email" :value="old('email')" required />
        <x-form.input name="phone" label="Phone" :value="old('phone')" />
        <x-form.input name="employee_id" label="Employee ID" :value="old('employee_id')" />
        <x-form.input name="joining_date" label="Joining Date" type="date" :value="old('joining_date', today()->toDateString())" />
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Professional Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="specialization" label="Specialization" :value="old('specialization')" />
        <x-form.input name="qualification" label="Qualification" :value="old('qualification')" />
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Department</label>
            <select name="department_id" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                <option value="">— None —</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(old('department_id')==$dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Consultation Fee (₨)</label>
            <input type="number" name="consultation_fee" value="{{ old('consultation_fee', 500) }}" min="0" step="0.01"
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
        <x-form.input name="license_number" label="License No." :value="old('license_number')" />
        <x-form.input name="experience_years" label="Experience (years)" type="number" :value="old('experience_years')" />
    </div>
    <div class="mt-4">
        <x-form.textarea name="bio" label="Bio / Notes" :value="old('bio')" rows="2" />
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Add Doctor</button>
    <a href="{{ route('doctors.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm rounded-lg">Cancel</a>
</div>

</form>
</div>
@endsection
