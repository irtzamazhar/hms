@extends('layouts.hms')
@section('title','Edit Ward')
@section('breadcrumb')
    <a href="{{ route('wards.index') }}" class="text-slate-400 hover:text-slate-600">Wards</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Edit {{ $ward->name }}</span>
@endsection

@section('content')
<div class="max-w-xl mx-auto">
<form method="POST" action="{{ route('wards.update',$ward) }}" class="space-y-4">
@csrf @method('PUT')
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">Edit Ward</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="name" label="Ward Name *" :value="old('name',$ward->name)" required />
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Ward Type</label>
            <select name="ward_type" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                @foreach(['general'=>'General','private'=>'Private','icu'=>'ICU','nicu'=>'NICU','emergency'=>'Emergency','maternity'=>'Maternity'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('ward_type',$ward->ward_type)===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
            <select name="is_active" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                <option value="1" @selected(old('is_active',$ward->is_active))>Active</option>
                <option value="0" @selected(!old('is_active',$ward->is_active))>Inactive</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Charge Per Day (₨)</label>
            <input type="number" name="charges_per_day" value="{{ old('charges_per_day',$ward->charges_per_day ?? 0) }}" min="0" step="0.01"
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
    </div>
    <x-form.textarea name="description" label="Description" :value="old('description',$ward->description)" rows="2" />
</div>
<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Update Ward</button>
    <a href="{{ route('wards.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm rounded-lg">Cancel</a>
</div>
</form>
</div>
@endsection
