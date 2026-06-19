@extends('layouts.hms')
@section('title','Add Supplier')
@section('breadcrumb')
    <a href="{{ route('suppliers.index') }}" class="text-slate-400 hover:text-slate-600">Suppliers</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Add</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Add Supplier</h1>
    <a href="{{ route('suppliers.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
</div>

<form method="POST" action="{{ route('suppliers.store') }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    @csrf

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="name" label="Supplier Name" required />
        <x-form.input name="company" label="Company / Distributor" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="phone" label="Phone" required />
        <x-form.input name="email" type="email" label="Email" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="contact_person" label="Contact Person" />
        <x-form.input name="city" label="City" />
    </div>

    <x-form.textarea name="address" label="Address" rows="2" />

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="opening_balance" type="number" step="0.01" label="Opening Balance (₨)" :value="0" />
        <x-form.select name="status" label="Status" required>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </x-form.select>
    </div>

    <div class="flex justify-end gap-3 pt-2">
        <a href="{{ route('suppliers.index') }}" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm rounded-lg hover:bg-slate-50">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Save Supplier</button>
    </div>
</form>
</div>
@endsection
