@extends('layouts.hms')
@section('title','Update Payment — '.$purchase->purchase_number)
@section('breadcrumb')
    <a href="{{ route('purchases.index') }}" class="text-slate-400 hover:text-slate-600">Purchases</a>
    <span class="mx-1">/</span>
    <a href="{{ route('purchases.show', $purchase) }}" class="text-slate-400 hover:text-slate-600">{{ $purchase->purchase_number }}</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Update Payment</span>
@endsection

@section('content')
<div class="max-w-lg mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Update Payment</h1>
    <a href="{{ route('purchases.show', $purchase) }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 mb-4 text-sm">
    <div class="flex justify-between mb-2"><span class="text-slate-400">Purchase #</span><span class="font-mono font-medium text-slate-700 dark:text-white">{{ $purchase->purchase_number }}</span></div>
    <div class="flex justify-between mb-2"><span class="text-slate-400">Supplier</span><span class="text-slate-700 dark:text-white">{{ $purchase->supplier?->name }}</span></div>
    <div class="flex justify-between mb-2"><span class="text-slate-400">Total Amount</span><span class="font-bold text-slate-800 dark:text-white">₨ {{ number_format($purchase->total_amount, 0) }}</span></div>
    <div class="flex justify-between"><span class="text-slate-400">Currently Paid</span><span class="text-green-600 font-semibold">₨ {{ number_format($purchase->paid_amount, 0) }}</span></div>
</div>

<form method="POST" action="{{ route('purchases.update', $purchase) }}"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
    @csrf
    @method('PUT')

    <x-form.input name="paid_amount" type="number" step="0.01" label="Updated Paid Amount (₨)"
                  :value="$purchase->paid_amount" required />

    <x-form.select name="payment_status" label="Payment Status" required>
        @foreach(['pending','partial','paid'] as $s)
        <option value="{{ $s }}" @selected($purchase->payment_status === $s)>{{ ucfirst($s) }}</option>
        @endforeach
    </x-form.select>

    <x-form.textarea name="notes" label="Notes" :value="$purchase->notes" rows="2" />

    <div class="flex justify-end gap-3 pt-2">
        <a href="{{ route('purchases.show', $purchase) }}" class="btn-cancel">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Update Payment</button>
    </div>
</form>
</div>
@endsection
