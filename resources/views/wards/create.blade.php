@extends('layouts.hms')
@section('title','Add Ward')
@section('breadcrumb')
    <a href="{{ route('wards.index') }}" class="text-slate-400 hover:text-slate-600">Wards</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Add Ward</span>
@endsection

@section('content')
<div class="max-w-xl mx-auto">
<form method="POST" action="{{ route('wards.store') }}" class="space-y-4">
@csrf
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">Ward Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="name" label="Ward Name *" :value="old('name')" required />
        <x-form.input name="code" label="Ward Code * (e.g. W-01)" :value="old('code')" required />
        <div>
            <label class="field-label">Ward Type *</label>
            <select name="ward_type" required class="field">
                @foreach(['general'=>'General','private'=>'Private','semi_private'=>'Semi Private','icu'=>'ICU','emergency'=>'Emergency','maternity'=>'Maternity','pediatric'=>'Pediatric'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('ward_type','general')===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Total Beds *</label>
            <input type="number" name="total_beds" value="{{ old('total_beds', 10) }}" min="1" max="200" required
                   class="field">
        </div>
        <div>
            <label class="field-label">Status</label>
            <select name="status" class="field">
                <option value="active" @selected(old('status','active')==='active')>Active</option>
                <option value="inactive" @selected(old('status')==='inactive')>Inactive</option>
            </select>
        </div>
        <div>
            <label class="field-label">Charge Per Day (₨)</label>
            <input type="number" name="charges_per_day" value="{{ old('charges_per_day', 1000) }}" min="0" step="0.01"
                   class="field">
        </div>
    </div>
    <x-form.textarea name="description" label="Description" :value="old('description')" rows="2" />
</div>
<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Create Ward</button>
    <a href="{{ route('wards.index') }}" class="btn-cancel">Cancel</a>
</div>
</form>
</div>
@endsection
