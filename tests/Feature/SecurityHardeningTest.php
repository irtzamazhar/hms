<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\IpdAdmission;
use App\Models\LabBooking;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function actor(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /** BAC-1: lab report PDF requires `view lab reports`. */
    public function test_lab_report_pdf_blocked_without_permission(): void
    {
        $receptionist = $this->actor('receptionist'); // no 'view lab reports'
        $patient = Patient::factory()->create();
        $booking = LabBooking::create([
            'booking_number' => 'LB-1', 'patient_id' => $patient->id,
            'booking_date' => today(), 'shift' => 'morning',
            'total_amount' => 0, 'discount' => 0, 'net_amount' => 0,
            'payment_method' => 'cash', 'created_by' => $receptionist->id,
        ]);

        $this->actingAs($receptionist)
            ->get(route('lab.report.pdf', $booking))
            ->assertForbidden();
    }

    /** BAC-5: adding an IPD treatment requires `edit ipd`. */
    public function test_ipd_add_treatment_blocked_without_permission(): void
    {
        $receptionist = $this->actor('receptionist'); // has view/create ipd, not edit
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();
        $admission = IpdAdmission::create([
            'admission_number' => 'IPD-X', 'patient_id' => $patient->id, 'doctor_id' => $doctor->id,
            'admission_datetime' => now(), 'status' => 'admitted', 'admitted_by' => $receptionist->id,
        ]);

        $this->actingAs($receptionist)
            ->post(route('ipd.treatment.add', $admission), ['treatment_notes' => 'x'])
            ->assertForbidden();

        $this->assertDatabaseCount('ipd_treatments', 0);
    }

    /** BAC: appointment status change requires `edit appointments`. */
    public function test_appointment_status_change_requires_permission(): void
    {
        $nurse = $this->actor('nurse'); // view appointments only
        $appt = Appointment::factory()->create(['status' => 'scheduled']);

        $this->actingAs($nurse)
            ->patch(route('appointments.status', $appt), ['status' => 'completed'])
            ->assertForbidden();
    }

    /** BL-1: expenses cannot be self-approved via mass assignment. */
    public function test_expense_cannot_be_self_approved(): void
    {
        $accountant = $this->actor('accountant'); // create expenses, NOT approve
        $cat = ExpenseCategory::create(['name' => 'Misc', 'code' => 'MISC', 'module' => 'general', 'status' => 'active']);

        $this->actingAs($accountant)->post(route('expenses.store'), [
            'expense_category_id' => $cat->id,
            'title' => 'Bribe', 'amount' => 100, 'expense_date' => today()->toDateString(),
            'payment_method' => 'cash', 'module' => 'general',
            'status' => 'approved', 'approved_by' => $accountant->id, // injected
        ])->assertRedirect();

        $expense = Expense::first();
        $this->assertSame('pending', $expense->status);
        $this->assertNull($expense->approved_by);
    }

    /** BL-4: a POS sale cannot oversell beyond available stock. */
    public function test_pos_sale_rejects_overselling(): void
    {
        $admin = $this->actor('super_admin');
        $medicine = Medicine::create([
            'name' => 'Panadol', 'unit' => 'tablet', 'sale_price' => 10,
            'purchase_price' => 5, 'stock_quantity' => 3,
        ]);

        $this->actingAs($admin)->post(route('pharmacy.sale.store'), [
            'items' => [[
                'medicine_id' => $medicine->id, 'quantity' => 10, 'unit_price' => 10,
            ]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(3, (int) $medicine->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 0);
    }

    /** UP-1: non-image / dangerous logo uploads are rejected. */
    public function test_logo_upload_rejects_non_image(): void
    {
        Storage::fake('public');
        $admin = $this->actor('super_admin');

        $this->actingAs($admin)->patch(route('settings.hospital'), [
            'hospital_name' => 'HMS',
            'logo' => UploadedFile::fake()->create('shell.php', 8, 'application/x-php'),
        ])->assertSessionHasErrors('logo');
    }

    /** BL-2: a POS price override below cost is rejected. */
    public function test_pos_price_override_below_cost_rejected(): void
    {
        $admin = $this->actor('super_admin');
        $medicine = Medicine::create([
            'name' => 'Brufen', 'unit' => 'tablet', 'sale_price' => 10,
            'purchase_price' => 5, 'stock_quantity' => 50,
        ]);

        $this->actingAs($admin)->post(route('pharmacy.sale.store'), [
            'items' => [['medicine_id' => $medicine->id, 'quantity' => 1, 'unit_price' => 1]],
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseCount('sales', 0);
    }

    /** BL-2: override is ignored for a user lacking `manage medicines`. */
    public function test_pos_price_override_ignored_without_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('create sales'); // can sell, cannot manage medicines

        $medicine = Medicine::create([
            'name' => 'Disprin', 'unit' => 'tablet', 'sale_price' => 10,
            'purchase_price' => 5, 'stock_quantity' => 50,
        ]);

        $this->actingAs($user)->post(route('pharmacy.sale.store'), [
            'items' => [['medicine_id' => $medicine->id, 'quantity' => 1, 'unit_price' => 999]],
        ])->assertRedirect();

        // Sale recorded at the catalogue price, not the injected 999.
        $this->assertEquals(10, (float) Sale::first()->total_amount);
    }

    /** CR-1: patient PHI is encrypted at rest but transparently readable. */
    public function test_patient_phi_encrypted_at_rest(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $patient = Patient::factory()->create(['medical_history' => 'Confidential diagnosis X']);

        $raw = DB::table('patients')->where('id', $patient->id)->value('medical_history');

        $this->assertNotSame('Confidential diagnosis X', $raw);                       // ciphertext at rest
        $this->assertSame('Confidential diagnosis X', $patient->fresh()->medical_history); // decrypts on read
        $this->assertSame('Confidential diagnosis X', Crypt::decryptString($raw));
    }

    /** CR-2: PHI fields are excluded from the audit trail. */
    public function test_patient_phi_excluded_from_audit(): void
    {
        $patient = new Patient;

        foreach (['medical_history', 'allergies', 'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation'] as $field) {
            $this->assertContains($field, $patient->getAuditExclude(), "$field should be excluded from audits");
        }
    }
}
