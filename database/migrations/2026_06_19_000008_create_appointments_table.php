<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('appointment_datetime');
            $table->unsignedSmallInteger('duration_minutes')->default(15);
            $table->enum('type', ['opd', 'follow_up', 'emergency', 'teleconsultation'])->default('opd');
            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('fee', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'waived'])->default('pending');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['doctor_id', 'appointment_datetime']);
            $table->index(['patient_id', 'appointment_datetime']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
