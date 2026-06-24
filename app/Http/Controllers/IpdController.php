<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\HospitalSetting;
use App\Models\IpdAdmission;
use App\Models\Patient;
use App\Models\Ward;
use App\Notifications\IpdAdmissionCreated;
use App\Services\IpdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IpdController extends Controller
{
    public function __construct(private readonly IpdService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('view ipd');
        $admissions = $this->service->list($request->only(['status', 'ward_id', 'doctor_id', 'search']));
        $wards = Ward::active()->get();
        $doctors = Doctor::active()->with('user:id,name')->get();

        return view('ipd.index', compact('admissions', 'wards', 'doctors'));
    }

    public function create(): View
    {
        $this->authorize('create ipd');
        $patients = Patient::select('id', 'name', 'mr_number')->latest()->get();
        $doctors = Doctor::active()->with('user:id,name')->get();
        $departments = Department::active()->get();
        $wards = Ward::active()->get();
        $beds = Bed::available()->with('ward:id,name')->get();

        return view('ipd.create', compact('patients', 'doctors', 'departments', 'wards', 'beds'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create ipd');
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'ward_id' => 'required|exists:wards,id',
            'bed_id' => 'required|exists:beds,id',
            'admission_datetime' => 'required|date',
            'admission_diagnosis' => 'nullable|string',
            'admission_type' => 'required|in:emergency,elective,transfer',
            'daily_bed_charge' => 'nullable|numeric|min:0',
        ]);

        $admission = $this->service->admit($validated);
        $admission->load(['patient', 'doctor.user', 'ward', 'bed']);
        auth()->user()->notify(new IpdAdmissionCreated($admission));

        return redirect()->route('ipd.show', $admission)->with('success', "Patient admitted — {$admission->admission_number}.");
    }

    public function show(IpdAdmission $ipd): View
    {
        $this->authorize('view ipd');
        $admission = $ipd;
        $admission->load(['patient', 'doctor.user', 'ward', 'room', 'bed', 'treatments.doctor.user', 'admittedBy']);
        $charges = $this->service->calculateCharges($admission);

        return view('ipd.show', compact('admission', 'charges'));
    }

    public function edit(IpdAdmission $ipd): View
    {
        $this->authorize('edit ipd');
        $admission = $ipd;
        $doctors = Doctor::active()->with('user:id,name')->get();
        $departments = Department::active()->get();
        $wards = Ward::active()->get();

        return view('ipd.edit', compact('admission', 'doctors', 'departments', 'wards'));
    }

    public function update(Request $request, IpdAdmission $ipd): RedirectResponse
    {
        $this->authorize('edit ipd');
        $ipd->update($request->only([
            'doctor_id', 'department_id', 'admission_diagnosis',
            'doctor_charges', 'nursing_charges', 'medicine_charges',
            'lab_charges', 'other_charges', 'discount', 'paid_amount',
            'payment_status', 'notes',
        ]));

        return redirect()->route('ipd.show', $ipd)->with('success', 'Admission updated.');
    }

    public function destroy(IpdAdmission $ipd): RedirectResponse
    {
        $this->authorize('delete ipd');
        $ipd->delete();

        return redirect()->route('ipd.index')->with('success', 'Admission removed.');
    }

    public function discharge(Request $request, IpdAdmission $ipdAdmission): RedirectResponse
    {
        $this->authorize('discharge patients');
        $validated = $request->validate([
            'discharge_datetime' => 'required|date',
            'discharge_diagnosis' => 'nullable|string',
            'treatment_summary' => 'nullable|string',
        ]);

        $this->service->discharge($ipdAdmission, $validated);

        return redirect()->route('ipd.show', $ipdAdmission)->with('success', 'Patient discharged successfully.');
    }

    public function addTreatment(Request $request, IpdAdmission $ipdAdmission): RedirectResponse
    {
        $this->authorize('edit ipd');
        $validated = $request->validate(['treatment_notes' => 'required|string']);
        $this->service->addTreatment($ipdAdmission, $validated);

        return back()->with('success', 'Treatment note added.');
    }

    public function invoice(IpdAdmission $ipdAdmission): View
    {
        $this->authorize('view ipd');
        $admission = $ipdAdmission;
        $admission->load(['patient', 'doctor.user', 'ward', 'bed']);
        $charges = $this->service->calculateCharges($admission);
        $setting = HospitalSetting::current();

        return view('ipd.invoice', compact('admission', 'charges', 'setting'));
    }
}
