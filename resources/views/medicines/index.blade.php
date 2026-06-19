@extends('layouts.hms')

@section('title', 'Medicines')
@section('breadcrumb')
    <span class="text-slate-400 dark:text-slate-600">Home</span>
    <svg class="w-3.5 h-3.5 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-600 dark:text-slate-300 font-medium">Medicines</span>
@endsection

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-lg font-bold text-slate-800 dark:text-white">Medicines</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $medicines->total() }} medicines in inventory</p>
    </div>
    @can('manage medicines')
    <a href="{{ route('medicines.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Medicine
    </a>
    @endcan
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('medicines.index') }}"
      class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 p-4 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="col-span-2">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search name, brand, SKU, generic…"
                       class="field pl-9">
            </div>
        </div>

        <select name="category_id" class="field">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>

        <select name="status" class="field">
            <option value="">All Status</option>
            <option value="active"   @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>

        <div class="flex gap-2">
            <button type="submit"
                    class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors">
                Filter
            </button>
            <a href="{{ route('medicines.index') }}"
               class="btn-cancel">
                Reset
            </a>
        </div>
    </div>

    {{-- Low stock toggle --}}
    <div class="mt-3 flex items-center gap-2">
        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" name="low_stock" value="1" @checked(request('low_stock'))
                   onchange="this.form.submit()"
                   class="field bg-white dark:bg-dark-900 text-red-500 focus:ring-red-500 focus:ring-offset-0">
            <span class="text-sm text-slate-500 dark:text-slate-400 font-medium">Show low-stock only</span>
        </label>
    </div>
</form>

{{-- Table --}}
<div class="bg-white dark:bg-dark-800 rounded-xl border border-slate-200 dark:border-dark-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-dark-700 bg-slate-50 dark:bg-dark-900/50">
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Medicine</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Category</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Unit</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Stock</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Sale Price</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Purchase</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-dark-700/50">
                @forelse($medicines as $medicine)
                @php $isLow = $medicine->isLowStock(); @endphp
                <tr class="hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors {{ $isLow ? 'bg-red-50/30 dark:bg-red-900/5' : '' }}">

                    {{-- Medicine name + brand --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
                                {{ $isLow ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : 'bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <p class="font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $medicine->name }}</p>
                                    @if($medicine->is_controlled)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 font-semibold">Ctrl</span>
                                    @endif
                                    @if($medicine->requires_prescription)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-semibold">Rx</span>
                                    @endif
                                    @if($isLow)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 font-semibold">Low Stock</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    @if($medicine->generic_name)
                                    <p class="text-[11px] text-slate-400 truncate">{{ $medicine->generic_name }}</p>
                                    @endif
                                    @if($medicine->brand)
                                    <p class="text-[11px] text-slate-400">· {{ $medicine->brand }}</p>
                                    @endif
                                    @if($medicine->sku)
                                    <p class="text-[11px] font-mono text-slate-400">· {{ $medicine->sku }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Category --}}
                    <td class="px-4 py-3">
                        @if($medicine->category)
                        <span class="text-xs px-2 py-1 rounded-lg bg-slate-100 dark:bg-dark-700 text-slate-600 dark:text-slate-300 font-medium">
                            {{ $medicine->category->name }}
                        </span>
                        @else
                        <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>

                    {{-- Unit --}}
                    <td class="px-4 py-3">
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium uppercase">{{ $medicine->unit }}</span>
                    </td>

                    {{-- Stock --}}
                    <td class="px-4 py-3 text-right">
                        <div>
                            <p class="font-bold text-sm {{ $isLow ? 'text-red-600 dark:text-red-400' : 'text-slate-700 dark:text-slate-200' }}">
                                {{ number_format($medicine->stock_quantity) }}
                            </p>
                            @if($medicine->minimum_stock)
                            <p class="text-[10px] text-slate-400">min {{ $medicine->minimum_stock }}</p>
                            @endif
                        </div>
                    </td>

                    {{-- Sale Price --}}
                    <td class="px-4 py-3 text-right">
                        <p class="font-semibold text-slate-700 dark:text-slate-200">₨ {{ number_format($medicine->sale_price, 2) }}</p>
                    </td>

                    {{-- Purchase Price --}}
                    <td class="px-4 py-3 text-right">
                        <p class="text-slate-500 dark:text-slate-400">₨ {{ number_format($medicine->purchase_price, 2) }}</p>
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-3 text-center">
                        <span class="text-[11px] px-2 py-0.5 rounded-md font-semibold
                            {{ $medicine->status === 'active'
                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                : 'bg-slate-100 text-slate-500 dark:bg-dark-700 dark:text-slate-400' }}">
                            {{ ucfirst($medicine->status) }}
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 justify-end">
                            <a href="{{ route('medicines.show', $medicine) }}"
                               class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-dark-700 hover:text-slate-700 dark:hover:text-slate-200 transition-colors" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @can('manage medicines')
                            <a href="{{ route('medicines.edit', $medicine) }}"
                               class="p-1.5 rounded-lg text-slate-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('medicines.destroy', $medicine) }}"
                                  onsubmit="return confirm('Delete {{ addslashes($medicine->name) }}?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-16 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-dark-700 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">No medicines found</p>
                        <p class="text-xs text-slate-400 mt-1">
                            @if(request()->hasAny(['search','category_id','status','low_stock']))
                                Try adjusting your filters.
                                <a href="{{ route('medicines.index') }}" class="text-blue-500 hover:underline ml-1">Clear filters</a>
                            @else
                                @can('manage medicines')
                                <a href="{{ route('medicines.create') }}" class="text-blue-500 hover:underline">Add your first medicine →</a>
                                @endcan
                            @endif
                        </p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($medicines->hasPages())
    <div class="px-4 py-3 border-t border-slate-100 dark:border-dark-700 flex items-center justify-between">
        <p class="text-xs text-slate-400">
            Showing {{ $medicines->firstItem() }}–{{ $medicines->lastItem() }} of {{ $medicines->total() }}
        </p>
        {{ $medicines->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
