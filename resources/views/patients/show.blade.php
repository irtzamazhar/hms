@extends('layouts.hms')
@section('title',$patient->name)
@section('breadcrumb')
    <a href="{{ route('patients.index') }}" class="text-slate-400 hover:text-slate-600">Patients</a> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $patient->name }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    {{-- Header --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-xl font-bold text-primary-600">
                {{ substr($patient->name,0,1) }}
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-800 dark:text-white">{{ $patient->name }}</h1>
                <p class="text-sm text-slate-400">{{ $patient->mr_number }} · {{ $patient->age }} yrs · {{ ucfirst($patient->gender) }}</p>
                <p class="text-sm text-slate-400">{{ $patient->phone }}</p>
            </div>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('tokens.create', ['patient_id'=>$patient->id]) }}" class="px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm rounded-lg">+ Token</a>
            <a href="{{ route('opd.create', ['patient_id'=>$patient->id]) }}" class="px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg">+ OPD Visit</a>
            @can('edit patients')
            <a href="{{ route('patients.edit', $patient) }}" class="px-3 py-2 bg-slate-200 text-slate-700 dark:text-slate-200">Edit</a>
            @endcan
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        {{-- Personal Info --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
            <div class="px-5 py-3 font-semibold text-sm text-slate-600 dark:text-slate-400 uppercase tracking-wide">Personal</div>
            @foreach([
                ['Date of Birth', $patient->date_of_birth?->format('d M Y') ?? '—'],
                ['Blood Group', $patient->blood_group ?? '—'],
                ['CNIC', $patient->cnic ?? '—'],
                ['Email', $patient->email ?? '—'],
                ['Address', $patient->address ?? '—'],
            ] as [$l,$v])
            <div class="px-5 py-2.5 flex justify-between">
                <span class="text-sm text-slate-400">{{ $l }}</span>
                <span class="text-sm text-slate-700 dark:text-slate-200 font-medium text-right max-w-[200px]">{{ $v }}</span>
            </div>
            @endforeach
        </div>

        {{-- Medical Info + Emergency Contact --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
                <div class="px-5 py-3 font-semibold text-sm text-slate-600 dark:text-slate-400 uppercase tracking-wide">Medical</div>
                @foreach([
                    ['Allergies', $patient->allergies ?? '—'],
                    ['Chronic Diseases', $patient->chronic_diseases ?? '—'],
                    ['Notes', $patient->medical_notes ?? '—'],
                ] as [$l,$v])
                <div class="px-5 py-2.5">
                    <p class="text-xs text-slate-400">{{ $l }}</p>
                    <p class="text-sm text-slate-700 dark:text-slate-200 mt-0.5">{{ $v }}</p>
                </div>
                @endforeach
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
                <div class="px-5 py-3 font-semibold text-sm text-slate-600 dark:text-slate-400 uppercase tracking-wide">Emergency Contact</div>
                @foreach([
                    ['Name', $patient->emergency_contact_name ?? '—'],
                    ['Phone', $patient->emergency_contact_phone ?? '—'],
                    ['Relation', $patient->emergency_contact_relation ?? '—'],
                ] as [$l,$v])
                <div class="px-5 py-2.5 flex justify-between">
                    <span class="text-sm text-slate-400">{{ $l }}</span>
                    <span class="text-sm text-slate-700 dark:text-slate-200 font-medium">{{ $v }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Visit History --}}
    @if($patient->opdVisits->count())
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700">
            <h3 class="font-semibold text-sm text-slate-700 dark:text-white">OPD Visit History</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs text-slate-500">Visit #</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-500">Doctor</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-500">Date</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-500">Diagnosis</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-500">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($patient->opdVisits as $v)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                    <td class="px-4 py-2 font-mono text-xs text-primary-600">{{ $v->visit_number }}</td>
                    <td class="px-4 py-2 text-slate-600 dark:text-slate-300">Dr. {{ $v->doctor->user->name }}</td>
                    <td class="px-4 py-2 text-xs text-slate-400">{{ $v->visit_date->format('d M Y') }}</td>
                    <td class="px-4 py-2 text-slate-600 dark:text-slate-300 max-w-[180px] truncate">{{ $v->diagnosis ?? '—' }}</td>
                    <td class="px-4 py-2"><x-badge color="{{ ['completed'=>'green','waiting'=>'amber','cancelled'=>'red','in_progress'=>'blue'][$v->status] ?? 'slate' }}">{{ ucfirst(str_replace('_',' ',$v->status)) }}</x-badge></td>
                    <td class="px-4 py-2"><a href="{{ route('opd.show',$v) }}" class="text-xs text-primary-600 hover:underline">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
@endsection
