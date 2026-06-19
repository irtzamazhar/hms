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
        $this->authorize('view lab');
        $tests = LabTest::with('category:id,name')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('code', 'like', "%$s%"))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = LabTestCategory::active()->get();

        return view('lab.tests.index', compact('tests', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('create lab');
        $categories = LabTestCategory::active()->get();

        return view('lab.tests.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create lab');
        $request->validate([
            'category_id'              => 'required|exists:lab_test_categories,id',
            'name'                     => 'required|string|max:150',
            'code'                     => 'required|string|max:30|unique:lab_tests,code',
            'cost'                     => 'required|numeric|min:0',
            'normal_range'             => 'nullable|string|max:100',
            'unit'                     => 'nullable|string|max:30',
            'sample_type'              => 'nullable|string|max:50',
            'turnaround_hours'         => 'nullable|integer|min:1',
            'preparation_instructions' => 'nullable|string',
            'description'              => 'nullable|string',
            'status'                   => 'required|in:active,inactive',
        ]);

        LabTest::create($request->all());

        return redirect()->route('lab.tests.index')->with('success', 'Lab test added.');
    }

    public function edit(LabTest $labTest): View
    {
        $this->authorize('create lab');
        $categories = LabTestCategory::active()->get();

        return view('lab.tests.edit', compact('labTest', 'categories'));
    }

    public function update(Request $request, LabTest $labTest): RedirectResponse
    {
        $this->authorize('create lab');
        $request->validate([
            'category_id'  => 'required|exists:lab_test_categories,id',
            'name'         => 'required|string|max:150',
            'cost'         => 'required|numeric|min:0',
            'normal_range' => 'nullable|string|max:100',
            'status'       => 'required|in:active,inactive',
        ]);

        $labTest->update($request->all());

        return redirect()->route('lab.tests.index')->with('success', 'Lab test updated.');
    }

    public function destroy(LabTest $labTest): RedirectResponse
    {
        $this->authorize('create lab');
        $labTest->delete();

        return redirect()->route('lab.tests.index')->with('success', 'Lab test deleted.');
    }
}
