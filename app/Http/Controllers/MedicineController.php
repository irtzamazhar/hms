<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\Supplier;
use App\Notifications\LowStockAlert;
use App\Services\PharmacyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicineController extends Controller
{
    public function __construct(private readonly PharmacyService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('view pharmacy');
        $medicines = Medicine::with('category:id,name')
            ->when($request->search, fn ($q, $s) => $q->search($s))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->low_stock, fn ($q) => $q->lowStock())
            ->latest()->paginate(25)->withQueryString();

        $categories = MedicineCategory::active()->get();

        return view('medicines.index', compact('medicines', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('manage medicines');

        return view('medicines.create', [
            'categories' => MedicineCategory::active()->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage medicines');
        $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string',
            'brand' => 'nullable|string',
            'unit' => 'required|string',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:medicine_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        Medicine::create($request->all());

        return redirect()->route('medicines.index')->with('success', 'Medicine added.');
    }

    public function show(Medicine $medicine): View
    {
        $this->authorize('view pharmacy');
        $medicine->load(['category', 'batches', 'stockMovements' => fn ($q) => $q->latest()->take(20)]);

        return view('medicines.show', compact('medicine'));
    }

    public function edit(Medicine $medicine): View
    {
        $this->authorize('manage medicines');

        return view('medicines.edit', [
            'medicine' => $medicine,
            'categories' => MedicineCategory::active()->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $this->authorize('manage medicines');
        $request->validate([
            'name' => 'required|string|max:255',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $medicine->update($request->all());

        return redirect()->route('medicines.show', $medicine)->with('success', 'Medicine updated.');
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        $this->authorize('manage medicines');
        $medicine->delete();

        return redirect()->route('medicines.index')->with('success', 'Medicine removed.');
    }

    public function stockAdjustment(Request $request, Medicine $medicine): RedirectResponse
    {
        $this->authorize('manage medicines');
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:in,out',
            'notes' => 'nullable|string',
        ]);

        $this->service->adjustStock($medicine, $request->quantity, $request->type, $request->notes ?? '');

        $medicine->refresh();
        if ($medicine->isLowStock()) {
            auth()->user()->notify(new LowStockAlert($medicine));
        }

        return back()->with('success', 'Stock adjusted.');
    }
}
