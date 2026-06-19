<?php

namespace App\Http\Controllers;

use App\Exports\PurchasesExport;
use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\Purchase;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manage pharmacy');
        $purchases = Purchase::with(['supplier:id,name,company', 'createdBy:id,name'])
            ->when($request->supplier_id, fn ($q, $id) => $q->where('supplier_id', $id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->date_from, fn ($q, $d) => $q->where('purchase_date', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->where('purchase_date', '<=', $d))
            ->latest('purchase_date')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::active()->get();
        $summary = [
            'month_total' => Purchase::whereMonth('purchase_date', now()->month)->sum('total_amount'),
            'pending'     => Purchase::where('payment_status', 'pending')->count(),
        ];

        return view('purchases.index', compact('purchases', 'suppliers', 'summary'));
    }

    public function create(): View
    {
        $this->authorize('manage pharmacy');
        $suppliers = Supplier::active()->get();
        $medicines = Medicine::select('id', 'name', 'generic_name', 'unit')->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'medicines'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage pharmacy');
        $request->validate([
            'supplier_id'         => 'required|exists:suppliers,id',
            'purchase_date'       => 'required|date',
            'invoice_number'      => 'nullable|string|max:100',
            'payment_method'      => 'required|string',
            'items'               => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.sale_price'  => 'required|numeric|min:0',
            'items.*.batch_number'=> 'nullable|string',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request) {
            $subtotal = collect($request->items)->sum(fn ($i) => $i['quantity'] * $i['unit_price']);
            $discount = $request->discount ?? 0;
            $tax      = $request->tax ?? 0;
            $total    = $subtotal - $discount + $tax;
            $paid     = $request->paid_amount ?? 0;

            $purchase = Purchase::create([
                'purchase_number' => Purchase::generateNumber(),
                'supplier_id'     => $request->supplier_id,
                'purchase_date'   => $request->purchase_date,
                'invoice_number'  => $request->invoice_number,
                'subtotal'        => $subtotal,
                'discount'        => $discount,
                'tax'             => $tax,
                'total_amount'    => $total,
                'paid_amount'     => $paid,
                'due_amount'      => max(0, $total - $paid),
                'payment_method'  => $request->payment_method,
                'payment_status'  => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'pending'),
                'status'          => 'received',
                'notes'           => $request->notes,
                'created_by'      => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $purchase->items()->create(array_merge($item, ['total_price' => $lineTotal]));

                // Update medicine stock
                $medicine = Medicine::find($item['medicine_id']);
                $medicine->increment('current_stock', $item['quantity']);
                $medicine->update(['purchase_price' => $item['unit_price'], 'selling_price' => $item['sale_price']]);

                MedicineStock::create([
                    'medicine_id'      => $item['medicine_id'],
                    'type'             => 'in',
                    'quantity'         => $item['quantity'],
                    'reference_type'   => 'purchase',
                    'reference_id'     => $purchase->id,
                    'reference_number' => $purchase->purchase_number,
                    'notes'            => "Purchase from {$purchase->supplier->name}",
                    'created_by'       => auth()->id(),
                ]);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase recorded and stock updated.');
    }

    public function show(Purchase $purchase): View
    {
        $this->authorize('manage pharmacy');
        $purchase->load(['supplier', 'items.medicine', 'createdBy']);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase): View
    {
        $this->authorize('manage pharmacy');
        $purchase->load('items.medicine');
        $suppliers = Supplier::active()->get();
        $medicines = Medicine::select('id', 'name', 'generic_name', 'unit')->orderBy('name')->get();

        return view('purchases.edit', compact('purchase', 'suppliers', 'medicines'));
    }

    public function update(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('manage pharmacy');
        $request->validate([
            'payment_status' => 'required|in:pending,partial,paid',
            'paid_amount'    => 'required|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        $purchase->update([
            'paid_amount'    => $request->paid_amount,
            'due_amount'     => max(0, $purchase->total_amount - $request->paid_amount),
            'payment_status' => $request->payment_status,
            'notes'          => $request->notes,
        ]);

        return redirect()->route('purchases.show', $purchase)->with('success', 'Payment updated.');
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        $this->authorize('manage pharmacy');
        $purchase->delete();

        return redirect()->route('purchases.index')->with('success', 'Purchase deleted.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('manage pharmacy');

        return Excel::download(
            new PurchasesExport($request->date_from, $request->date_to, $request->supplier_id),
            'Purchases-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function print(Purchase $purchase): Response
    {
        $purchase->load(['supplier', 'items.medicine', 'createdBy']);
        $setting = \App\Models\HospitalSetting::current();

        $pdf = Pdf::loadView('purchases.print', compact('purchase', 'setting'))->setPaper('a4');

        return $pdf->stream("PO-{$purchase->purchase_number}.pdf");
    }
}
