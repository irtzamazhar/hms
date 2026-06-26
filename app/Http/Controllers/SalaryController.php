<?php

namespace App\Http\Controllers;

use App\Exports\SalaryExport;
use App\Models\HospitalSetting;
use App\Models\SalaryPayment;
use App\Models\SalaryStructure;
use App\Models\User;
use App\Notifications\SalaryGenerated;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SalaryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manage salaries');
        $payments = SalaryPayment::with(['user:id,name,employee_id', 'generatedBy:id,name'])
            ->when($request->month, fn ($q, $m) => $q->where('month', $m))
            ->when($request->year, fn ($q, $y) => $q->where('year', $y))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%$s%")))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'total' => SalaryPayment::where('month', now()->month)->where('year', now()->year)->sum('net_salary'),
            'paid' => SalaryPayment::where('month', now()->month)->where('year', now()->year)->where('status', 'paid')->sum('net_salary'),
            'pending' => SalaryPayment::where('status', 'pending')->count(),
        ];

        return view('salaries.index', compact('payments', 'summary'));
    }

    public function structure(Request $request): View
    {
        $this->authorize('manage salaries');
        $users = User::with('salaryStructure')
            ->whereIn('user_type', ['doctor', 'staff', 'admin'])
            ->where('status', 'active')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->get();

        return view('salaries.structure', compact('users'));
    }

    public function saveStructure(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage salaries');
        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'house_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'other_allowances' => 'nullable|numeric|min:0',
            'income_tax_deduction' => 'nullable|numeric|min:0',
            'provident_fund_deduction' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        // Mark previous structure as non-current
        SalaryStructure::where('user_id', $user->id)->where('is_current', true)->update(['is_current' => false, 'effective_to' => now()->subDay()]);

        SalaryStructure::create(array_merge($request->only([
            'basic_salary', 'house_allowance', 'transport_allowance',
            'medical_allowance', 'other_allowances', 'income_tax_deduction',
            'provident_fund_deduction', 'other_deductions', 'effective_from',
        ]), [
            'user_id' => $user->id,
            'is_current' => true,
        ]));

        return redirect()->route('salaries.structure')->with('success', "Salary structure saved for {$user->name}.");
    }

    public function generateForm(): View
    {
        $this->authorize('manage salaries');
        $users = User::with('salaryStructure')
            ->whereIn('user_type', ['doctor', 'staff', 'admin'])
            ->where('status', 'active')
            ->get();

        return view('salaries.generate', compact('users'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $this->authorize('manage salaries');
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'user_ids' => 'required|array|min:1',
        ]);

        $generated = 0;
        foreach ($request->user_ids as $userId) {
            $user = User::find($userId);
            $structure = $user?->salaryStructure;

            if (! $structure) {
                continue;
            }

            $exists = SalaryPayment::where('user_id', $userId)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->exists();

            if ($exists) {
                continue;
            }

            $payment = SalaryPayment::create([
                'user_id' => $userId,
                'salary_structure_id' => $structure->id,
                'month' => $request->month,
                'year' => $request->year,
                'basic_salary' => $structure->basic_salary,
                'total_allowances' => $structure->total_allowances,
                'total_deductions' => $structure->total_deductions,
                'bonus' => 0,
                'overtime' => 0,
                'net_salary' => $structure->net_salary,
                'status' => 'pending',
                'generated_by' => auth()->id(),
            ]);

            $user->notify(new SalaryGenerated($payment));
            $generated++;
        }

        return redirect()->route('salaries.index')->with('success', "{$generated} salary slip(s) generated.");
    }

    public function pay(Request $request, SalaryPayment $salaryPayment): RedirectResponse
    {
        $this->authorize('manage salaries');
        $request->validate([
            'payment_method' => 'required|string',
            'transaction_reference' => 'nullable|string|max:100',
            'bonus' => 'nullable|numeric|min:0',
            'overtime' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $bonus = $request->bonus ?? 0;
        $overtime = $request->overtime ?? 0;
        $net = $salaryPayment->basic_salary + $salaryPayment->total_allowances + $bonus + $overtime - $salaryPayment->total_deductions;

        $salaryPayment->update([
            'bonus' => $bonus,
            'overtime' => $overtime,
            'net_salary' => $net,
            'payment_date' => now()->toDateString(),
            'payment_method' => $request->payment_method,
            'transaction_reference' => $request->transaction_reference,
            'remarks' => $request->remarks,
            'status' => 'paid',
            'paid_by' => auth()->id(),
        ]);

        return back()->with('success', 'Salary marked as paid.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('manage salaries');
        $month = $request->month ? (int) $request->month : null;
        $year = $request->year ? (int) $request->year : null;

        $filename = 'Salary-Report';
        if ($month && $year) {
            $filename .= '-'.date('F', mktime(0, 0, 0, $month, 1))."-{$year}";
        }

        return Excel::download(
            new SalaryExport($month, $year, $request->status),
            $filename.'.xlsx'
        );
    }

    public function slip(SalaryPayment $salaryPayment): Response
    {
        $this->authorize('manage salaries');
        $salaryPayment->load(['user', 'salaryStructure', 'paidBy']);
        $setting = HospitalSetting::current();

        $pdf = Pdf::loadView('salaries.slip', compact('salaryPayment', 'setting'))->setPaper('a4');

        return $pdf->stream("salary-slip-{$salaryPayment->id}.pdf");
    }
}
