<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\HospitalSetting;
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
        $patients = Patient::select('id', 'name', 'mr_number', 'phone')->latest()->get();
        $doctors = Doctor::active()->with('user:id,name')->get();

        return view('pharmacy.pos', compact('medicines', 'patients', 'doctors'));
    }

    public function storeSale(Request $request): RedirectResponse
    {
        $this->authorize('create sales');
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $sale = $this->service->processSale($request->all());

        return redirect()->route('pharmacy.sale.show', $sale)->with('success', "Sale {$sale->invoice_number} created.");
    }

    public function sales(Request $request): View
    {
        $this->authorize('view sales');
        $query = Sale::with(['patient:id,name'])
            ->withCount('items')
            ->when($request->from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($request->shift, fn ($q, $s) => $q->where('shift', $s))
            ->latest();

        $totalRevenue = (clone $query)->sum('total_amount');
        $totalDiscount = (clone $query)->sum('discount_amount');
        $sales = $query->paginate(20)->withQueryString();

        return view('pharmacy.sales', compact('sales', 'totalRevenue', 'totalDiscount'));
    }

    public function searchMedicines(Request $request): JsonResponse
    {
        $this->authorize('view pharmacy');
        $medicines = Medicine::active()
            ->where(fn ($q) => $q->where('name', 'like', '%'.$request->q.'%')
                ->orWhere('generic_name', 'like', '%'.$request->q.'%')
                ->orWhere('barcode', $request->q))
            ->select('id', 'name', 'generic_name', 'sale_price', 'stock_quantity')
            ->limit(12)
            ->get();

        return response()->json($medicines);
    }

    public function saleShow(Sale $sale): View
    {
        $this->authorize('view sales');
        $sale->load(['items.medicine', 'patient', 'doctor.user', 'createdBy']);

        return view('pharmacy.sale-show', compact('sale'));
    }

    public function salePrint(Sale $sale)
    {
        $this->authorize('view sales');
        $sale->load(['items.medicine', 'patient', 'doctor.user']);
        $setting = HospitalSetting::current();
        $pdf = app('dompdf.wrapper')->loadView('pharmacy.invoice-pdf', compact('sale', 'setting'));

        return $pdf->stream("Invoice-{$sale->invoice_number}.pdf");
    }
}
