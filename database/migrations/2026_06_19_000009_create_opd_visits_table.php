<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opd_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_number')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('token_id')->nullable()->constrained()->nullOnDelete();
            $table->date('visit_date');
            $table->enum('shift', ['morning', 'evening', 'night']);
            $table->text('chief_complaints')->nullable();
            $table->text('symptoms')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('notes')->nullable();
            $table->string('vital_bp')->nullable();
            $table->string('vital_pulse')->nullable();
            $table->string('vital_temperature')->nullable();
            $table->string('vital_weight')->nullable();
            $table->string('vital_height')->nullable();
            $table->string('vital_spo2')->nullable();
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'waived'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'insurance'])->nullable();
            $table->boolean('is_follow_up')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->enum('status', ['waiting', 'in_progress', 'completed', 'cancelled'])->default('waiting');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['visit_date', 'shift']);
            $table->index(['doctor_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opd_visits');
    }
};
