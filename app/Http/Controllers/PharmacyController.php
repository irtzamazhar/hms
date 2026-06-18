<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Sale;
use App\Services\PharmacyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PharmacyController extends Controller
{
    public function __construct(private readonly PharmacyService $service) {}

    public function pos(): View
    {
        $this->authorize('view pharmacy');
        $medicines = Medicine::active()->with('category:id,name')->orderBy('name')->get();
        $patients  = Patient::select('id', 'name', 'mr_number', 'phone')->latest()->get();
        $doctors   = Doctor::active()->with('user:id,name')->get();

        return view('pharmacy.pos', compact('medicines', 'patients', 'doctors'));
    }

    public function storeSale(Request $request): RedirectResponse
    {
        $this->authorize('create sales');
        $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.medicine_id'  => 'required|exists:medicines,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'paid_amount'          => 'required|numeric|min:0',
        ]);

        $sale = $this->service->processSale($request->all());

        return redirect()->route('pharmacy.sale.show', $sale)->with('success', "Invoice {$sale->invoice_number} created.");
    }

    public function sales(Request $request): View
    {
        $this->authorize('view sales');
        $sales = Sale::with(['patient:id,name', 'createdBy:id,name'])
            ->when($request->date, fn ($q, $d) => $q->whereDate('sale_date', $d))
            ->when($request->shift, fn ($q, $s) => $q->where('shift', $s))
            ->when($request->search, fn ($q, $s) => $q->where('invoice_number', 'like', "%{$s}%")
                ->orWhere('customer_name', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pharmacy.sales', compact('sales'));
    }

    public function saleShow(Sale $sale): View
    {
        $this->authorize('view sales');
        $sale->load(['items.medicine', 'patient', 'doctor.user', 'createdBy']);

        return view('pharmacy.sale-show', compact('sale'));
    }

    public function salePrint(Sale $sale)
    {
        $sale->load(['items.medicine', 'patient', 'doctor.user']);
        $setting = \App\Models\HospitalSetting::current();
        $pdf     = app('dompdf.wrapper')->loadView('pharmacy.invoice-pdf', compact('sale', 'setting'));

        return $pdf->stream("Invoice-{$sale->invoice_number}.pdf");
    }
}
