@extends('layouts.hms')
@section('title','Supplier — '.$supplier->name)
@section('breadcrumb')
    <a href="{{ route('suppliers.index') }}" class="text-slate-400 hover:text-slate-600">Suppliers</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">{{ $supplier->name }}</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">{{ $supplier->name }}</h1>
    <div class="flex gap-2">
        @can('manage pharmacy')
        <a href="{{ route('suppliers.edit', $supplier) }}" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm rounded-lg hover:bg-slate-50">Edit</a>
        @endcan
        <a href="{{ route('purchases.create') }}?supplier_id={{ $supplier->id }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg">New Purchase</a>
    </div>
</div>

<div class="grid md:grid-cols-3 gap-4 mb-6">
    {{-- Info card --}}
    <div class="md:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-3">Supplier Details</h2>
        <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
            @foreach([
                ['Company', $supplier->company ?? '—'],
                ['Phone', $supplier->phone],
                ['Email', $supplier->email ?? '—'],
                ['Contact Person', $supplier->contact_person ?? '—'],
                ['City', $supplier->city ?? '—'],
                ['Status', ucfirst($supplier->status)],
            ] as [$k,$v])
            <div><dt class="text-slate-400">{{ $k }}</dt><dd class="font-medium text-slate-700 dark:text-white mt-0.5">{{ $v }}</dd></div>
            @endforeach
        </dl>
        @if($supplier->address)
        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
            <dt class="text-xs text-slate-400">Address</dt>
            <dd class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">{{ $supplier->address }}</dd>
        </div>
        @endif
    </div>

    {{-- Stats --}}
    <div class="space-y-3">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-xs text-slate-400">Total Purchases</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $supplier->purchases->count() }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-xs text-slate-400">Total Amount</p>
            <p class="text-xl font-bold text-green-600 mt-1">₨ {{ number_format($supplier->total_purchases, 0) }}</p>
        </div>
    </div>
</div>

{{-- Recent Purchases --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700">
        <h2 class="font-semibold text-slate-800 dark:text-white">Recent Purchases</h2>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">PO #</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Date</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Amount</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($supplier->purchases as $p)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $p->purchase_number }}</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $p->purchase_date->format('d M Y') }}</td>
                <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-white">₨ {{ number_format($p->total_amount, 0) }}</td>
                <td class="px-4 py-3"><x-badge color="{{ ['paid'=>'green','partial'=>'amber','pending'=>'red'][$p->payment_status] ?? 'slate' }}">{{ ucfirst($p->payment_status) }}</x-badge></td>
                <td class="px-4 py-3 text-right"><a href="{{ route('purchases.show', $p) }}" class="text-xs text-primary-600 hover:underline">View</a></td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No purchases yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
