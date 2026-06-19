@extends('layouts.hms')
@section('title','Add Medicine')
@section('breadcrumb')
    <a href="{{ route('medicines.index') }}" class="text-slate-400 hover:text-slate-600">Medicines</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Add New</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<form method="POST" action="{{ route('medicines.store') }}" class="space-y-4">
@csrf

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Medicine Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="name" label="Medicine Name *" :value="old('name')" required />
        <x-form.input name="generic_name" label="Generic Name" :value="old('generic_name')" />
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Category *</label>
            <select name="medicine_category_id" required class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                <option value="">Select…</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('medicine_category_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('medicine_category_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Dosage Form</label>
            <select name="dosage_form" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                @foreach(['tablet'=>'Tablet','capsule'=>'Capsule','syrup'=>'Syrup','injection'=>'Injection','cream'=>'Cream','drops'=>'Drops','inhaler'=>'Inhaler','suppository'=>'Suppository','other'=>'Other'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('dosage_form','tablet')===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <x-form.input name="strength" label="Strength (e.g. 500mg)" :value="old('strength')" />
        <x-form.input name="unit" label="Unit (e.g. piece, bottle)" :value="old('unit','piece')" />
        <x-form.input name="barcode" label="Barcode (optional)" :value="old('barcode')" />
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Supplier</label>
            <select name="supplier_id" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                <option value="">— None —</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" @selected(old('supplier_id')==$s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Pricing & Stock</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Purchase Price *</label>
            <input type="number" name="purchase_price" value="{{ old('purchase_price') }}" min="0" step="0.01" required
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Selling Price *</label>
            <input type="number" name="selling_price" value="{{ old('selling_price') }}" min="0" step="0.01" required
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Min. Stock Alert</label>
            <input type="number" name="minimum_stock" value="{{ old('minimum_stock', 10) }}" min="0"
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Opening Stock</label>
            <input type="number" name="opening_stock" value="{{ old('opening_stock', 0) }}" min="0"
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Requires Prescription?</label>
            <select name="is_prescription_required" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                <option value="0" @selected(!old('is_prescription_required'))>No</option>
                <option value="1" @selected(old('is_prescription_required'))>Yes</option>
            </select>
        </div>
    </div>
    <div class="mt-4">
        <x-form.textarea name="description" label="Description / Notes" :value="old('description')" rows="2" />
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Add Medicine</button>
    <a href="{{ route('medicines.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm rounded-lg">Cancel</a>
</div>

</form>
</div>
@endsection
