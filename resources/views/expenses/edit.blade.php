@extends('layouts.hms')
@section('title','Edit Expense')
@section('breadcrumb')
    <a href="{{ route('expenses.index') }}" class="text-slate-400 hover:text-slate-600">Expenses</a> <span class="mx-1">/</span>
    <a href="{{ route('expenses.show',$expense) }}" class="text-slate-400 hover:text-slate-600">{{ $expense->title }}</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Edit</span>
@endsection

@section('content')
<div class="max-w-xl mx-auto">
<form method="POST" action="{{ route('expenses.update',$expense) }}" class="space-y-4">
@csrf @method('PUT')

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide">Edit Expense</h2>

    <x-form.input name="title" label="Title *" :value="old('title',$expense->title)" required />

    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Category *</label>
        <select name="expense_category_id" required class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(old('expense_category_id',$expense->expense_category_id)==$c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Amount (₨) *</label>
            <input type="number" name="amount" value="{{ old('amount',$expense->amount) }}" min="0" step="0.01" required
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Expense Date *</label>
            <input type="date" name="expense_date" value="{{ old('expense_date',$expense->expense_date->toDateString()) }}" required
                   class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
            <select name="payment_method" class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-primary-500">
                @foreach(['cash'=>'Cash','bank_transfer'=>'Bank Transfer','cheque'=>'Cheque','card'=>'Card'] as $v=>$l)
                    <option value="{{ $v }}" @selected(old('payment_method',$expense->payment_method)===$v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <x-form.input name="reference_no" label="Reference No." :value="old('reference_no',$expense->reference_no)" />
    </div>

    <x-form.textarea name="description" label="Description" :value="old('description',$expense->description)" rows="3" />
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg">Update Expense</button>
    <a href="{{ route('expenses.show',$expense) }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm rounded-lg">Cancel</a>
</div>

</form>
</div>
@endsection
