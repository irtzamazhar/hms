@extends('layouts.hms')
@section('title','Close Shift')
@section('breadcrumb')
    <a href="{{ route('shifts.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Shifts</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Close Shift</span>
@endsection

@section('content')
<div class="grid md:grid-cols-2 gap-4">
    {{-- Close form --}}
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white mb-4">Close Shift</h1>
        <form method="POST" action="{{ route('shifts.close') }}"
              class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Shift <span class="text-red-500">*</span></label>
                <select name="shift_id" required class="field">
                    <option value="">— Select Shift —</option>
                    @foreach($shifts as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <x-form.input name="closing_date" type="date" label="Closing Date" :value="today()->toDateString()" required />

            <div class="border-t border-slate-200 dark:border-slate-700 pt-3">
                <p class="text-xs font-medium text-slate-400 uppercase mb-3">Revenue Breakdown</p>
                <div class="grid grid-cols-2 gap-3">
                    <x-form.input name="opd_revenue" type="number" step="0.01" label="OPD Revenue ₨" :value="0" />
                    <x-form.input name="ipd_revenue" type="number" step="0.01" label="IPD Revenue ₨" :value="0" />
                    <x-form.input name="pharmacy_revenue" type="number" step="0.01" label="Pharmacy Revenue ₨" :value="0" />
                    <x-form.input name="lab_revenue" type="number" step="0.01" label="Lab Revenue ₨" :value="0" />
                    <x-form.input name="other_revenue" type="number" step="0.01" label="Other Revenue ₨" :value="0" />
                    <x-form.input name="total_expenses" type="number" step="0.01" label="Total Expenses ₨" :value="0" />
                </div>
            </div>

            <x-form.textarea name="notes" label="Notes" rows="2" />

            <button type="submit" class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm rounded-lg">Close Shift</button>
        </form>
    </div>

    {{-- Recent closings --}}
    <div>
        <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-4">Recent Closings</h2>
        <div class="space-y-3">
            @forelse($closings as $c)
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="font-semibold text-slate-800 dark:text-white">{{ $c->shift?->name }}</p>
                        <p class="text-xs text-slate-400">{{ $c->closing_date->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-400">Net Profit</p>
                        <p class="font-bold {{ $c->net_profit >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            ₨ {{ number_format($c->net_profit, 0) }}
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <span class="text-slate-400">Revenue: <span class="text-green-600 font-medium">₨ {{ number_format($c->total_revenue, 0) }}</span></span>
                    <span class="text-slate-400">Expenses: <span class="text-red-500 font-medium">₨ {{ number_format($c->total_expenses, 0) }}</span></span>
                </div>
            </div>
            @empty
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-8 text-center">
                <p class="text-slate-400 text-sm">No shift closings recorded yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
