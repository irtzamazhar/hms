<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpdVisitRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\HospitalSetting;
use App\Models\OpdVisit;
use App\Models\Patient;
use App\Services\OpdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpdController extends Controller
{
    public function __construct(private readonly OpdService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('view opd');
        $visits = $this->service->list($request->only(['date', 'shift', 'doctor_id', 'status', 'search']));
        $doctors = Doctor::active()->with('user:id,name')->get();

        return view('opd.index', compact('visits', 'doctors'));
    }

    public function create(): View
    {
        $this->authorize('create opd');
        $patients = Patient::select('id', 'name', 'mr_number')->latest()->get();
        $doctors = Doctor::active()->with('user:id,name')->get();
        $departments = Department::active()->get();
        $currentShift = $this->service->currentShift();

        return view('opd.create', compact('patients', 'doctors', 'departments', 'currentShift'));
    }

    public function store(OpdVisitRequest $request): RedirectResponse
    {
        $this->authorize('create opd');
        $visit = $this->service->create($request->validated());

        return redirect()->route('opd.show', $visit)->with('success', "OPD visit {$visit->visit_number} created.");
    }

    public function show(OpdVisit $opd): View
    {
        $this->authorize('view opd');
        $visit = $opd;
        $visit->load(['patient', 'doctor.user', 'prescriptions.items', 'createdBy']);

        return view('opd.show', compact('visit'));
    }

    public function edit(OpdVisit $opd): View
    {
        $this->authorize('edit opd');
        $visit = $opd;
        $doctors = Doctor::active()->with('user:id,name')->get();

        return view('opd.edit', compact('visit', 'doctors'));
    }

    public function update(OpdVisitRequest $request, OpdVisit $opd): RedirectResponse
    {
        $this->authorize('edit opd');
        $this->service->update($opd, $request->validated());

        return redirect()->route('opd.show', $opd)->with('success', 'OPD visit updated.');
    }

    public function destroy(OpdVisit $opd): RedirectResponse
    {
        $this->authorize('delete opd');
        $opd->delete();

        return redirect()->route('opd.index')->with('success', 'OPD visit deleted.');
    }

    public function invoice(OpdVisit $opdVisit): View
    {
        $this->authorize('view opd');
        $visit = $opdVisit;
        $visit->load(['patient', 'doctor.user', 'prescriptions.items']);
        $setting = HospitalSetting::current();

        return view('opd.invoice', compact('visit', 'setting'));
    }

    public function print(OpdVisit $opdVisit)
    {
        $this->authorize('view opd');
        $visit = $opdVisit;
        $visit->load(['patient', 'doctor.user', 'prescriptions.items']);
        $setting = HospitalSetting::current();
        $pdf = app('dompdf.wrapper')->loadView('opd.print', compact('visit', 'setting'));

        return $pdf->stream("OPD-{$visit->visit_number}.pdf");
    }
}
