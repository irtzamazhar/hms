@extends('layouts.hms')
@section('title','Expense — #'.$expense->id)
@section('breadcrumb')
    <a href="{{ route('expenses.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">Expenses</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $expense->title }}</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex justify-between items-start flex-wrap gap-3">
        <div>
            <h1 class="text-lg font-bold text-slate-800 dark:text-white">{{ $expense->title }}</h1>
            <p class="text-sm text-slate-400">{{ $expense->category->name ?? '—' }} · {{ $expense->expense_date->format('d M Y') }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <x-badge color="{{ ['approved'=>'green','pending'=>'amber','rejected'=>'red'][$expense->status] ?? 'slate' }}">{{ ucfirst($expense->status) }}</x-badge>
            @if($expense->status === 'pending')
            @can('edit expenses')
            <a href="{{ route('expenses.edit',$expense) }}" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">Edit</a>
            <form method="POST" action="{{ route('expenses.approve',$expense) }}" class="inline">
                @csrf @method('PATCH')
                <button class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg">Approve</button>
            </form>
            @endcan
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
        @foreach([
            ['Amount', '₨ '.number_format($expense->amount, 2)],
            ['Category', $expense->category->name ?? '—'],
            ['Expense Date', $expense->expense_date->format('d M Y')],
            ['Payment Method', ucfirst(str_replace('_',' ',$expense->payment_method ?? '—'))],
            ['Reference No.', $expense->reference_no ?? '—'],
            ['Module', ucfirst($expense->module ?? 'general')],
            ['Status', ucfirst($expense->status)],
            ['Added By', $expense->user->name ?? '—'],
            ['Approved By', $expense->approvedBy?->name ?? '—'],
        ] as [$l,$v])
        <div class="px-5 py-3 flex justify-between">
            <span class="text-sm text-slate-400">{{ $l }}</span>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $v }}</span>
        </div>
        @endforeach
        @if($expense->description)
        <div class="px-5 py-4">
            <p class="text-xs text-slate-400 mb-1">Description</p>
            <p class="text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line">{{ $expense->description }}</p>
        </div>
        @endif
    </div>

</div>
@endsection
