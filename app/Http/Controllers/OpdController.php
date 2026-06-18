<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpdVisitRequest;
use App\Models\Department;
use App\Models\Doctor;
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
        $visits  = $this->service->list($request->only(['date', 'shift', 'doctor_id', 'status', 'search']));
        $doctors = Doctor::active()->with('user:id,name')->get();

        return view('opd.index', compact('visits', 'doctors'));
    }

    public function create(): View
    {
        $this->authorize('create opd');
        $patients    = Patient::select('id', 'name', 'mr_number')->latest()->get();
        $doctors     = Doctor::active()->with('user:id,name')->get();
        $departments = Department::active()->get();

        return view('opd.create', compact('patients', 'doctors', 'departments'));
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
        $opd->load(['patient', 'doctor.user', 'department', 'prescriptions.items', 'labBookings.items.test', 'createdBy']);

        return view('opd.show', compact('opd'));
    }

    public function edit(OpdVisit $opd): View
    {
        $this->authorize('edit opd');
        $doctors     = Doctor::active()->with('user:id,name')->get();
        $departments = Department::active()->get();

        return view('opd.edit', compact('opd', 'doctors', 'departments'));
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
        $opdVisit->load(['patient', 'doctor.user']);

        return view('opd.invoice', compact('opdVisit'));
    }

    public function print(OpdVisit $opdVisit)
    {
        $opdVisit->load(['patient', 'doctor.user', 'prescriptions.items']);
        $pdf = app('dompdf.wrapper')->loadView('opd.print', compact('opdVisit'));

        return $pdf->stream("OPD-{$opdVisit->visit_number}.pdf");
    }
}
