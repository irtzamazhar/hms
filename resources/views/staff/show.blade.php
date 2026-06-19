@extends('layouts.hms')
@section('title',$staff->user->name)
@section('breadcrumb')
    <a href="{{ route('staff.index') }}" class="text-slate-400 hover:text-slate-600">Staff</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $staff->user->name }}</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex justify-between items-start flex-wrap gap-3">
        <div class="flex gap-4 items-center">
            <div class="field w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center text-xl font-bold text-slate-600 dark:text-slate-300">{{ substr($staff->user->name,0,1) }}</div>
            <div>
                <h1 class="text-lg font-bold text-slate-800 dark:text-white">{{ $staff->user->name }}</h1>
                <p class="text-sm text-slate-400">{{ ucfirst(str_replace('_',' ',$staff->position ?? '')) }} · {{ $staff->department->name ?? '—' }}</p>
                <p class="text-sm text-slate-400">{{ $staff->user->email }}</p>
            </div>
        </div>
        @can('update staff')
        <a href="{{ route('staff.edit',$staff) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">Edit</a>
        @endcan
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
        <div class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Staff Details</div>
        @foreach([
            ['Role', ucfirst(str_replace('_',' ',$staff->user->user_type ?? '—'))],
            ['Employee ID', $staff->user->employee_id ?? '—'],
            ['Phone', $staff->user->phone ?? '—'],
            ['Department', $staff->department->name ?? '—'],
            ['Position', $staff->position ?? '—'],
            ['Basic Salary', $staff->basic_salary ? '₨ '.number_format($staff->basic_salary,0) : '—'],
            ['Joining Date', $staff->user->joining_date?->format('d M Y') ?? '—'],
            ['Status', ($staff->is_active ?? true) ? 'Active' : 'Inactive'],
        ] as [$l,$v])
        <div class="px-5 py-2.5 flex justify-between">
            <span class="text-sm text-slate-400">{{ $l }}</span>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $v }}</span>
        </div>
        @endforeach
    </div>

</div>
@endsection
