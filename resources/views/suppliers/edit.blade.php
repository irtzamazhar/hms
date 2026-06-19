@extends('layouts.hms')
@section('title','Edit Supplier')
@section('breadcrumb')
    <a href="{{ route('suppliers.index') }}" class="text-slate-400 hover:text-slate-600">Suppliers</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Edit</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Edit Supplier — {{ $supplier->name }}</h1>
    <a href="{{ route('suppliers.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
</div>

<form method="POST" action="{{ route('suppliers.update', $supplier) }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="name" label="Supplier Name" :value="$supplier->name" required />
        <x-form.input name="company" label="Company / Distributor" :value="$supplier->company" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="phone" label="Phone" :value="$supplier->phone" required />
        <x-form.input name="email" type="email" label="Email" :value="$supplier->email" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="contact_person" label="Contact Person" :value="$supplier->contact_person" />
        <x-form.input name="city" label="City" :value="$supplier->city" />
    </div>

    <x-form.textarea name="address" label="Address" :value="$supplier->address" rows="2" />

    <x-form.select name="status" label="Status" required>
        <option value="active" @selected($supplier->status === 'active')>Active</option>
        <option value="inactive" @selected($supplier->status === 'inactive')>Inactive</option>
    </x-form.select>

    <div class="flex justify-end gap-3 pt-2">
        <a href="{{ route('suppliers.index') }}" class="btn-cancel">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Update Supplier</button>
    </div>
</form>
</div>
@endsection
