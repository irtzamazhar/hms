<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_admissions', function (Blueprint $table) {
            $table->id();
            $table->string('admission_number')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bed_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('admission_datetime');
            $table->dateTime('discharge_datetime')->nullable();
            $table->text('admission_diagnosis')->nullable();
            $table->text('discharge_diagnosis')->nullable();
            $table->text('treatment_summary')->nullable();
            $table->enum('admission_type', ['emergency', 'elective', 'transfer'])->default('elective');
            $table->enum('status', ['admitted', 'discharged', 'transferred', 'absconded', 'death'])->default('admitted');
            $table->decimal('daily_bed_charge', 10, 2)->default(0);
            $table->decimal('doctor_charges', 10, 2)->default(0);
            $table->decimal('nursing_charges', 10, 2)->default(0);
            $table->decimal('medicine_charges', 10, 2)->default(0);
            $table->decimal('lab_charges', 10, 2)->default(0);
            $table->decimal('other_charges', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'insurance'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('admitted_by')->constrained('users');
            $table->foreignId('discharged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['admission_datetime']);
            $table->index(['patient_id', 'status']);
        });

        Schema::create('ipd_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipd_admission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->dateTime('treatment_datetime');
            $table->text('treatment_notes');
            $table->string('vital_bp')->nullable();
            $table->string('vital_pulse')->nullable();
            $table->string('vital_temperature')->nullable();
            $table->string('vital_weight')->nullable();
            $table->string('vital_spo2')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_treatments');
        Schema::dropIfExists('ipd_admissions');
    }
};
