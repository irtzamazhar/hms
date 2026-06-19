<?php

namespace App\Http\Controllers;

use App\Exports\ExpensesReportExport;
use App\Exports\IpdReportExport;
use App\Exports\LabReportExport;
use App\Exports\OpdReportExport;
use App\Exports\PharmacyReportExport;
use App\Exports\ProfitLossExport;
use App\Models\DailyClosingReport;
use App\Models\Expense;
use App\Models\IpdAdmission;
use App\Models\LabBooking;
use App\Models\MonthlyClosingReport;
use App\Models\OpdVisit;
use App\Models\Sale;
use App\Models\SalaryPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $to   = $request->get('to', today()->toDateString());

        $visits = OpdVisit::with(['patient:id,name,mr_number', 'doctor.user:id,name'])
            ->whereBetween('visit_date', [$from, $to])
            ->when($request->shift, fn ($q, $s) => $q->where('shift', $s))
            ->when($request->doctor_id, fn ($q, $id) => $q->where('doctor_id', $id))
            ->latest()->get();

        $totals = [
            'patients' => $visits->count(),
            'revenue'  => $visits->sum('net_amount'),
            'paid'     => $visits->where('payment_status', 'paid')->sum('net_amount'),
            'pending'  => $visits->where('payment_status', 'pending')->sum('net_amount'),
        ];

        return view('reports.opd', compact('visits', 'totals', 'from', 'to'));
    }

    public function ipd(Request $request): View
    {
        $this->authorize('view reports');
        $from       = $request->get('from', today()->toDateString());
        $to         = $request->get('to', today()->toDateString());
        $admissions = IpdAdmission::with(['patient:id,name,mr_number', 'doctor.user:id,name', 'ward:id,name'])
            ->whereBetween('admission_datetime', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->latest('admission_datetime')->get();

        return view('reports.ipd', compact('admissions', 'from', 'to'));
    }

    public function pharmacy(Request $request): View
    {
        $this->authorize('view pharmacy reports');
        $from  = $request->get('from', today()->toDateString());
        $to    = $request->get('to', today()->toDateString());
        $sales = Sale::with(['patient:id,name', 'createdBy:id,name'])
            ->whereBetween('sale_date', [$from, $to])->where('status', 'completed')->latest()->get();

        $totals = ['count' => $sales->count(), 'revenue' => $sales->sum('total_amount'), 'profit' => $sales->flatMap->items->sum('profit')];

        return view('reports.pharmacy', compact('sales', 'totals', 'from', 'to'));
    }

    public function laboratory(Request $request): View
    {
        $this->authorize('view lab reports');
        $from     = $request->get('from', today()->toDateString());
        $to       = $request->get('to', today()->toDateString());
        $bookings = LabBooking::with(['patient:id,name'])
            ->whereBetween('booking_date', [$from, $to])->latest()->get();

        return view('reports.laboratory', compact('bookings', 'from', 'to'));
    }

    public function expenses(Request $request): View
    {
        $this->authorize('view reports');
        $from     = $request->get('from', today()->toDateString());
        $to       = $request->get('to', today()->toDateString());
        $expenses = Expense::with(['category:id,name'])->approved()
            ->whereBetween('expense_date', [$from, $to])->latest()->get();

        $totals = $expenses->groupBy('module')->map->sum('amount');

        return view('reports.expenses', compact('expenses', 'totals', 'from', 'to'));
    }

    public function profitLoss(Request $request): View
    {
        $this->authorize('view reports');
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);
        $start = now()->setDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $revenue = [
            'opd'      => OpdVisit::whereBetween('visit_date', [$start, $end])->sum('net_amount'),
            'pharmacy' => Sale::whereBetween('sale_date', [$start, $end])->where('status', 'completed')->sum('total_amount'),
            'lab'      => LabBooking::whereBetween('booking_date', [$start, $end])->sum('net_amount'),
        ];

        $expenses = [
            'hospital'  => Expense::whereBetween('expense_date', [$start, $end])->approved()->forModule('hospital')->sum('amount'),
            'pharmacy'  => Expense::whereBetween('expense_date', [$start, $end])->approved()->forModule('pharmacy')->sum('amount'),
            'lab'       => Expense::whereBetween('expense_date', [$start, $end])->approved()->forModule('laboratory')->sum('amount'),
            'salaries'  => SalaryPayment::where('month', $month)->where('year', $year)->where('status', 'paid')->sum('net_salary'),
        ];

        $revenue['total']  = array_sum($revenue);
        $expenses['total'] = array_sum($expenses);
        $netProfit         = $revenue['total'] - $expenses['total'];

        return view('reports.profit-loss', compact('revenue', 'expenses', 'netProfit', 'month', 'year'));
    }

    public function dailyClosingForm(): View
    {
        $this->authorize('close daily reports');
        $existing = DailyClosingReport::whereDate('report_date', today())->first();

        return view('reports.daily-closing', compact('existing'));
    }

    public function closeDay(Request $request): RedirectResponse
    {
        $this->authorize('close daily reports');
        $date   = today();
        $start  = $date->copy()->startOfDay();
        $end    = $date->copy()->endOfDay();

        $report = DailyClosingReport::updateOrCreate(
            ['report_date' => $date->toDateString()],
            [
                'total_opd_patients'  => OpdVisit::whereDate('visit_date', $date)->count(),
                'total_ipd_admissions' => IpdAdmission::whereBetween('admission_datetime', [$start, $end])->count(),
                'total_ipd_discharged' => IpdAdmission::whereBetween('discharge_datetime', [$start, $end])->where('status', 'discharged')->count(),
                'opd_revenue'         => OpdVisit::whereDate('visit_date', $date)->where('payment_status', 'paid')->sum('net_amount'),
                'pharmacy_revenue'    => Sale::whereDate('sale_date', $date)->where('status', 'completed')->sum('total_amount'),
                'lab_revenue'         => LabBooking::whereDate('booking_date', $date)->where('payment_status', 'paid')->sum('net_amount'),
                'hospital_expenses'   => Expense::whereDate('expense_date', $date)->approved()->forModule('hospital')->sum('amount'),
                'pharmacy_expenses'   => Expense::whereDate('expense_date', $date)->approved()->forModule('pharmacy')->sum('amount'),
                'lab_expenses'        => Expense::whereDate('expense_date', $date)->approved()->forModule('laboratory')->sum('amount'),
                'notes'               => $request->notes,
                'closed_by'           => auth()->id(),
                'closed_at'           => now(),
            ]
        );

        $report->update([
            'total_revenue'   => $report->opd_revenue + $report->pharmacy_revenue + $report->lab_revenue,
            'total_expenses'  => $report->hospital_expenses + $report->pharmacy_expenses + $report->lab_expenses,
        ]);
        $report->update(['net_profit' => $report->total_revenue - $report->total_expenses]);

        return redirect()->route('reports.daily.pdf', $report)->with('success', 'Daily closing report generated.');
    }

    public function monthlyClosingForm(): View
    {
        $this->authorize('close monthly reports');

        return view('reports.monthly-closing');
    }

    public function closeMonth(Request $request): RedirectResponse
    {
        $this->authorize('close monthly reports');
        $request->validate(['month' => 'required|integer|min:1|max:12', 'year' => 'required|integer|min:2000']);

        $month = (int) $request->month;
        $year  = (int) $request->year;
        $start = now()->setDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $report = MonthlyClosingReport::updateOrCreate(
            ['month' => $month, 'year' => $year],
            [
                'total_opd_patients'    => OpdVisit::whereBetween('visit_date', [$start, $end])->count(),
                'total_ipd_admissions'  => IpdAdmission::whereBetween('admission_datetime', [$start, $end])->count(),
                'opd_revenue'           => OpdVisit::whereBetween('visit_date', [$start, $end])->sum('net_amount'),
                'pharmacy_revenue'      => Sale::whereBetween('sale_date', [$start, $end])->where('status', 'completed')->sum('total_amount'),
                'lab_revenue'           => LabBooking::whereBetween('booking_date', [$start, $end])->sum('net_amount'),
                'total_expenses'        => Expense::whereBetween('expense_date', [$start, $end])->approved()->sum('amount'),
                'total_salaries'        => SalaryPayment::where('month', $month)->where('year', $year)->where('status', 'paid')->sum('net_salary'),
                'notes'                 => $request->notes,
                'closed_by'             => auth()->id(),
                'closed_at'             => now(),
            ]
        );

        $report->update([
            'total_revenue' => $report->opd_revenue + $report->pharmacy_revenue + $report->lab_revenue,
            'net_profit'    => ($report->opd_revenue + $report->pharmacy_revenue + $report->lab_revenue)
                - $report->total_expenses - $report->total_salaries,
        ]);

        return redirect()->route('reports.monthly.pdf', $report)->with('success', 'Monthly closing report generated.');
    }

    public function dailyPdf(DailyClosingReport $report)
    {
        $setting = \App\Models\HospitalSetting::current();
        $pdf     = app('dompdf.wrapper')->loadView('reports.daily-pdf', compact('report', 'setting'));

        return $pdf->stream("Daily-Report-{$report->report_date}.pdf");
    }

    public function monthlyPdf(MonthlyClosingReport $report)
    {
        $setting = \App\Models\HospitalSetting::current();
        $pdf     = app('dompdf.wrapper')->loadView('reports.monthly-pdf', compact('report', 'setting'));

        return $pdf->stream("Monthly-Report-{$report->month}-{$report->year}.pdf");
    }

    // ── Excel Exports ────────────────────────────────────────────────────────

    public function opdExport(Request $request): BinaryFileResponse
    {
        $this->authorize('view reports');
        $from = $request->get('from', today()->toDateString());
        $to   = $request->get('to', today()->toDateString());

        return Excel::download(
            new OpdReportExport($from, $to, $request->shift, $request->doctor_id),
            "OPD-Report-{$from}-to-{$to}.xlsx"
        );
    }

    public function ipdExport(Request $request): BinaryFileResponse
    {
        $this->authorize('view reports');
        $from = $request->get('from', today()->toDateString());
        $to   = $request->get('to', today()->toDateString());

        return Excel::download(
            new IpdReportExport($from, $to, $request->status),
            "IPD-Report-{$from}-to-{$to}.xlsx"
        );
    }

    public function pharmacyExport(Request $request): BinaryFileResponse
    {
        $this->authorize('view pharmacy reports');
        $from = $request->get('from', today()->toDateString());
        $to   = $request->get('to', today()->toDateString());

        return Excel::download(
            new PharmacyReportExport($from, $to),
            "Pharmacy-Report-{$from}-to-{$to}.xlsx"
        );
    }

    public function labExport(Request $request): BinaryFileResponse
    {
        $this->authorize('view lab reports');
        $from = $request->get('from', today()->toDateString());
        $to   = $request->get('to', today()->toDateString());

        return Excel::download(
            new LabReportExport($from, $to, $request->status),
            "Lab-Report-{$from}-to-{$to}.xlsx"
        );
    }

    public function expensesExport(Request $request): BinaryFileResponse
    {
        $this->authorize('view reports');
        $from = $request->get('from', today()->toDateString());
        $to   = $request->get('to', today()->toDateString());

        return Excel::download(
            new ExpensesReportExport($from, $to, $request->module),
            "Expenses-Report-{$from}-to-{$to}.xlsx"
        );
    }

    public function profitLossExport(Request $request): BinaryFileResponse
    {
        $this->authorize('view reports');
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);
        $start = now()->setDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $revenue = [
            'opd'      => OpdVisit::whereBetween('visit_date', [$start, $end])->sum('net_amount'),
            'pharmacy' => Sale::whereBetween('sale_date', [$start, $end])->where('status', 'completed')->sum('total_amount'),
            'lab'      => LabBooking::whereBetween('booking_date', [$start, $end])->sum('net_amount'),
        ];
        $revenue['total'] = array_sum($revenue);

        $expenses = [
            'hospital' => Expense::whereBetween('expense_date', [$start, $end])->approved()->forModule('hospital')->sum('amount'),
            'pharmacy' => Expense::whereBetween('expense_date', [$start, $end])->approved()->forModule('pharmacy')->sum('amount'),
            'lab'      => Expense::whereBetween('expense_date', [$start, $end])->approved()->forModule('laboratory')->sum('amount'),
            'salaries' => SalaryPayment::where('month', $month)->where('year', $year)->where('status', 'paid')->sum('net_salary'),
        ];
        $expenses['total'] = array_sum($expenses);
        $netProfit         = $revenue['total'] - $expenses['total'];

        return Excel::download(
            new ProfitLossExport($revenue, $expenses, $netProfit, $month, $year),
            "ProfitLoss-" . date('F', mktime(0, 0, 0, $month, 1)) . "-{$year}.xlsx"
        );
    }
}
