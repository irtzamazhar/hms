<?php

namespace App\Http\Controllers;

use App\Exports\PatientsExport;
use App\Http\Requests\PatientRequest;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PatientController extends Controller
{
    public function __construct(private readonly PatientService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('view patients');
        $patients = $this->service->list($request->only(['search', 'gender', 'blood_group', 'status']));

        return view('patients.index', compact('patients'));
    }

    public function create(): View
    {
        $this->authorize('create patients');

        return view('patients.create');
    }

    public function store(PatientRequest $request): RedirectResponse
    {
        $this->authorize('create patients');
        $patient = $this->service->create($request->validated());

        return redirect()->route('patients.show', $patient)->with('success', "Patient {$patient->name} registered (MR: {$patient->mr_number}).");
    }

    public function show(Patient $patient): View
    {
        $this->authorize('view patients');
        $patient->load(['registeredBy', 'opdVisits.doctor.user', 'ipdAdmissions.ward']);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        $this->authorize('edit patients');

        return view('patients.edit', compact('patient'));
    }

    public function update(PatientRequest $request, Patient $patient): RedirectResponse
    {
        $this->authorize('edit patients');
        $this->service->update($patient, $request->validated());

        return redirect()->route('patients.show', $patient)->with('success', 'Patient updated successfully.');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $this->authorize('delete patients');
        $patient->delete();

        return redirect()->route('patients.index')->with('success', 'Patient removed.');
    }

    public function history(Patient $patient): View
    {
        $this->authorize('view patients');
        $history = $this->service->getHistory($patient);

        return view('patients.history', compact('patient', 'history'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('view patients');

        return Excel::download(
            new PatientsExport($request->search, $request->gender, $request->blood_group),
            'Patients-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
