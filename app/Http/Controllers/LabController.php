<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\LabBooking;
use App\Models\LabTest;
use App\Models\LabTestCategory;
use App\Models\Patient;
use App\Notifications\LabResultReady;
use App\Services\LabService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabController extends Controller
{
    public function __construct(private readonly LabService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('view laboratory');
        $bookings = $this->service->list($request->only(['date', 'status', 'search']));

        return view('lab.index', compact('bookings'));
    }

    public function create(): View
    {
        $this->authorize('create lab bookings');
        $patients   = Patient::select('id', 'name', 'mr_number')->latest()->get();
        $doctors    = Doctor::active()->with('user:id,name')->get();
        $categories = LabTestCategory::active()->with(['tests' => fn ($q) => $q->active()->orderBy('name')])->get();

        return view('lab.create', compact('patients', 'doctors', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create lab bookings');
        $request->validate([
            'patient_id'         => 'required|exists:patients,id',
            'tests'              => 'required|array|min:1',
            'tests.*.test_id'    => 'required|exists:lab_tests,id',
            'tests.*.cost'       => 'required|numeric|min:0',
            'paid_amount'        => 'nullable|numeric|min:0',
            'payment_method'     => 'nullable|string',
        ]);

        $booking = $this->service->createBooking($request->all());

        return redirect()->route('lab.show', $booking)->with('success', "Lab booking {$booking->booking_number} created.");
    }

    public function show(LabBooking $labBooking): View
    {
        $this->authorize('view laboratory');
        $labBooking->load(['patient', 'doctor.user', 'items.test.category', 'reports.technician', 'createdBy']);

        return view('lab.show', compact('labBooking'));
    }

    public function saveResults(Request $request, LabBooking $labBooking): RedirectResponse
    {
        $this->authorize('enter lab results');
        $request->validate(['results' => 'required|array']);

        $this->service->saveResults($labBooking, $request->results);

        $labBooking->load('patient');
        auth()->user()->notify(new LabResultReady($labBooking));

        return back()->with('success', 'Results saved.');
    }

    public function reportPdf(LabBooking $labBooking)
    {
        $labBooking->load(['patient', 'doctor.user', 'reports.test', 'reports.technician', 'reports.verifiedBy']);
        $setting = \App\Models\HospitalSetting::current();
        $pdf     = app('dompdf.wrapper')->loadView('lab.report-pdf', compact('labBooking', 'setting'));

        return $pdf->stream("Lab-Report-{$labBooking->booking_number}.pdf");
    }
}
