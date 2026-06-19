<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftClosing;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage settings');
        $shifts = Shift::withCount('assignments')->get();

        return view('shifts.index', compact('shifts'));
    }

    public function create(): View
    {
        $this->authorize('manage settings');

        return view('shifts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage settings');
        $request->validate([
            'name'       => 'required|string|max:100|unique:shifts,name',
            'type'       => 'required|in:morning,evening,night,custom',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i',
            'status'     => 'required|in:active,inactive',
        ]);

        Shift::create($request->only(['name', 'type', 'start_time', 'end_time', 'status']));

        return redirect()->route('shifts.index')->with('success', 'Shift created.');
    }

    public function edit(Shift $shift): View
    {
        $this->authorize('manage settings');

        return view('shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorize('manage settings');
        $request->validate([
            'name'       => "required|string|max:100|unique:shifts,name,{$shift->id}",
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i',
            'status'     => 'required|in:active,inactive',
        ]);

        $shift->update($request->only(['name', 'start_time', 'end_time', 'status']));

        return redirect()->route('shifts.index')->with('success', 'Shift updated.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $this->authorize('manage settings');
        $shift->delete();

        return redirect()->route('shifts.index')->with('success', 'Shift deleted.');
    }

    public function assignments(Request $request): View
    {
        $this->authorize('manage settings');
        $assignments = ShiftAssignment::with(['user:id,name,employee_id', 'shift:id,name,type'])
            ->when($request->date, fn ($q, $d) => $q->where('assignment_date', $d))
            ->when($request->shift_id, fn ($q, $id) => $q->where('shift_id', $id))
            ->latest('assignment_date')
            ->paginate(25)
            ->withQueryString();

        $shifts = Shift::active()->get();
        $users  = User::whereIn('user_type', ['doctor', 'staff'])->where('status', 'active')->get();

        return view('shifts.assignments', compact('assignments', 'shifts', 'users'));
    }

    public function assign(Request $request): RedirectResponse
    {
        $this->authorize('manage settings');
        $request->validate([
            'user_id'         => 'required|exists:users,id',
            'shift_id'        => 'required|exists:shifts,id',
            'assignment_date' => 'required|date',
        ]);

        ShiftAssignment::updateOrCreate(
            ['user_id' => $request->user_id, 'assignment_date' => $request->assignment_date],
            ['shift_id' => $request->shift_id, 'status' => 'assigned', 'assigned_by' => auth()->id(), 'notes' => $request->notes]
        );

        return back()->with('success', 'Shift assigned.');
    }

    public function closeForm(): View
    {
        $this->authorize('manage settings');
        $shifts  = Shift::active()->get();
        $closings = ShiftClosing::with('shift')->latest()->limit(10)->get();

        return view('shifts.close', compact('shifts', 'closings'));
    }

    public function close(Request $request): RedirectResponse
    {
        $this->authorize('manage settings');
        $request->validate([
            'shift_id'          => 'required|exists:shifts,id',
            'closing_date'      => 'required|date',
            'opd_revenue'       => 'nullable|numeric|min:0',
            'ipd_revenue'       => 'nullable|numeric|min:0',
            'pharmacy_revenue'  => 'nullable|numeric|min:0',
            'lab_revenue'       => 'nullable|numeric|min:0',
            'total_expenses'    => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string',
        ]);

        $already = ShiftClosing::where('shift_id', $request->shift_id)
            ->where('closing_date', $request->closing_date)
            ->exists();

        if ($already) {
            return back()->withErrors(['error' => 'This shift has already been closed for the selected date.']);
        }

        $totalRevenue = ($request->opd_revenue ?? 0) + ($request->ipd_revenue ?? 0)
            + ($request->pharmacy_revenue ?? 0) + ($request->lab_revenue ?? 0)
            + ($request->other_revenue ?? 0);

        ShiftClosing::create([
            'shift_id'          => $request->shift_id,
            'closing_date'      => $request->closing_date,
            'opd_revenue'       => $request->opd_revenue ?? 0,
            'ipd_revenue'       => $request->ipd_revenue ?? 0,
            'pharmacy_revenue'  => $request->pharmacy_revenue ?? 0,
            'lab_revenue'       => $request->lab_revenue ?? 0,
            'other_revenue'     => $request->other_revenue ?? 0,
            'total_revenue'     => $totalRevenue,
            'total_expenses'    => $request->total_expenses ?? 0,
            'notes'             => $request->notes,
            'closed_by'         => auth()->id(),
            'closed_at'         => now(),
        ]);

        return redirect()->route('shifts.close.form')->with('success', 'Shift closed successfully.');
    }
}
