@extends('layouts.hms')
@section('title','Edit Patient')
@section('breadcrumb')
    <a href="{{ route('patients.index') }}" class="text-slate-400 hover:text-slate-600">Patients</a> <span class="mx-1">/</span>
    <a href="{{ route('patients.show',$patient) }}" class="text-slate-400 hover:text-slate-600">{{ $patient->name }}</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Edit</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
<form method="POST" action="{{ route('patients.update', $patient) }}" class="space-y-4">
@csrf @method('PUT')

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Personal Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="name" label="Full Name *" :value="old('name',$patient->name)" required />
        <x-form.input name="phone" label="Phone *" :value="old('phone',$patient->phone)" required />
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Gender *</label>
            <select name="gender" required class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                @foreach(['male','female','other'] as $g)
                    <option value="{{ $g }}" @selected(old('gender',$patient->gender)===$g)>{{ ucfirst($g) }}</option>
                @endforeach
            </select>
        </div>
        <x-form.input name="date_of_birth" label="Date of Birth" type="date" :value="old('date_of_birth', $patient->date_of_birth?->toDateString())" />
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Blood Group</label>
            <select name="blood_group" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                <option value="">—</option>
                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                    <option value="{{ $bg }}" @selected(old('blood_group',$patient->blood_group)===$bg)>{{ $bg }}</option>
                @endforeach
            </select>
        </div>
        <x-form.input name="cnic" label="CNIC" :value="old('cnic',$patient->cnic)" />
        <x-form.input name="email" label="Email" type="email" :value="old('email',$patient->email)" />
        <x-form.input name="occupation" label="Occupation" :value="old('occupation',$patient->occupation)" />
    </div>
    <div class="mt-4">
        <x-form.textarea name="address" label="Address" :value="old('address',$patient->address)" rows="2" />
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Emergency Contact</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-form.input name="emergency_contact_name" label="Name" :value="old('emergency_contact_name',$patient->emergency_contact_name)" />
        <x-form.input name="emergency_contact_phone" label="Phone" :value="old('emergency_contact_phone',$patient->emergency_contact_phone)" />
        <x-form.input name="emergency_contact_relation" label="Relation" :value="old('emergency_contact_relation',$patient->emergency_contact_relation)" />
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Medical History</h2>
    <div class="space-y-4">
        <x-form.textarea name="allergies" label="Allergies" :value="old('allergies',$patient->allergies)" rows="2" />
        <x-form.textarea name="chronic_diseases" label="Chronic Diseases" :value="old('chronic_diseases',$patient->chronic_diseases)" rows="2" />
        <x-form.textarea name="medical_notes" label="Notes" :value="old('medical_notes',$patient->medical_notes)" rows="2" />
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Update Patient</button>
    <a href="{{ route('patients.show', $patient) }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm rounded-lg hover:bg-slate-200">Cancel</a>
</div>

</form>
</div>
@endsection
