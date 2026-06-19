@extends('layouts.hms')
@section('title','Dr. '.$doctor->user->name)
@section('breadcrumb')
    <a href="{{ route('doctors.index') }}" class="text-slate-400 hover:text-slate-600">Doctors</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Dr. {{ $doctor->user->name }}</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex justify-between items-start flex-wrap gap-3">
        <div class="flex gap-4 items-center">
            <div class="w-14 h-14 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-xl font-bold text-primary-600">{{ substr($doctor->user->name,0,1) }}</div>
            <div>
                <h1 class="text-lg font-bold text-slate-800 dark:text-white">Dr. {{ $doctor->user->name }}</h1>
                <p class="text-sm text-slate-400">{{ $doctor->specialization }} · {{ $doctor->department->name ?? '—' }}</p>
                <p class="text-sm text-slate-400">{{ $doctor->user->email }}</p>
            </div>
        </div>
        @can('update doctors')
        <a href="{{ route('doctors.edit',$doctor) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">Edit</a>
        @endcan
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
            <div class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Professional</div>
            @foreach([
                ['Qualification', $doctor->qualification ?? '—'],
                ['License No.', $doctor->license_number ?? '—'],
                ['Experience', ($doctor->experience_years ?? '—').' years'],
                ['Consultation Fee', '₨ '.number_format($doctor->consultation_fee ?? 0, 0)],
                ['Status', $doctor->is_active ? 'Active' : 'Inactive'],
            ] as [$l,$v])
            <div class="px-5 py-2.5 flex justify-between">
                <span class="text-sm text-slate-400">{{ $l }}</span>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $v }}</span>
            </div>
            @endforeach
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
            <div class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Contact & HR</div>
            @foreach([
                ['Phone', $doctor->user->phone ?? '—'],
                ['Employee ID', $doctor->user->employee_id ?? '—'],
                ['Joining Date', $doctor->user->joining_date?->format('d M Y') ?? '—'],
                ['OPD Visits', $doctor->opdVisits?->count() ?? 0],
            ] as [$l,$v])
            <div class="px-5 py-2.5 flex justify-between">
                <span class="text-sm text-slate-400">{{ $l }}</span>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $v }}</span>
            </div>
            @endforeach
        </div>
    </div>

    @if($doctor->bio)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Bio</p>
        <p class="text-sm text-slate-600 dark:text-slate-300">{{ $doctor->bio }}</p>
    </div>
    @endif

</div>
@endsection
