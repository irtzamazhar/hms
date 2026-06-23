<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view suppliers');
        $suppliers = Supplier::withCount('purchases')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('company', 'like', "%$s%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        $this->authorize('manage suppliers');

        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage suppliers');
        $request->validate([
            'name' => 'required|string|max:100',
            'company' => 'nullable|string|max:150',
            'email' => 'nullable|email|unique:suppliers,email',
            'phone' => 'required|string|max:20',
            'contact_person' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'opening_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        Supplier::create($request->all());

        return redirect()->route('suppliers.index')->with('success', 'Supplier added.');
    }

    public function show(Supplier $supplier): View
    {
        $this->authorize('view suppliers');
        $supplier->load(['purchases' => fn ($q) => $q->with('items.medicine')->latest()->limit(10)]);

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorize('manage suppliers');

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('manage suppliers');
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => "nullable|email|unique:suppliers,email,{$supplier->id}",
            'status' => 'required|in:active,inactive',
        ]);

        $supplier->update($request->all());

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('manage suppliers');
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
    }
}
