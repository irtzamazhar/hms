@extends('layouts.hms')
@section('title','Edit Medicine')
@section('breadcrumb')
    <a href="{{ route('medicines.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Medicines</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Edit {{ $medicine->name }}</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<form method="POST" action="{{ route('medicines.update',$medicine) }}" class="space-y-4">
@csrf @method('PUT')

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Medicine Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="name" label="Medicine Name *" :value="old('name',$medicine->name)" required />
        <x-form.input name="generic_name" label="Generic Name" :value="old('generic_name',$medicine->generic_name)" />
        <div>
            <label class="field-label">Category</label>
            <select name="medicine_category_id" class="field">
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('medicine_category_id',$medicine->medicine_category_id)==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Dosage Form</label>
            <select name="dosage_form" class="field">
                @foreach(['tablet'=>'Tablet','capsule'=>'Capsule','syrup'=>'Syrup','injection'=>'Injection','cream'=>'Cream','drops'=>'Drops','inhaler'=>'Inhaler','suppository'=>'Suppository','other'=>'Other'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('dosage_form',$medicine->dosage_form)===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <x-form.input name="strength" label="Strength" :value="old('strength',$medicine->strength)" />
        <x-form.input name="unit" label="Unit" :value="old('unit',$medicine->unit)" />
        <div>
            <label class="field-label">Supplier</label>
            <select name="supplier_id" class="field">
                <option value="">— None —</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" @selected(old('supplier_id',$medicine->supplier_id)==$s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Status</label>
            <select name="status" class="field">
                <option value="active" @selected(old('status',$medicine->status)==='active')>Active</option>
                <option value="inactive" @selected(old('status',$medicine->status)==='inactive')>Inactive</option>
                <option value="discontinued" @selected(old('status',$medicine->status)==='discontinued')>Discontinued</option>
            </select>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-4">Pricing & Stock</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div>
            <label class="field-label">Purchase Price</label>
            <input type="number" name="purchase_price" value="{{ old('purchase_price',$medicine->purchase_price) }}" step="0.01"
                   class="field">
        </div>
        <div>
            <label class="field-label">Selling Price</label>
            <input type="number" name="sale_price" value="{{ old('sale_price',$medicine->sale_price) }}" step="0.01"
                   class="field">
        </div>
        <div>
            <label class="field-label">Min. Stock Alert</label>
            <input type="number" name="minimum_stock" value="{{ old('minimum_stock',$medicine->minimum_stock) }}" min="0"
                   class="field">
        </div>
    </div>
    <div class="mt-4">
        <x-form.textarea name="description" label="Description" :value="old('description',$medicine->description)" rows="2" />
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Update Medicine</button>
    <a href="{{ route('medicines.index') }}" class="btn-cancel">Cancel</a>
</div>

</form>
</div>
@endsection
