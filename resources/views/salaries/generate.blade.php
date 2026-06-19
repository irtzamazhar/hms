@extends('layouts.hms')
@section('title','Generate Salaries')
@section('breadcrumb')
    <a href="{{ route('salaries.index') }}" class="text-slate-400 hover:text-slate-600">Salaries</a>
    <span class="mx-1">/</span><span class="font-medium text-slate-700 dark:text-slate-200">Generate</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white">Generate Salary Slips</h1>
    <a href="{{ route('salaries.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Back</a>
</div>

<form method="POST" action="{{ route('salaries.generate.run') }}" x-data="{selectAll: true}" class="space-y-4">
    @csrf

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 grid grid-cols-2 gap-4">
        <x-form.select name="month" label="Month" required>
            @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" @selected(now()->month == $m)>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
            @endfor
        </x-form.select>
        <x-form.select name="year" label="Year" required>
            @for($y = now()->year; $y >= now()->year - 2; $y--)
            <option value="{{ $y }}" @selected(now()->year == $y)>{{ $y }}</option>
            @endfor
        </x-form.select>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <h2 class="font-semibold text-slate-800 dark:text-white">Select Employees</h2>
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                <input type="checkbox" x-model="selectAll" @change="document.querySelectorAll('[name=\'user_ids[]\']').forEach(c=>c.checked=selectAll)"
                       class="field text-primary-600">
                Select All
            </label>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50">
                <tr>
                    <th class="px-4 py-2 w-10"></th>
                    <th class="px-4 py-2 text-left text-xs text-slate-400 uppercase">Employee</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-400 uppercase">Role</th>
                    <th class="px-4 py-2 text-right text-xs text-slate-400 uppercase">Net Salary</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-400 uppercase">Structure</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($users as $u)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                    <td class="px-4 py-3 text-center">
                        @if($u->salaryStructure)
                        <input type="checkbox" name="user_ids[]" value="{{ $u->id }}" checked
                               class="field text-primary-600">
                        @else
                        <span class="text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800 dark:text-white">{{ $u->name }}</p>
                        <p class="text-xs text-slate-400">{{ $u->employee_id }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400 capitalize">{{ $u->user_type }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-white">
                        {{ $u->salaryStructure ? '₨ '.number_format($u->salaryStructure->net_salary, 0) : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($u->salaryStructure)
                        <x-badge color="green">Active</x-badge>
                        @else
                        <x-badge color="amber">Not Set</x-badge>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">Generate Salary Slips</button>
    </div>
</form>
</div>
@endsection
