<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\IpdAdmission;
use App\Models\OpdVisit;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_mr_number_is_generated_with_correct_prefix(): void
    {
        $number = Patient::generateMrNumber();
        $this->assertStringStartsWith('MR-', $number);
    }

    public function test_patient_mr_numbers_are_sequential(): void
    {
        $first  = Patient::generateMrNumber();
        Patient::factory()->create(['mr_number' => $first]);

        $second = Patient::generateMrNumber();
        $this->assertNotEquals($first, $second);
    }

    public function test_appointment_number_is_generated_with_correct_prefix(): void
    {
        $number = Appointment::generateNumber();
        $this->assertStringStartsWith('APT-', $number);
    }

    public function test_opd_visit_number_is_generated_with_correct_prefix(): void
    {
        $number = OpdVisit::generateVisitNumber();
        $this->assertStringStartsWith('OPD-', $number);
    }

    public function test_ipd_admission_number_is_generated_with_correct_prefix(): void
    {
        $number = IpdAdmission::generateAdmissionNumber();
        $this->assertStringStartsWith('IPD-', $number);
    }
}
