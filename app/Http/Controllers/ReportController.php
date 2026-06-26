<?php

namespace App\Http\Controllers;

use App\Exports\ExpensesReportExport;
use App\Exports\IpdReportExport;
use App\Exports\LabReportExport;
use App\Exports\OpdReportExport;
use App\Exports\PharmacyReportExport;
use App\Exports\ProfitLossExport;
use App\Models\DailyClosingReport;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\HospitalSetting;
use App\Models\IpdAdmission;
use App\Models\LabBooking;
use App\Models\Medicine;
use App\Models\MonthlyClosingReport;
use App\Models\OpdVisit;
use App\Models\SalaryPayment;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        $this->authorize('view reports');

        return view('reports.index');
    }

    public function opd(Request $request): View
    {
        $this->authorize('view reports');
        $from = $request->get('from', today()->toDateString());
        $to = $request->get('to', today()->toDateString());

        $visits = OpdVisit::with(['patient:id,name,mr_number', 'doctor.user:id,name'])
            ->whereBetween('visit_date', [$from, $to])
            ->when($request->shift, fn ($q, $s) => $q->where('shift', $s))
            ->when($request->doctor_id, fn ($q, $id) => $q->where('doctor_id', $id))
            ->latest()->get();

        $summary = [
            'total_visits' => $visits->count(),
            'total_revenue' => $visits->sum('net_amount'),
            'total_discount' => $visits->sum('discount'),
            'avg_fee' => round($visits->avg('consultation_fee') ?? 0, 0),
        ];

        $doctors = Doctor::active()->with('user:id,name')->get();

        return view('reports.opd', compact('visits', 'summary', 'from', 'to', 'doctors'));
    }

    public function ipd(Request $request): View
    {
        $this->authorize('view reports');
        $from = $request->get('from', today()->toDateString());
        $to = $request->get('to', today()->toDateString());
        $admissions = IpdAdmission::with(['patient:id,name,mr_number', 'doctor.user:id,name', 'ward:id,name'])
            ->whereBetween('admission_datetime', [$from.' 00:00:00', $to.' 23:59:59'])
            ->latest('admission_datetime')->get();

        $discharged = $admissions->whereNotNull('discharge_datetime');
        $summary = [
            'total_admissions' => $admissions->count(),
            'total_discharges' => $admissions->where('status', 'discharged')->count(),
            'total_revenue' => $admissions->sum('net_amount'),
            'avg_stay' => $discharged->count() ? round($discharged->avg('days_admitted'), 1) : 0,
        ];

        return view('reports.ipd', compact('admissions', 'summary', 'from', 'to'));
    }

    public function pharmacy(Request $request): View
    {
        $this->authorize('view pharmacy reports');
        $from = $request->get('from', today()->toDateString());
        $to = $request->get('to', today()->toDateString());
        $sales = Sale::with(['patient:id,name', 'createdBy:id,name', 'items'])
            ->withCount('items')
            ->whereBetween('sale_date', [$from, $to])->where('status', 'completed')->latest()->get();

        $items = $sales->flatMap->items;
        // COGS per line = its sale total minus the recorded profit on that line.
        $cost = $items->sum(fn ($i) => $i->total_price - $i->profit);

        $summary = [
            'total_sales' => $sales->count(),
            'revenue' => $sales->sum('total_amount'),
            'cost' => $cost,
        ];

        $lowStock = Medicine::lowStock()->active()->orderBy('stock_quantity')->get(['id', 'name', 'stock_quantity']);

        return view('reports.pharmacy', compact('sales', 'summary', 'lowStock', 'from', 'to'));
    }

    public function laboratory(Request $request): View
    {
        $this->authorize('view lab reports');
        $from = $request->get('from', today()->toDateString());
        $to = $request->get('to', today()->toDateString());
        $bookings = LabBooking::with(['patient:id,name', 'items'])
            ->whereBetween('booking_date', [$from, $to])->latest()->get();

        $summary = [
            'total_bookings' => $bookings->count(),
            'completed' => $bookings->where('status', 'completed')->count(),
            'pending' => $bookings->where('status', 'pending')->count(),
            'revenue' => $bookings->sum('net_amount'),
        ];

        return view('reports.laboratory', compact('bookings', 'summary', 'from', 'to'));
    }

    public function expenses(Request $request): View
    {
        $this->authorize('view reports');
        $from = $request->get('from', today()->toDateString());
        $to = $request->get('to', today()->toDateString());
        $expenses = Expense::with(['category:id,name'])
            ->whereBetween('expense_date', [$from, $to])
            ->when($request->category_id, fn ($q, $id) => $q->where('expense_category_id', $id))
            ->latest()->get();

        $totalAmount = $expenses->sum('amount');
        $approvedAmount = $expenses->where('status', 'approved')->sum('amount');
        $pendingAmount = $expenses->where('status', 'pending')->sum('amount');

        $categories = ExpenseCategory::active()->get();

        return view('reports.expenses', compact('expenses', 'totalAmount', 'approvedAmount', 'pendingAmount', 'from', 'to', 'categories'));
    }

    public function profitLoss(Request $request): View
    {
        $this->authorize('view reports');
        $from = $request->get('from', today()->startOfMonth()->toDateString());
        $to = $request->get('to', today()->toDateString());
        $data = $this->profitLossData(Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay());

        return view('reports.profit-loss', array_merge($data, compact('from', 'to')));
    }

    /**
     * Shared P&L aggregation for a date range, used by both the report view and
     * its Excel export so the two never drift apart. Returns the per-stream
     * revenue (keys summed directly by the view — no synthetic 'total' key),
     * expenses grouped by category, salaries, and the derived totals.
     */
    private function profitLossData(Carbon $start, Carbon $end): array
    {
        $revenue = [
            'opd' => OpdVisit::whereBetween('visit_date', [$start, $end])->sum('net_amount'),
            'ipd' => IpdAdmission::whereBetween('admission_datetime', [$start, $end])->sum('net_amount'),
            'pharmacy' => Sale::whereBetween('sale_date', [$start, $end])->where('status', 'completed')->sum('total_amount'),
            'lab' => LabBooking::whereBetween('booking_date', [$start, $end])->sum('net_amount'),
            'other' => 0,
        ];

        $expenseByCategory = Expense::approved()
            ->whereBetween('expense_date', [$start, $end])
            ->with('category:id,name')
            ->get()
            ->groupBy(fn ($e) => $e->category->name ?? 'Uncategorized')
            ->map(fn ($group, $name) => (object) ['name' => $name, 'total' => $group->sum('amount')])
            ->values();

        $totalSalaries = SalaryPayment::where('status', 'paid')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('net_salary');

        $totalExpenses = $expenseByCategory->sum('total') + $totalSalaries;
        $netProfit = array_sum($revenue) - $totalExpenses;

        return compact('revenue', 'expenseByCategory', 'totalSalaries', 'totalExpenses', 'netProfit');
    }

    public function dailyClosingForm(): View
    {
        $this->authorize('close daily reports');
        $date = request('date', today()->toDateString());
        $currentShift = now()->hour < 14 ? 'morning' : (now()->hour < 22 ? 'evening' : 'night');
        $report = DailyClosingReport::whereDate('report_date', $date)->first();

        return view('reports.daily-closing', compact('report', 'currentShift'));
    }

    public function closeDay(Request $request): RedirectResponse
    {
        $this->authorize('close daily reports');
        $date = today();
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        $report = DailyClosingReport::updateOrCreate(
            ['report_date' => $date->toDateString()],
            [
                'total_opd_patients' => OpdVisit::whereDate('visit_date', $date)->count(),
                'total_ipd_admissions' => IpdAdmission::whereBetween('admission_datetime', [$start, $end])->count(),
                'total_ipd_discharged' => IpdAdmission::whereBetween('discharge_datetime', [$start, $end])->where('status', 'discharged')->count(),
                'opd_revenue' => OpdVisit::whereDate('visit_date', $date)->where('payment_status', 'paid')->sum('net_amount'),
                'ipd_revenue' => IpdAdmission::whereBetween('admission_datetime', [$start, $end])->sum('net_amount'),
                'pharmacy_revenue' => Sale::whereDate('sale_date', $date)->where('status', 'completed')->sum('total_amount'),
                'lab_revenue' => LabBooking::whereDate('booking_date', $date)->where('payment_status', 'paid')->sum('net_amount'),
                'hospital_expenses' => Expense::whereDate('expense_date', $date)->approved()->forModule('hospital')->sum('amount'),
                'pharmacy_expenses' => Expense::whereDate('expense_date', $date)->approved()->forModule('pharmacy')->sum('amount'),
                'lab_expenses' => Expense::whereDate('expense_date', $date)->approved()->forModule('laboratory')->sum('amount'),
                'salary_expenses' => SalaryPayment::where('status', 'paid')->whereDate('payment_date', $date)->sum('net_salary'),
                'notes' => $request->notes,
                'closed_by' => auth()->id(),
                'closed_at' => now(),
            ]
        );

        $report->update([
            'total_revenue' => $report->opd_revenue + $report->ipd_revenue + $report->pharmacy_revenue + $report->lab_revenue + $report->other_revenue,
            'total_expenses' => $report->hospital_expenses + $report->pharmacy_expenses + $report->lab_expenses + $report->salary_expenses,
        ]);
        $report->update(['net_profit' => $report->total_revenue - $report->total_expenses]);

        return redirect()->route('reports.daily.pdf', $report)->with('success', 'Daily closing report generated.');
    }

    public function monthlyClosingForm(): View
    {
        $this->authorize('close monthly reports');
        $month = (int) request('month', now()->month);
        $year = (int) request('year', now()->year);
        $report = MonthlyClosingReport::where('month', $month)->where('year', $year)->first();

        return view('reports.monthly-closing', compact('report'));
    }

    public function closeMonth(Request $request): RedirectResponse
    {
        $this->authorize('close monthly reports');
        $request->validate(['month' => 'required|integer|min:1|max:12', 'year' => 'required|integer|min:2000']);

        $month = (int) $request->month;
        $year = (int) $request->year;
        $start = now()->setDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $report = MonthlyClosingReport::updateOrCreate(
            ['month' => $month, 'year' => $year],
            [
                'total_opd_patients' => OpdVisit::whereBetween('visit_date', [$start, $end])->count(),
                'total_ipd_admissions' => IpdAdmission::whereBetween('admission_datetime', [$start, $end])->count(),
                'opd_revenue' => OpdVisit::whereBetween('visit_date', [$start, $end])->sum('net_amount'),
                'ipd_revenue' => IpdAdmission::whereBetween('admission_datetime', [$start, $end])->sum('net_amount'),
                'pharmacy_revenue' => Sale::whereBetween('sale_date', [$start, $end])->where('status', 'completed')->sum('total_amount'),
                'lab_revenue' => LabBooking::whereBetween('booking_date', [$start, $end])->sum('net_amount'),
                'total_expenses' => Expense::whereBetween('expense_date', [$start, $end])->approved()->sum('amount'),
                'total_salaries' => SalaryPayment::where('month', $month)->where('year', $year)->where('status', 'paid')->sum('net_salary'),
                'notes' => $request->notes,
                'closed_by' => auth()->id(),
                'closed_at' => now(),
            ]
        );

        $totalRevenue = $report->opd_revenue + $report->ipd_revenue + $report->pharmacy_revenue + $report->lab_revenue;
        $report->update([
            'total_revenue' => $totalRevenue,
            'net_profit' => $totalRevenue - $report->total_expenses - $report->total_salaries,
        ]);

        return redirect()->route('reports.monthly.pdf', $report)->with('success', 'Monthly closing report generated.');
    }

    public function dailyPdf(DailyClosingReport $report)
    {
        $this->authorize('view reports');
        $setting = HospitalSetting::current();
        $pdf = app('dompdf.wrapper')->loadView('reports.daily-pdf', compact('report', 'setting'));

        return $pdf->stream("Daily-Report-{$report->report_date}.pdf");
    }

    public function monthlyPdf(MonthlyClosingReport $report)
    {
        $this->authorize('view reports');
        $setting = HospitalSetting::current();
        $start = now()->setDate($report->year, $report->month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $expenseByCategory = Expense::approved()
            ->whereBetween('expense_date', [$start, $end])
            ->with('category:id,name')
            ->get()
            ->groupBy(fn ($e) => $e->category->name ?? 'Uncategorized')
            ->map(fn ($group, $name) => (object) ['name' => $name, 'total' => $group->sum('amount')])
            ->values();
        $pdf = app('dompdf.wrapper')->loadView('reports.monthly-pdf', compact('report', 'setting', 'expenseByCategory'));

        return $pdf->stream("Monthly-Report-{$report->month}-{$report->year}.pdf");
    }

    // ── Excel Exports ────────────────────────────────────────────────────────

    public function opdExport(Request $request): BinaryFileResponse
    {
        $this->authorize('view reports');
        $from = $request->get('from', today()->toDateString());
        $to = $request->get('to', today()->toDateString());

        return Excel::download(
            new OpdReportExport($from, $to, $request->shift, $request->doctor_id),
            "OPD-Report-{$from}-to-{$to}.xlsx"
        );
    }

    public function ipdExport(Request $request): BinaryFileResponse
    {
        $this->authorize('view reports');
        $from = $request->get('from', today()->toDateString());
        $to = $request->get('to', today()->toDateString());

        return Excel::download(
            new IpdReportExport($from, $to, $request->status),
            "IPD-Report-{$from}-to-{$to}.xlsx"
        );
    }

    public function pharmacyExport(Request $request): BinaryFileResponse
    {
        $this->authorize('view pharmacy reports');
        $from = $request->get('from', today()->toDateString());
        $to = $request->get('to', today()->toDateString());

        return Excel::download(
            new PharmacyReportExport($from, $to),
            "Pharmacy-Report-{$from}-to-{$to}.xlsx"
        );
    }

    public function labExport(Request $request): BinaryFileResponse
    {
        $this->authorize('view lab reports');
        $from = $request->get('from', today()->toDateString());
        $to = $request->get('to', today()->toDateString());

        return Excel::download(
            new LabReportExport($from, $to, $request->status),
            "Lab-Report-{$from}-to-{$to}.xlsx"
        );
    }

    public function expensesExport(Request $request): BinaryFileResponse
    {
        $this->authorize('view reports');
        $from = $request->get('from', today()->toDateString());
        $to = $request->get('to', today()->toDateString());

        return Excel::download(
            new ExpensesReportExport($from, $to, $request->category_id),
            "Expenses-Report-{$from}-to-{$to}.xlsx"
        );
    }

    public function profitLossExport(Request $request): BinaryFileResponse
    {
        $this->authorize('view reports');
        $from = $request->get('from', today()->startOfMonth()->toDateString());
        $to = $request->get('to', today()->toDateString());
        $data = $this->profitLossData(Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay());

        return Excel::download(
            new ProfitLossExport(
                $data['revenue'],
                $data['expenseByCategory'],
                $data['totalSalaries'],
                $data['totalExpenses'],
                $data['netProfit'],
                $from,
                $to
            ),
            "ProfitLoss-{$from}-to-{$to}.xlsx"
        );
    }
}
