<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view expenses');
        $expenses = Expense::with(['category:id,name', 'createdBy:id,name'])
            ->when($request->date, fn ($q, $d) => $q->whereDate('expense_date', $d))
            ->when($request->module, fn ($q, $m) => $q->where('module', $m))
            ->when($request->category_id, fn ($q, $id) => $q->where('expense_category_id', $id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest('expense_date')->paginate(20)->withQueryString();

        $categories = ExpenseCategory::active()->get();
        $summary = [
            'today' => Expense::whereDate('expense_date', today())->approved()->sum('amount'),
            'month' => Expense::whereMonth('expense_date', now()->month)->approved()->sum('amount'),
            'pending' => Expense::where('status', 'pending')->count(),
        ];

        return view('expenses.index', compact('expenses', 'categories', 'summary'));
    }

    public function create(): View
    {
        $this->authorize('create expenses');

        return view('expenses.create', ['categories' => ExpenseCategory::active()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create expenses');
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|string',
            'module' => 'required|in:hospital,pharmacy,laboratory,general',
        ]);

        // Only persist explicitly allowed fields. Workflow columns (status,
        // approved_by) are forced server-side so an expense can never be
        // self-approved by injecting them in the request body.
        Expense::create(array_merge(
            $request->only([
                'expense_category_id', 'title', 'amount', 'expense_date', 'shift',
                'reference_number', 'payment_method', 'module', 'description',
            ]),
            [
                'created_by' => auth()->id(),
                'status' => 'pending',
                'approved_by' => null,
            ]
        ));

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function show(Expense $expense): View
    {
        $this->authorize('view expenses');
        $expense->load(['category', 'createdBy', 'approvedBy']);

        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense): View
    {
        $this->authorize('edit expenses');

        return view('expenses.edit', ['expense' => $expense, 'categories' => ExpenseCategory::active()->get()]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorize('edit expenses');
        $expense->update($request->only(['expense_category_id', 'title', 'amount', 'expense_date', 'payment_method', 'module', 'description']));

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete expenses');
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    public function approve(Expense $expense): RedirectResponse
    {
        $this->authorize('approve expenses');
        $expense->update(['status' => 'approved', 'approved_by' => auth()->id()]);

        return back()->with('success', 'Expense approved.');
    }
}
