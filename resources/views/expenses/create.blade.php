@extends('layouts.hms')
@section('title','Add Expense')
@section('breadcrumb')
    <a href="{{ route('expenses.index') }}" class="text-slate-400 hover:text-slate-600">Expenses</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Add</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Record Expense</h1>
    <a href="{{ route('expenses.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
</div>

<form method="POST" action="{{ route('expenses.store') }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    @csrf

    <x-form.input name="title" label="Expense Title" required />

    <div class="grid grid-cols-2 gap-4">
        <x-form.select name="expense_category_id" label="Category" required>
            <option value="">— Select —</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(old('expense_category_id') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </x-form.select>
        <x-form.select name="module" label="Module" required>
            @foreach(['hospital','pharmacy','laboratory','general'] as $m)
            <option value="{{ $m }}" @selected(old('module') === $m)>{{ ucfirst($m) }}</option>
            @endforeach
        </x-form.select>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-form.input name="amount" type="number" step="0.01" label="Amount (₨)" required />
        <x-form.input name="expense_date" type="date" label="Date" :value="today()->toDateString()" required />
    </div>

    <x-form.select name="payment_method" label="Payment Method" required>
        @foreach(['cash','bank_transfer','cheque','online'] as $m)
        <option value="{{ $m }}" @selected(old('payment_method') === $m)>{{ ucfirst(str_replace('_',' ',$m)) }}</option>
        @endforeach
    </x-form.select>

    <x-form.textarea name="description" label="Description / Notes" rows="3" />

    <div class="flex justify-end gap-3 pt-2">
        <a href="{{ route('expenses.index') }}" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Save Expense</button>
    </div>
</form>
</div>
@endsection
