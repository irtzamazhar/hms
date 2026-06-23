@extends('layouts.hms')
@section('title','Edit Lab Test')
@section('breadcrumb')
    <a href="{{ route('lab.tests.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Lab Tests</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Edit</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Edit Lab Test — {{ $labTest->name }}</h1>
    <a href="{{ route('lab.tests.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-200">← Back</a>
</div>

<form method="POST" action="{{ route('lab.tests.update', $labTest) }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="name" label="Test Name" :value="$labTest->name" required />
        <x-form.input name="code" label="Test Code" :value="$labTest->code" required />
    </div>

    <x-form.select name="category_id" label="Category" required>
        <option value="">— Select Category —</option>
        @foreach($categories as $cat)
        <option value="{{ $cat->id }}" @selected($labTest->category_id == $cat->id)>{{ $cat->name }}</option>
        @endforeach
    </x-form.select>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="cost" type="number" step="0.01" label="Cost (₨)" :value="$labTest->cost" required />
        <x-form.input name="turnaround_hours" type="number" label="Turnaround (hours)" :value="$labTest->turnaround_hours" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="sample_type" label="Sample Type" :value="$labTest->sample_type" />
        <x-form.input name="unit" label="Result Unit" :value="$labTest->unit" />
    </div>

    <x-form.input name="normal_range" label="Normal Range" :value="$labTest->normal_range" />
    <x-form.textarea name="preparation_instructions" label="Preparation Instructions" :value="$labTest->preparation_instructions" rows="2" />

    <x-form.select name="status" label="Status" required>
        <option value="active" @selected($labTest->status === 'active')>Active</option>
        <option value="inactive" @selected($labTest->status === 'inactive')>Inactive</option>
    </x-form.select>

    <div class="flex justify-between items-center pt-2">
        <button type="submit" form="deleteTestForm" onclick="return confirm('Delete this test? This cannot be undone.')"
                class="text-sm text-red-500 hover:text-red-700">Delete Test</button>
        <div class="flex gap-3">
            <a href="{{ route('lab.tests.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Update Test</button>
        </div>
    </div>
</form>

{{-- Separate, non-nested form for deletion (referenced by the Delete button above via form=) --}}
<form id="deleteTestForm" method="POST" action="{{ route('lab.tests.destroy', $labTest) }}" class="hidden">
    @csrf @method('DELETE')
</form>
</div>
@endsection
