@extends('layouts.hms')
@section('title','New Purchase Order')
@section('breadcrumb')
    <a href="{{ route('purchases.index') }}" class="text-slate-400 hover:text-slate-600">Purchases</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">New</span>
@endsection

@section('content')
<div x-data="purchaseForm()" class="space-y-4">
<div class="flex items-center justify-between mb-2">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">New Purchase Order</h1>
    <a href="{{ route('purchases.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
</div>

<form method="POST" action="{{ route('purchases.store') }}" class="space-y-4" @submit="prepareSubmit">
    @csrf

    {{-- Header --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 grid grid-cols-2 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Supplier <span class="text-red-500">*</span></label>
            <select name="supplier_id" required class="w-full text-sm rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                <option value="">— Select Supplier —</option>
                @foreach($suppliers as $s)
                <option value="{{ $s->id }}" @selected(request('supplier_id') == $s->id)>{{ $s->name }}@if($s->company) — {{ $s->company }}@endif</option>
                @endforeach
            </select>
        </div>
        <x-form.input name="purchase_date" type="date" label="Purchase Date" :value="today()->toDateString()" required />
        <x-form.input name="invoice_number" label="Invoice / Bill No." />
        <x-form.select name="payment_method" label="Payment Method" required>
            @foreach(['cash','bank_transfer','cheque','credit'] as $m)
            <option value="{{ $m }}">{{ ucfirst(str_replace('_',' ',$m)) }}</option>
            @endforeach
        </x-form.select>
        <x-form.input name="paid_amount" type="number" step="0.01" label="Amount Paid (₨)" :value="0" />
    </div>

    {{-- Items --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <h2 class="font-semibold text-slate-800 dark:text-white">Purchase Items</h2>
            <button type="button" @click="addRow()" class="text-xs px-3 py-1.5 bg-primary-600 text-white rounded-lg">+ Add Row</button>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs text-slate-400">Medicine</th>
                    <th class="px-3 py-2 text-center text-xs text-slate-400">Qty</th>
                    <th class="px-3 py-2 text-right text-xs text-slate-400">Unit Price</th>
                    <th class="px-3 py-2 text-right text-xs text-slate-400">Sale Price</th>
                    <th class="px-3 py-2 text-left text-xs text-slate-400">Batch #</th>
                    <th class="px-3 py-2 text-left text-xs text-slate-400">Expiry</th>
                    <th class="px-3 py-2 text-right text-xs text-slate-400">Total</th>
                    <th class="px-3 py-2 w-8"></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, i) in rows" :key="i">
                <tr>
                    <td class="px-3 py-2">
                        <select :name="'items['+i+'][medicine_id]'" x-model="row.medicine_id" required class="w-full text-xs rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                            <option value="">— Select —</option>
                            @foreach($medicines as $med)
                            <option value="{{ $med->id }}">{{ $med->name }}@if($med->generic_name) ({{ $med->generic_name }})@endif</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2"><input :name="'items['+i+'][quantity]'" type="number" min="1" x-model.number="row.qty" @input="calc()" required class="w-20 text-center text-xs rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white"></td>
                    <td class="px-3 py-2"><input :name="'items['+i+'][unit_price]'" type="number" step="0.01" min="0" x-model.number="row.price" @input="calc()" required class="w-24 text-right text-xs rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white"></td>
                    <td class="px-3 py-2"><input :name="'items['+i+'][sale_price]'" type="number" step="0.01" min="0" x-model.number="row.salePrice" required class="w-24 text-right text-xs rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white"></td>
                    <td class="px-3 py-2"><input :name="'items['+i+'][batch_number]'" type="text" x-model="row.batch" class="w-28 text-xs rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white"></td>
                    <td class="px-3 py-2"><input :name="'items['+i+'][expiry_date]'" type="date" x-model="row.expiry" class="text-xs rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white"></td>
                    <td class="px-3 py-2 text-right font-semibold text-slate-700 dark:text-white" x-text="'₨ '+lineTotal(row)"></td>
                    <td class="px-3 py-2"><button type="button" @click="rows.splice(i,1);calc()" class="text-red-400 hover:text-red-600">&times;</button></td>
                </tr>
                </template>
            </tbody>
        </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <span class="text-sm text-slate-400">{{ count($medicines) }} medicines available</span>
            <div class="text-right">
                <span class="text-slate-500 text-sm">Subtotal: </span>
                <span class="text-lg font-bold text-slate-800 dark:text-white" x-text="'₨ '+subtotal.toLocaleString()"></span>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <x-form.textarea name="notes" label="Notes" rows="2" />
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('purchases.index') }}" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm rounded-lg">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Save Purchase Order</button>
    </div>
</form>
</div>
@endsection

@push('scripts')
<script>
function purchaseForm() {
    return {
        rows: [{ medicine_id: '', qty: 1, price: 0, salePrice: 0, batch: '', expiry: '' }],
        subtotal: 0,
        lineTotal(row) { return (row.qty * row.price).toLocaleString('en-PK', {maximumFractionDigits: 0}); },
        addRow() { this.rows.push({ medicine_id: '', qty: 1, price: 0, salePrice: 0, batch: '', expiry: '' }); },
        calc() { this.subtotal = this.rows.reduce((s, r) => s + (r.qty * r.price), 0); },
        prepareSubmit() { this.calc(); }
    }
}
</script>
@endpush
