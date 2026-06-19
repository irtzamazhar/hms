@extends('layouts.hms')
@section('title','Add Department')
@section('breadcrumb')
    <a href="{{ route('departments.index') }}" class="text-slate-400 hover:text-slate-600">Departments</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Add</span>
@endsection

@section('content')
<div class="max-w-lg mx-auto">
<form method="POST" action="{{ route('departments.store') }}" class="space-y-4">
@csrf
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">New Department</h2>
    <x-form.input name="name" label="Department Name *" :value="old('name')" required />
    <x-form.input name="code" label="Code (e.g. OPD, SURG) *" :value="old('code')" required />
    <x-form.textarea name="description" label="Description" :value="old('description')" rows="2" />
    <div>
        <label class="field-label">Status</label>
        <select name="status" class="field">
            <option value="active" @selected(old('status','active')==='active')>Active</option>
            <option value="inactive" @selected(old('status')==='inactive')>Inactive</option>
        </select>
    </div>
    <div>
        <label class="field-label">Head of Department</label>
        <select name="head_doctor_id" class="field">
            <option value="">— None —</option>
            @foreach($doctors as $d)
                <option value="{{ $d->id }}" @selected(old('head_doctor_id')==$d->id)>Dr. {{ $d->user->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Create</button>
    <a href="{{ route('departments.index') }}" class="btn-cancel">Cancel</a>
</div>
</form>
</div>
@endsection
