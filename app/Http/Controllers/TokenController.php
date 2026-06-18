<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Token;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TokenController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view tokens');
        $shift  = $request->get('shift', $this->currentShift());
        $date   = $request->get('date', today()->toDateString());
        $tokens = Token::with(['patient:id,name,mr_number', 'doctor.user:id,name', 'department:id,name'])
            ->whereDate('token_date', $date)
            ->when($shift !== 'all', fn ($q) => $q->where('shift', $shift))
            ->orderBy('token_number')
            ->get();

        return view('tokens.index', compact('tokens', 'shift', 'date'));
    }

    public function create(): View
    {
        $this->authorize('create tokens');
        $patients    = Patient::select('id', 'name', 'mr_number')->latest()->get();
        $doctors     = Doctor::active()->with('user:id,name')->get();
        $departments = Department::active()->get();
        $shift       = $this->currentShift();
        $nextToken   = Token::nextTokenNumber($shift);

        return view('tokens.create', compact('patients', 'doctors', 'departments', 'shift', 'nextToken'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create tokens');
        $data = $request->validate([
            'patient_id'    => 'required|exists:patients,id',
            'doctor_id'     => 'nullable|exists:doctors,id',
            'department_id' => 'nullable|exists:departments,id',
            'shift'         => 'required|in:morning,evening,night',
            'priority'      => 'nullable|in:normal,urgent,emergency',
            'notes'         => 'nullable|string',
        ]);

        $data['token_date']   = today();
        $data['token_number'] = Token::nextTokenNumber($data['shift']);
        $data['created_by']   = auth()->id();

        $token = Token::create($data);

        return redirect()->route('tokens.index')->with('success', "Token #{$token->token_number} generated.");
    }

    public function show(Token $token): View
    {
        $token->load(['patient', 'doctor.user', 'department']);

        return view('tokens.show', compact('token'));
    }

    public function destroy(Token $token): RedirectResponse
    {
        $this->authorize('manage tokens');
        $token->delete();

        return back()->with('success', 'Token cancelled.');
    }

    public function updateStatus(Request $request, Token $token): RedirectResponse
    {
        $this->authorize('manage tokens');
        $request->validate(['status' => 'required|in:waiting,in_progress,completed,cancelled,no_show']);
        $token->update(['status' => $request->status]);

        return back()->with('success', 'Token status updated.');
    }

    public function print(Token $token)
    {
        $token->load(['patient', 'doctor.user', 'department']);

        return view('tokens.print', compact('token'));
    }

    private function currentShift(): string
    {
        $hour = now()->hour;
        if ($hour >= 8 && $hour < 14)  return 'morning';
        if ($hour >= 14 && $hour < 20) return 'evening';
        return 'night';
    }
}
