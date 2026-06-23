<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IpdController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\LabTestController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Patients ──────────────────────────────────────────
    Route::get('/patients/export', [PatientController::class, 'export'])->name('patients.export');
    Route::resource('patients', PatientController::class);
    Route::get('/patients/{patient}/history', [PatientController::class, 'history'])->name('patients.history');

    // ── Tokens ────────────────────────────────────────────
    Route::resource('tokens', TokenController::class)->except(['edit', 'update']);
    Route::patch('/tokens/{token}/status', [TokenController::class, 'updateStatus'])->name('tokens.status');
    Route::get('/tokens/{token}/print', [TokenController::class, 'print'])->name('tokens.print');

    // ── Appointments ──────────────────────────────────────
    Route::get('/appointments/slots', [AppointmentController::class, 'slots'])->name('appointments.slots');
    Route::get('/appointments/doctor-info/{doctor}', [AppointmentController::class, 'doctorInfo'])->name('appointments.doctor-info');
    Route::resource('appointments', AppointmentController::class);
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');

    // ── OPD ───────────────────────────────────────────────
    Route::resource('opd', OpdController::class);
    Route::get('/opd/{opdVisit}/invoice', [OpdController::class, 'invoice'])->name('opd.invoice');
    Route::get('/opd/{opdVisit}/print', [OpdController::class, 'print'])->name('opd.print');

    // ── IPD ───────────────────────────────────────────────
    Route::resource('ipd', IpdController::class);
    Route::patch('/ipd/{ipdAdmission}/discharge', [IpdController::class, 'discharge'])->name('ipd.discharge');
    Route::post('/ipd/{ipdAdmission}/treatment', [IpdController::class, 'addTreatment'])->name('ipd.treatment.add');
    Route::get('/ipd/{ipdAdmission}/invoice', [IpdController::class, 'invoice'])->name('ipd.invoice');

    // ── Wards & Beds ──────────────────────────────────────
    Route::resource('wards', WardController::class);
    Route::get('/wards/{ward}/beds', [WardController::class, 'beds'])->name('wards.beds');

    // ── Doctors ───────────────────────────────────────────
    Route::resource('doctors', DoctorController::class);

    // ── Staff ─────────────────────────────────────────────
    Route::resource('staff', StaffController::class);

    // ── Departments ───────────────────────────────────────
    Route::resource('departments', DepartmentController::class);

    // ── Shifts ────────────────────────────────────────────
    Route::resource('shifts', ShiftController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/shifts/close', [ShiftController::class, 'closeForm'])->name('shifts.close.form');
    Route::post('/shifts/close', [ShiftController::class, 'close'])->name('shifts.close');
    Route::get('/shift-assignments', [ShiftController::class, 'assignments'])->name('shifts.assignments');
    Route::post('/shift-assignments', [ShiftController::class, 'assign'])->name('shifts.assign');

    // ── Pharmacy ──────────────────────────────────────────
    Route::prefix('pharmacy')->name('pharmacy.')->group(function () {
        Route::get('/pos', [PharmacyController::class, 'pos'])->name('pos');
        Route::post('/pos', [PharmacyController::class, 'storeSale'])->name('sale.store');
        Route::get('/sales', [PharmacyController::class, 'sales'])->name('sales');
        Route::get('/sales/{sale}', [PharmacyController::class, 'saleShow'])->name('sale.show');
        Route::get('/sales/{sale}/print', [PharmacyController::class, 'salePrint'])->name('sale.print');
        Route::get('/search-medicines', [PharmacyController::class, 'searchMedicines'])->name('search.medicines');
    });

    Route::resource('medicines', MedicineController::class);
    Route::post('/medicines/{medicine}/stock-adjustment', [MedicineController::class, 'stockAdjustment'])->name('medicines.stock.adjust');

    Route::get('/purchases/export', [PurchaseController::class, 'export'])->name('purchases.export');
    Route::resource('purchases', PurchaseController::class);
    Route::get('/purchases/{purchase}/print', [PurchaseController::class, 'print'])->name('purchases.print');

    Route::resource('suppliers', SupplierController::class);

    // ── Laboratory ────────────────────────────────────────
    Route::prefix('lab')->name('lab.')->group(function () {
        Route::get('/', [LabController::class, 'index'])->name('index');
        Route::get('/create', [LabController::class, 'create'])->name('create');
        Route::post('/', [LabController::class, 'store'])->name('store');
        Route::get('/{labBooking}', [LabController::class, 'show'])->name('show');
        Route::patch('/{labBooking}/results', [LabController::class, 'saveResults'])->name('results.save');
        Route::get('/{labBooking}/report/pdf', [LabController::class, 'reportPdf'])->name('report.pdf');
        Route::delete('/{labBooking}', [LabController::class, 'destroy'])->name('destroy');
    });
    Route::resource('lab-tests', LabTestController::class)->except(['show'])->names([
        'index'   => 'lab.tests.index',
        'create'  => 'lab.tests.create',
        'store'   => 'lab.tests.store',
        'edit'    => 'lab.tests.edit',
        'update'  => 'lab.tests.update',
        'destroy' => 'lab.tests.destroy',
    ]);

    // ── Expenses ──────────────────────────────────────────
    Route::resource('expenses', ExpenseController::class);
    Route::patch('/expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');

    // ── Salaries ──────────────────────────────────────────
    Route::prefix('salaries')->name('salaries.')->group(function () {
        Route::get('/', [SalaryController::class, 'index'])->name('index');
        Route::get('/structure', [SalaryController::class, 'structure'])->name('structure');
        Route::post('/structure/{user}', [SalaryController::class, 'saveStructure'])->name('structure.save');
        Route::get('/generate', [SalaryController::class, 'generateForm'])->name('generate');
        Route::post('/generate', [SalaryController::class, 'generate'])->name('generate.run');
        Route::patch('/{salaryPayment}/pay', [SalaryController::class, 'pay'])->name('pay');
        Route::get('/{salaryPayment}/slip', [SalaryController::class, 'slip'])->name('slip');
        Route::get('/export', [SalaryController::class, 'export'])->name('export');
    });

    // ── Reports ───────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/opd', [ReportController::class, 'opd'])->name('opd');
        Route::get('/ipd', [ReportController::class, 'ipd'])->name('ipd');
        Route::get('/pharmacy', [ReportController::class, 'pharmacy'])->name('pharmacy');
        Route::get('/laboratory', [ReportController::class, 'laboratory'])->name('laboratory');
        Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/daily-closing', [ReportController::class, 'dailyClosingForm'])->name('daily-closing');
        Route::post('/daily-closing', [ReportController::class, 'closeDay'])->name('daily-closing.close');
        Route::get('/monthly-closing', [ReportController::class, 'monthlyClosingForm'])->name('monthly-closing');
        Route::post('/monthly-closing', [ReportController::class, 'closeMonth'])->name('monthly-closing.close');
        Route::get('/daily/{report}/pdf', [ReportController::class, 'dailyPdf'])->name('daily.pdf');
        Route::get('/monthly/{report}/pdf', [ReportController::class, 'monthlyPdf'])->name('monthly.pdf');
        // Excel exports
        Route::get('/opd/export', [ReportController::class, 'opdExport'])->name('opd.export');
        Route::get('/ipd/export', [ReportController::class, 'ipdExport'])->name('ipd.export');
        Route::get('/pharmacy/export', [ReportController::class, 'pharmacyExport'])->name('pharmacy.export');
        Route::get('/laboratory/export', [ReportController::class, 'labExport'])->name('laboratory.export');
        Route::get('/expenses/export', [ReportController::class, 'expensesExport'])->name('expenses.export');
        Route::get('/profit-loss/export', [ReportController::class, 'profitLossExport'])->name('profit-loss.export');
    });

    // ── Users ─────────────────────────────────────────────
    Route::resource('users', UserController::class);

    // ── Settings ──────────────────────────────────────────
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::patch('/hospital', [SettingController::class, 'updateHospital'])->name('hospital');
        Route::patch('/system', [SettingController::class, 'updateSystem'])->name('system');
    });
});

require __DIR__ . '/auth.php';
