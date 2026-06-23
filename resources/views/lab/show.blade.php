@extends('layouts.hms')
@section('title','Lab Booking — '.$booking->booking_number)
@section('breadcrumb')
    <a href="{{ route('lab.index') }}" class="text-slate-400 hover:text-slate-600">Lab</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $booking->booking_number }}</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    {{-- Header --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex flex-wrap justify-between items-start gap-4">
        <div>
            <p class="text-xs text-slate-400 font-mono">{{ $booking->booking_number }}</p>
            <h1 class="text-lg font-bold text-slate-800 dark:text-white">{{ $booking->patient->name }}</h1>
            <p class="text-sm text-slate-400">{{ $booking->patient->mr_number }} · {{ $booking->created_at->format('d M Y H:i') }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            @if($booking->status === 'completed')
            <a href="{{ route('lab.report.pdf',$booking) }}" target="_blank" class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg">📄 Report PDF</a>
            @endif
            <x-badge color="{{ ['completed'=>'green','processing'=>'blue','pending'=>'amber','cancelled'=>'red'][$booking->status] ?? 'slate' }}" class="text-sm">
                {{ ucfirst($booking->status) }}
            </x-badge>
        </div>
    </div>

    {{-- Tests & Results --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 font-semibold text-sm text-slate-700 dark:text-white">Tests & Results</div>
        @foreach($booking->items as $item)
        <div class="border-b border-slate-100 dark:border-slate-700 last:border-0">
            <div class="px-5 py-3 flex justify-between items-center">
                <div>
                    <p class="font-medium text-slate-700 dark:text-white">{{ $item->test->name }}</p>
                    <p class="text-xs text-slate-400">₨ {{ number_format($item->net_cost ?? $item->cost, 0) }}</p>
                </div>
                <x-badge color="{{ ['completed'=>'green','processing'=>'blue','pending'=>'amber'][$item->status ?? 'pending'] ?? 'slate' }}">
                    {{ ucfirst($item->status ?? 'pending') }}
                </x-badge>
            </div>

            {{-- Result entry (lab technician) --}}
            @can('enter lab results')
            @if(($item->status ?? 'pending') !== 'completed')
            <div class="px-5 pb-4">
                <form method="POST" action="{{ route('lab.results.save',$booking) }}">
                    @csrf
                    <input type="hidden" name="booking_item_id" value="{{ $item->id }}">
                    <div class="grid grid-cols-2 gap-3 mb-2">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Result Value</label>
                            <input type="text" name="result_value" value="{{ $item->report?->result_value }}" placeholder="e.g. 5.2 mmol/L"
                                   class="field">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Normal Range</label>
                            <input type="text" name="normal_range" value="{{ $item->report?->normal_range }}" placeholder="e.g. 3.9–6.1"
                                   class="field">
                        </div>
                    </div>
                    <textarea name="remarks" rows="2" placeholder="Remarks / interpretation…"
                              class="field mb-2">{{ $item->report?->remarks }}</textarea>
                    <button type="submit" class="px-4 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs rounded-lg font-medium">Save Result</button>
                </form>
            </div>
            @else
            <div class="px-5 pb-4 bg-green-50 dark:bg-green-900/10 mx-4 mb-3 rounded-lg">
                <p class="text-sm font-semibold text-slate-700 dark:text-white">{{ $item->report?->result_value ?? '—' }}</p>
                @if($item->report?->normal_range)
                <p class="text-xs text-slate-400">Normal: {{ $item->report->normal_range }}</p>
                @endif
                @if($item->report?->remarks)
                <p class="text-xs text-slate-500 mt-1 italic">{{ $item->report->remarks }}</p>
                @endif
            </div>
            @endif
            @endcan
        </div>
        @endforeach
    </div>

    {{-- Payment --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <h3 class="font-semibold text-sm text-slate-700 dark:text-white mb-3">Payment</h3>
        <div class="flex gap-6 flex-wrap">
            <div><p class="text-xs text-slate-400">Total</p><p class="font-bold text-slate-700 dark:text-white">₨ {{ number_format($booking->total_amount, 0) }}</p></div>
            @if(($booking->discount ?? 0) > 0)
            <div><p class="text-xs text-slate-400">Discount</p><p class="font-bold text-red-500">— ₨ {{ number_format($booking->discount, 0) }}</p></div>
            @endif
            <div><p class="text-xs text-slate-400">Net</p><p class="font-bold text-lg text-green-600">₨ {{ number_format($booking->net_amount, 0) }}</p></div>
            <div><p class="text-xs text-slate-400">Method</p><p class="font-bold text-slate-700 dark:text-white">{{ ucfirst(str_replace('_',' ',$booking->payment_method)) }}</p></div>
        </div>
    </div>

</div>
@endsection
