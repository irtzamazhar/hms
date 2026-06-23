@extends('layouts.hms')
@section('title','Salary Structures')
@section('breadcrumb')
    <a href="{{ route('salaries.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Salaries</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Salary Structure</span>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Salary Structures</h1>
    <a href="{{ route('salaries.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-200">← Back</a>
</div>

<div class="space-y-4">
@forelse($users as $user)
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden" x-data="{open:false}">
    <div class="px-5 py-3 flex justify-between items-center cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/30" @click="open=!open">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 font-bold text-sm">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <p class="font-semibold text-slate-800 dark:text-white">{{ $user->name }}</p>
                <p class="text-xs text-slate-400">{{ $user->employee_id }} · {{ ucfirst($user->user_type) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            @if($user->salaryStructure)
            <div class="text-right">
                <p class="text-xs text-slate-400">Net Salary</p>
                <p class="font-bold text-green-600">₨ {{ number_format($user->salaryStructure->net_salary, 0) }}</p>
            </div>
            @else
            <span class="text-xs text-amber-600 bg-amber-50 dark:bg-amber-900/30 px-2 py-1 rounded">No Structure</span>
            @endif
            <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
    </div>

    <div x-show="open" x-transition class="border-t border-slate-200 dark:border-slate-700 p-5">
        <form method="POST" action="{{ route('salaries.structure.save', $user) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <x-form.input name="basic_salary" type="number" step="0.01" label="Basic Salary ₨" :value="$user->salaryStructure?->basic_salary ?? 0" required />
                <x-form.input name="house_allowance" type="number" step="0.01" label="House Allowance ₨" :value="$user->salaryStructure?->house_allowance ?? 0" />
                <x-form.input name="transport_allowance" type="number" step="0.01" label="Transport Allowance ₨" :value="$user->salaryStructure?->transport_allowance ?? 0" />
                <x-form.input name="medical_allowance" type="number" step="0.01" label="Medical Allowance ₨" :value="$user->salaryStructure?->medical_allowance ?? 0" />
                <x-form.input name="other_allowances" type="number" step="0.01" label="Other Allowances ₨" :value="$user->salaryStructure?->other_allowances ?? 0" />
            </div>
            <div class="border-t border-slate-200 dark:border-slate-700 pt-3 grid grid-cols-2 md:grid-cols-3 gap-4">
                <x-form.input name="income_tax_deduction" type="number" step="0.01" label="Income Tax ₨" :value="$user->salaryStructure?->income_tax_deduction ?? 0" />
                <x-form.input name="provident_fund_deduction" type="number" step="0.01" label="Provident Fund ₨" :value="$user->salaryStructure?->provident_fund_deduction ?? 0" />
                <x-form.input name="other_deductions" type="number" step="0.01" label="Other Deductions ₨" :value="$user->salaryStructure?->other_deductions ?? 0" />
            </div>
            <x-form.input name="effective_from" type="date" label="Effective From" :value="$user->salaryStructure?->effective_from?->toDateString() ?? today()->toDateString()" required />
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Save Structure</button>
            </div>
        </form>
    </div>
</div>
@empty
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-12 text-center">
    <p class="text-slate-400">No employees found.</p>
</div>
@endforelse
</div>
@endsection
