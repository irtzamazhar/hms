<?php

namespace App\Http\Controllers;

use App\Models\LabTest;
use App\Models\LabTestCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabTestController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view laboratory');
        $tests = LabTest::with('category:id,name')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('code', 'like', "%$s%"))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = LabTestCategory::active()->get();

        return view('lab-tests.index', compact('tests', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('manage lab tests');
        $categories = LabTestCategory::active()->get();

        return view('lab-tests.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage lab tests');
        $validated = $request->validate([
            'category_id' => 'required|exists:lab_test_categories,id',
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:30|unique:lab_tests,code',
            'cost' => 'required|numeric|min:0',
            'normal_range' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:30',
            'sample_type' => 'nullable|string|max:50',
            'turnaround_hours' => 'nullable|integer|min:1',
            'preparation_instructions' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        LabTest::create($validated);

        return redirect()->route('lab.tests.index')->with('success', 'Lab test added.');
    }

    public function edit(LabTest $labTest): View
    {
        $this->authorize('manage lab tests');
        $categories = LabTestCategory::active()->get();

        return view('lab-tests.edit', compact('labTest', 'categories'));
    }

    public function update(Request $request, LabTest $labTest): RedirectResponse
    {
        $this->authorize('manage lab tests');
        $validated = $request->validate([
            'category_id' => 'required|exists:lab_test_categories,id',
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:30|unique:lab_tests,code,'.$labTest->id,
            'cost' => 'required|numeric|min:0',
            'normal_range' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:30',
            'sample_type' => 'nullable|string|max:50',
            'turnaround_hours' => 'nullable|integer|min:1',
            'preparation_instructions' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $labTest->update($validated);

        return redirect()->route('lab.tests.index')->with('success', 'Lab test updated.');
    }

    public function destroy(LabTest $labTest): RedirectResponse
    {
        $this->authorize('manage lab tests');

        if ($labTest->bookingItems()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete a test that has booking history. Set it to Inactive instead.']);
        }

        $labTest->delete();

        return redirect()->route('lab.tests.index')->with('success', 'Lab test deleted.');
    }
}
