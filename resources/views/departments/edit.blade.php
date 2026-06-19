@extends('layouts.hms')
@section('title','Edit Department')
@section('breadcrumb')
    <a href="{{ route('departments.index') }}" class="text-slate-400 hover:text-slate-600">Departments</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Edit {{ $department->name }}</span>
@endsection

@section('content')
<div class="max-w-lg mx-auto">
<form method="POST" action="{{ route('departments.update',$department) }}" class="space-y-4">
@csrf @method('PUT')
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">Edit Department</h2>
    <x-form.input name="name" label="Department Name *" :value="old('name',$department->name)" required />
    <x-form.input name="code" label="Code" :value="old('code',$department->code)" />
    <x-form.textarea name="description" label="Description" :value="old('description',$department->description)" rows="2" />
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
        <select name="is_active" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
            <option value="1" @selected(old('is_active',$department->is_active))>Active</option>
            <option value="0" @selected(!old('is_active',$department->is_active))>Inactive</option>
        </select>
    </div>
</div>
<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Update</button>
    <a href="{{ route('departments.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm rounded-lg">Cancel</a>
</div>
</form>
</div>
@endsection
