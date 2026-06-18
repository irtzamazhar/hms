<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Expense;
use App\Models\IpdAdmission;
use App\Models\LabBooking;
use App\Models\Medicine;
use App\Models\OpdVisit;
use App\Models\Patient;
use App\Models\Sale;
use App\Models\SalaryPayment;
use App\Models\Staff;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getSummary(): array
    {
        $today = today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd   = $today->copy()->endOfMonth();

        return [
            'hospital' => $this->hospitalSummary($today, $monthStart, $monthEnd),
            'pharmacy' => $this->pharmacySummary($today, $monthStart, $monthEnd),
            'lab'      => $this->labSummary($today, $monthStart, $monthEnd),
            'finance'  => $this->financeSummary($today, $monthStart, $monthEnd),
        ];
    }

    private function hospitalSummary(Carbon $today, Carbon $monthStart, Carbon $monthEnd): array
    {
        return [
            'total_patients'        => Patient::count(),
            'today_opd'             => OpdVisit::whereDate('visit_date', $today)->count(),
            'today_ipd_admissions'  => IpdAdmission::whereDate('admission_datetime', $today)->count(),
            'current_ipd'           => IpdAdmission::where('status', 'admitted')->count(),
            'total_doctors'         => Doctor::where('status', 'active')->count(),
            'total_staff'           => Staff::where('status', 'active')->count(),
            'today_revenue'         => OpdVisit::whereDate('visit_date', $today)->where('payment_status', 'paid')->sum('net_amount'),
            'monthly_revenue'       => OpdVisit::whereBetween('visit_date', [$monthStart, $monthEnd])->where('payment_status', 'paid')->sum('net_amount'),
        ];
    }

    private function pharmacySummary(Carbon $today, Carbon $monthStart, Carbon $monthEnd): array
    {
        return [
            'stock_value'      => Medicine::sum(DB::raw('stock_quantity * purchase_price')),
            'low_stock_count'  => Medicine::lowStock()->active()->count(),
            'expiring_count'   => \App\Models\MedicineBatch::where('expiry_date', '<=', now()->addDays(90))->where('remaining_quantity', '>', 0)->count(),
            'today_sales'      => Sale::whereDate('sale_date', $today)->where('status', 'completed')->sum('total_amount'),
            'monthly_sales'    => Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->where('status', 'completed')->sum('total_amount'),
        ];
    }

    private function labSummary(Carbon $today, Carbon $monthStart, Carbon $monthEnd): array
    {
        return [
            'today_bookings'   => LabBooking::whereDate('booking_date', $today)->count(),
            'pending_reports'  => LabBooking::where('status', 'processing')->orWhere('status', 'sample_collected')->count(),
            'today_revenue'    => LabBooking::whereDate('booking_date', $today)->where('payment_status', 'paid')->sum('net_amount'),
            'monthly_revenue'  => LabBooking::whereBetween('booking_date', [$monthStart, $monthEnd])->where('payment_status', 'paid')->sum('net_amount'),
        ];
    }

    private function financeSummary(Carbon $today, Carbon $monthStart, Carbon $monthEnd): array
    {
        $monthlyExpenses = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])->approved()->sum('amount');
        $monthlyRevenue  = OpdVisit::whereBetween('visit_date', [$monthStart, $monthEnd])->sum('net_amount')
            + Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->where('status', 'completed')->sum('total_amount')
            + LabBooking::whereBetween('booking_date', [$monthStart, $monthEnd])->sum('net_amount');

        return [
            'monthly_expenses'  => $monthlyExpenses,
            'salary_due'        => SalaryPayment::where('status', 'pending')->sum('net_salary'),
            'monthly_revenue'   => $monthlyRevenue,
            'monthly_profit'    => $monthlyRevenue - $monthlyExpenses,
        ];
    }

    public function getRevenueChart(int $days = 14): array
    {
        $data = OpdVisit::selectRaw('DATE(visit_date) as date, SUM(net_amount) as opd')
            ->where('visit_date', '>=', now()->subDays($days))
            ->groupBy('date')
            ->pluck('opd', 'date')
            ->toArray();

        $pharmData = Sale::selectRaw('DATE(sale_date) as date, SUM(total_amount) as pharmacy')
            ->where('sale_date', '>=', now()->subDays($days))
            ->where('status', 'completed')
            ->groupBy('date')
            ->pluck('pharmacy', 'date')
            ->toArray();

        $labData = LabBooking::selectRaw('DATE(booking_date) as date, SUM(net_amount) as lab')
            ->where('booking_date', '>=', now()->subDays($days))
            ->groupBy('date')
            ->pluck('lab', 'date')
            ->toArray();

        $labels = [];
        $opd = $pharmacy = $lab = [];

        for ($i = $days; $i >= 0; $i--) {
            $date     = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d M');
            $opd[]      = $data[$date] ?? 0;
            $pharmacy[] = $pharmData[$date] ?? 0;
            $lab[]      = $labData[$date] ?? 0;
        }

        return compact('labels', 'opd', 'pharmacy', 'lab');
    }

    public function getPatientGrowth(int $months = 6): array
    {
        $labels = $counts = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start     = now()->subMonths($i)->startOfMonth();
            $end       = now()->subMonths($i)->endOfMonth();
            $labels[]  = $start->format('M Y');
            $counts[]  = Patient::whereBetween('created_at', [$start, $end])->count();
        }

        return compact('labels', 'counts');
    }

    public function getTodayActivity(): array
    {
        return [
            'opd_visits'   => OpdVisit::with(['patient:id,name,mr_number', 'doctor.user:id,name'])
                ->whereDate('visit_date', today())->latest()->take(10)->get(),
            'admissions'   => IpdAdmission::with(['patient:id,name,mr_number', 'ward:id,name'])
                ->whereDate('admission_datetime', today())->latest()->take(5)->get(),
            'lab_bookings' => LabBooking::with(['patient:id,name,mr_number'])
                ->whereDate('booking_date', today())->latest()->take(5)->get(),
        ];
    }
}
